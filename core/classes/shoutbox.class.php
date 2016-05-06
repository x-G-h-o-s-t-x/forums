<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'shoutbox.class.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class shoutbox {

    protected function __construct() { /** Thou shalt not construct that which is unconstructable! */ }
    protected function __clone() { /** Me not like clones! Me smash clones! */ }
    public function __wakeup() { throw new Exception('Cannot unserialize singleton'); }
    private static $instance;

    // call to start the forums instance
    public static function init() {
        $class = get_called_class(); // late-static-bound class name
            if(!isset(self::$instance[$class])):
                self::$instance[$class] = new static;
            endif;
        return self::$instance[$class];
    }

    /** return site config to use with class */
    public static function config() {
        db::pdo()->query('SELECT * FROM `config`');
        db::pdo()->execute();
        return db::pdo()->result()[0];
    }

    public static function shout($username, $shout) {
        if(!user::init()->is_authentic() or $shout == '' or isset($_SESSION['shout']) and strcasecmp($_SESSION['shout'], $shout) == 0):
            return '';
        else:
            db::pdo()->query('INSERT INTO `shouts` (`id`, `username`, `shout`, `time`) VALUES (NULL, :username, :shout, now())');
                db::pdo()->bind(array(':username' => $username, ':shout' => $shout));
            db::pdo()->execute();
        endif;
    }

    public static function shoutouts() {
        $results = null;
            if(preg_match('/true/i', self::config()->auto_delete_shouts)):
                db::pdo()->query('SELECT * FROM `shouts` ORDER BY `id` DESC');
                db::pdo()->execute();
                    if(db::pdo()->count() > 0):
                        db::pdo()->query('DELETE FROM `shouts` WHERE TIMESTAMPDIFF(HOUR, time, now()) > 24');
                        db::pdo()->execute();
                    endif;
            endif;
        db::pdo()->query('SELECT * FROM `shouts` ORDER BY `id` DESC LIMIT 30');
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $shout):
                    $results .= '['.date('h:ia', strtotime($shout->time)).'] '.sanitize($shout->username).': '.sanitize($shout->shout).'<br/>'."\r\n";
                endforeach;
            else:
                db::pdo()->query('SHOW TABLE STATUS LIKE "shouts"');
                db::pdo()->execute();
                    foreach(db::pdo()->result() as $table):
                        if($table->Auto_increment != 1):
                            db::pdo()->query('ALTER TABLE `shouts` AUTO_INCREMENT=1');
                            db::pdo()->execute();
                        endif;
                    endforeach;
            endif;
        return $results;
    }

    public static function shoutbox() {
        $results = null;
            if(!user::init()->is_authentic() and preg_match('/true/i', self::config()->show_shouts_to_guests) or user::init()->is_authentic()):
                if(user::init()->is_authentic() and isset($_POST['shout'])):
                    self::shout($_SESSION['user'], $_POST['post']);
                    $_SESSION['shout'] = $_POST['post'];
                endif;
                $results .= '<script type="text/javascript" src="core/js/shout_idle.js"></script>'."\r\n";
                $results .= '<div class="wrapper">'."\r\n";
                $results .= '<script type="text/javascript" src="core/js/shout_hide.js"></script>'."\r\n";
                $results .= '<div class="shoutbox-header">'."\r\n";
                $results .= 'Shoutbox <a id="shoutbox-refresh" href="'.$_SERVER['SCRIPT_NAME'].'">[refresh]</a>'."\r\n";
                $results .= '</div>'."\r\n";
                $results .= '<div class="shoutbox-announcement" id="shoutbox-announcement"></div>'."\r\n";
                $results .= '<div class="shoutbox-content" id="shoutbox-content">'."\r\n";
                $results .= self::shoutouts();
                $results .= '</div>'."\r\n";
                $results .= '<div class="shoutbox-form" id="shoutbox-form">'."\r\n";
                    if(user::init()->is_authentic()):
                        $results .= '<script type="text/javascript" src="core/js/shout_hijack.js"></script>'."\r\n";
                        $results .= '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" id="shout_form">'."\r\n";
                        $results .= '<input class="shoutbox-input" type="text" name="post" id="post" value="" maxlength="300"/>'."\r\n";
                        $results .= '<input class="shoutbox-submit-btn" name="shout" id="shout" value="Shout" type="submit"/>'."\r\n";
                        $results .= '</form>'."\r\n";
                        $results .= '<script type="text/javascript" src="core/js/shout.js"></script>'."\r\n";
                    else:
                        $results .= 'You must be logged in to use the shoutbox'."\r\n";
                    endif;
                $results .= '</div>'."\r\n";
                $results .= '</div>'."\r\n";
            endif;
        return $results;
    }
}
?>