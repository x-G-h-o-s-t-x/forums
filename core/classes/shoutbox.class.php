<?php
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

    public static function display() {
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
                    $results .= '['.gmdate('h:ia', strtotime($shout->time)).'] '.sanitize($shout->username).': '.sanitize($shout->shout).'<br/>'."\r\n";
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
}
?>