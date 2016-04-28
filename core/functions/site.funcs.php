<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'site.funcs.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

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
        db::pdo()->query('SET time_zone = :timezone;');
            db::pdo()->bind(array(':timezone' => $offset));
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

    // site statistics
    function statistics() {
        $results = null;
        $results .= '<div class="stats">';
        db::pdo()->query('SELECT * FROM `topics`');
        db::pdo()->execute();
        $results .= 'Topics <span class="stats-info">'.db::pdo()->count().'</span>';
        db::pdo()->query('SELECT * FROM `posts`');
        db::pdo()->execute();
        $results .= 'Posts <span class="stats-info">'.db::pdo()->count().'</span>';
        db::pdo()->query('SELECT * FROM `users`');
        db::pdo()->execute();
        $results .= 'Members <span class="stats-info">'.db::pdo()->count().'</span>';
        db::pdo()->query('SELECT * FROM `users` ORDER BY `id` DESC LIMIT 1');
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $newest):
                    $username = sanitize($newest->username);
                endforeach;
            endif;
        $results .= 'New Member <span class="stats-info">'.$username.'</span>';
        $results .= '</div>';
        return $results;
    }

    // functions to display all members
    function members() {
        $results = null;
        pagination::init()->paginator('SELECT * FROM `users` ORDER BY `id` DESC', null, 20, 5, null);
        $results .= '<div class="members-header">Members</div>';
        $results .= '<div class="members-content">';
            if(pagination::init()->count() > 0):
                foreach(pagination::init()->result() as $user):
                    $results .= '('.$user->id.') - '.sanitize($user->username).'<hr class="members-list-hr"/>';
                endforeach;
            endif;
        $results .= '</div>';
        $results .= '<div class="members-content-pagination">'.pagination::init()->links().'</div>';
        return $results;
    }

    // Function that will get a users rank from their id
    function rank($id) {
        db::pdo()->query('SELECT * FROM `users` WHERE `id` = :id LIMIT 1');
            db::pdo()->bind(array(':id' => $id));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $users):
                    return $users->rank;
                endforeach;
            endif;
    }

    // Function that will convert a rank id into their rank name
    function rank_status($id) {
        db::pdo()->query('SELECT * FROM `ranks` WHERE `id` = :id LIMIT 1');
            db::pdo()->bind(array(':id' => $id));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $status):
                    return $status->rank;
                endforeach;
            endif;
    }

    // bbcodes
    function bbcodes($data) {
        $data = preg_replace_callback('/\[php\](.*?)\[\/php\]/msi', create_function('$matches', '{
            $matches[1] = html_entity_decode($matches[1], ENT_QUOTES, \'UTF-8\');
                if(preg_match(\'/^(<\?|<\?php)\s*?.*?\s*?^\?>\s*?$/msi\', $matches[1], $new_matches)):
                    $matches[1] = highlight_string($new_matches[0], 1);
                    $matches[1] = str_replace(array("<br />\r\n", "<br />\r", "<br />\n"), "", nl2br($matches[1]));
                else:
                    $matches[1] = highlight_string(\'<?php\'.$matches[1], 1);
                    $matches[1] = preg_replace(\'/&lt;\?php\s*?<br \/>(.*?<\/span>)/msi\', \'$1\', $matches[1]);
                    $matches[1] = str_replace(array("<br />\r\n", "<br />\r", "<br />\n"), "", nl2br($matches[1]));
                endif;
            return \'<div class="codebox">\'.$matches[1].\'</div>\';
        }') , $data);
        $bbcode = array(
            '\[url\](.*?)\[\/url\]' => '<a href="$1" target="_blank">$1</a>',
            '\[url\=(.*?)\](.*?)\[\/url\]' => '<a href="$1" target="_BLANK">$2</a>',
            '\[color\=(.*?)\](.*?)\[\/color\]' => '<span style="color:$1">$2</span>',
            '\[h1\](.*?)\[\/h1\]' => '<h1>$1</h1>',
            '\[h2\](.*?)\[\/h2\]' => '<h2>$1</h2>',
            '\[h3\](.*?)\[\/h3\]' => '<h3>$1</h3>',
            '\[h4\](.*?)\[\/h4\]' => '<h4>$1</h4>',
            '\[h5\](.*?)\[\/h5\]' => '<h5>$1</h5>',
            '\[h6\](.*?)\[\/h6\]' => '<h6>$1</h6>',
            '\[small\](.*?)\[\/small\]' => '<small>$1</small>',
            '\[b\](.*?)\[\/b\]' => '<b>$1</b>',
            '\[i\](.*?)\[\/i\]' => '<i>$1</i>',
            '\[u\](.*?)\[\/u\]' => '<u>$1</u>'
        );
            foreach($bbcode as $from => $to):
                $data = preg_replace('/'.$from.'/msi', $to, $data);
            endforeach;
        return nl2br($data);
    }

    // theme changer
    function theme() {
        $themes = array();
            foreach(glob('*/css/*.css') as $css):
                $themes[] = preg_replace('/^(.*?)\/css\/(.*?).css$/msi', '$2', $css);
            endforeach;
        if(isset($_POST['change_theme']) and isset($_POST['new_theme']) and in_array(sanitize($_POST['new_theme']), $themes)):
            $new_theme = sanitize($_POST['new_theme']);
            setcookie('theme', $new_theme, time() +3600 * 24 * 365, '/');
            header('Location:'.seo($_SERVER['REQUEST_URI']));
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
        $results .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';
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