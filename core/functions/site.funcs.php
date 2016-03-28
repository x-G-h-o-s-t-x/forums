<?php
    /** function to turn errors on/off for debugging */
    function debug($true) {
        if($true == 'true'):
            ini_set('display_errors', '1');
            error_reporting(E_ALL | E_NOTICE | E_STRICT | E_DEPRECATED);
        elseif($true == 'false'):
            ini_set('display_errors', '0');
        endif;
    }

    /** function for cleaning inputted text */
    function sanitize($data) {
        $data = htmlentities(get_magic_quotes_gpc() ? stripslashes($data) : $data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    /** seo function */
    function seo($data) {
        //$config = db::mysqli()->config('SELECT * FROM `config`');
        db::pdo()->query('SELECT * FROM `config`');
        db::pdo()->execute();
        $config = db::pdo()->result()[0];
            if(preg_match('/true/i', $config->seo)):
                if(preg_match('/topic.php\?cid=(\w+)&amp;tid=(\w+)&amp;page=(\w+)/i', $data, $matches)):
                    $title = null;
                    //$links = db::mysqli()->query('SELECT * FROM `topics` WHERE `id`="'.db::mysqli()->sanitize($matches[2]).'" LIMIT 1');
                    db::pdo()->query('SELECT * FROM `topics` WHERE `id` = :id LIMIT 1');
                    db::pdo()->bind(':id', $matches[2]);
                    db::pdo()->execute();
                        if(db::pdo()->count() > 0):
                            foreach(db::pdo()->result() as $link):
                                $title = str_replace(' ', '-', str_replace(array('`', '&quot;'), '', sanitize($link->title)));
                            endforeach;
                        endif;
                    $data = preg_replace('/topic.php\?cid=(\w+)&amp;tid=(\w+)&amp;page=(\w+)/i', 'c$1/'.$title.'-$2/index$3.html', $data);
                    return $data;
                endif;
            else:
                return $data;
            endif;
    }

?>