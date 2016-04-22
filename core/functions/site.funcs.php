<?php
    // function to turn errors on/off for debugging
    function debug($true) {
        if($true == 'true'):
            ini_set('display_errors', '1');
            error_reporting(E_ALL | E_NOTICE | E_STRICT | E_DEPRECATED);
        elseif($true == 'false'):
            ini_set('display_errors', '0');
        endif;
    }

    // function that will synchronize php and mysql timezones to user (script owner)
    // defined timezone if not set will revert to a default of Europe/London
    function sync_php_and_mysql_timezones($config){
        $config->timezone = isset($config->timezone) ? $config->timezone : 'Europe/London';
        date_default_timezone_set($config->timezone);
        $now = new DateTime();
        $minutes = $now->getOffset() / 60;
        $segment = ($minutes < 0 ? -1 : 1);
        $minutes = abs($minutes);
        $hours = floor($minutes / 60);
        $minutes -= $hours * 60;
        $offset = sprintf('%+d:%02d', $hours*$segment, $minutes);
        db::pdo()->query('SET time_zone = "'.$offset.'";');
        db::pdo()->execute();
    }

    // function that will convert mysql DATETIME to user-friendly php time
    // will also check if datetime matches today, yesterday, or later
    function convert_datetime($date_time) {
        if(strtotime($date_time) >= strtotime('today')):
            return 'Today '.gmdate('h:ia', strtotime($date_time));
        elseif(strtotime($date_time) >= strtotime('yesterday')):
            return 'Yesterday '.gmdate('h:ia', strtotime($date_time));
        else:
            return gmdate('d.m.Y, h:ia', strtotime($date_time));
        endif;
    }

    // function for cleaning inputted text
    function sanitize($data) {
        $data = htmlentities(get_magic_quotes_gpc() ? stripslashes($data) : $data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    // seo function
    function seo($data) {
        db::pdo()->query('SELECT * FROM `config`');
        db::pdo()->execute();
        $config = db::pdo()->result()[0];
            if(preg_match('/true/i', $config->seo)):
                if(preg_match('/topic.php\?cid=(\w+)&amp;tid=(\w+)&amp;page=(\w+)/i', $data, $matches)):
                    $title = null;
                    db::pdo()->query('SELECT * FROM `topics` WHERE `id` = :id LIMIT 1');
                        db::pdo()->bind(array(':id' => $matches[2]));
                    db::pdo()->execute();
                        if(db::pdo()->count() > 0):
                            foreach(db::pdo()->result() as $link):
                                $title = str_replace(' ', '-', str_replace(array('`', '&quot;'), '', sanitize($link->title)));
                            endforeach;
                        endif;
                    $data = preg_replace('/topic.php\?cid=(\w+)&amp;tid=(\w+)&amp;page=(\w+)/i', 'c$1/'.$title.'-$2/index$3.html', $data);
                    return $data;
                elseif(preg_match('/^(.*?).php$/i', $data)):
                    $data = preg_replace('/^(.*?).php$/i', '$1.html', $data);
                    return $data;
                else:
                    return $data;
                endif;
            else:
                return $data;
            endif;
    }

    // Function that will convert a user id into their username
    function username($id) {
        db::pdo()->query('SELECT * FROM `users` WHERE `id` = :id LIMIT 1');
            db::pdo()->bind(array(':id' => $id));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $user):
                    return sanitize($user->username);
                endforeach;
            endif;
    }

    // theme changer
    function theme() {
        $themes = array();
            foreach(glob('*/css/*.css') as $css):
                $themes[] = preg_replace('/^(.*?)\/css\/(.*?).css$/msi', '$2', $css);
            endforeach;
        if(isset($_POST['change_theme']) and isset($_POST['new_theme']) and in_array(sanitize($_POST['new_theme']), $themes)):
            $new_theme = sanitize($_POST['new_theme']);
            setcookie('theme', $new_theme, time()+(60*60*24*365));
            header('Location:'.seo($_SERVER['SCRIPT_NAME']));
            exit();
        endif;
            if(isset($_COOKIE['theme']) and in_array(sanitize($_COOKIE['theme']), $themes)):
                $theme = sanitize($_COOKIE['theme']);
            else:
                $theme = 'light';
            endif;
        if($theme == 'dark'):
            ini_set('highlight.default', '#3DD909');
            ini_set('highlight.keyword', '#04D8F3'); 
            ini_set('highlight.string', '#E5CC0B');
            ini_set('highlight.comment', '#FFAB01');
            ini_set('highlight.html', '#000000');
        endif;
        return $theme;
    }

    //theme changer form
    function theme_form(){
        $results = null;
        $themes = array();
        $results .= '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post">';
        $results .= '<select name="new_theme">';
            foreach(glob('*/css/*.css') as $num => $css):
                $themes[$num] = preg_replace('/^(.*?)\/css\/(.*?).css$/msi', '$2', $css);
                $results .= '<option class="theme-option" value="'.$themes[$num].'">'.$themes[$num].'</option>';
            endforeach;
        $results .= '</select>';
        $results .= '<input class="theme-submit-btn" type="submit" name="change_theme" value="set">';
        $results .= '</form>';
        return $results;
    }
?>