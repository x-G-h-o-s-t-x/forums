<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'online.class.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class online {

    protected function __construct() { /** Thou shalt not construct that which is unconstructable! */ }
    protected function __clone() { /** Me not like clones! Me smash clones! */ }
    public function __wakeup() { throw new Exception('Cannot unserialize singleton'); }
    private static $instance;

    // call to start the online instance
    public static function init() {
        $class = get_called_class(); // late-static-bound class name
            if(!isset(self::$instance[$class])):
                self::$instance[$class] = new static;
            endif;
        return self::$instance[$class];
    }

    // function to check if a user is online
    public function check_online() {
    $uid = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    $page = preg_replace(array('/&page=\w/i', '/index\w\.html/i', '/\?page=\w/i'), '', $_SERVER['REQUEST_URI']);
        if(user::init()->is_authentic()):
            db::pdo()->query('SELECT * FROM `online` WHERE `uid` = :uid AND `member` = "yes" LIMIT 1');
                db::pdo()->bind(array(':uid' => $uid));
            db::pdo()->execute();
                if(db::pdo()->count() > 0):
                    db::pdo()->query('UPDATE `online` SET `time` = now(), `page` = :page WHERE `uid` = :uid AND `member` = "yes" LIMIT 1');
                        db::pdo()->bind(array(':page' => $page, ':uid' => $uid));
                    db::pdo()->execute();
                else:
                    db::pdo()->query('INSERT INTO `online` SET `uid` = :uid, `time` = now(), `page` = :page, `ip` = :ip, `member` = "yes"');
                        db::pdo()->bind(array(':uid' => $uid, ':page' => $page, ':ip' => $ip));
                    db::pdo()->execute();
                endif;
        else:
            db::pdo()->query('SELECT * FROM `online` WHERE `uid` = "0" AND `ip` = :ip AND `member` = "no" LIMIT 1');
                db::pdo()->bind(array(':ip' => $ip));
            db::pdo()->execute();
                if(db::pdo()->count() > 0):
                    db::pdo()->query('UPDATE `online` SET `time` = now(), `page` = :page WHERE `uid` = "0" AND `ip` = :ip AND `member` = "no" LIMIT 1');
                        db::pdo()->bind(array(':page' => $page, ':ip' => $ip));
                    db::pdo()->execute();
                else:
                    db::pdo()->query('INSERT INTO `online` SET `uid` = "0", `time` = now(), `page` = :page, `ip` = :ip, `member` = "no"');
                        db::pdo()->bind(array(':page' => $page, ':ip' => $ip));
                    db::pdo()->execute();
                endif;
        endif;
    }

    // function to check if user has gone offline
    public function check_offline() {
        db::pdo()->query('DELETE FROM `online` WHERE TIMESTAMPDIFF(MINUTE, time, now()) > 5');
        db::pdo()->execute();
    }

    // function to check if user has logged offline
    public function log_offline() {
        db::pdo()->query('DELETE FROM `online` WHERE `uid` = :uid LIMIT 1');
            db::pdo()->bind(array(':uid' => $_SESSION['uid']));
        db::pdo()->execute();
    }

    // function to display all users online
    public function display_all() {
        db::pdo()->query('SELECT * FROM `online` WHERE `uid` <> 0 AND `member` = "yes"');
        db::pdo()->execute();
            if(db::pdo()->count() == '1'):
                $members = '('.db::pdo()->count().') Member';
            else:
                $members = '('.db::pdo()->count().') Members';
            endif;
        db::pdo()->query('SELECT * FROM `online` WHERE `uid` = "0" AND `member` = "no"');
        db::pdo()->execute();
            if(db::pdo()->count() == '1'):
                $guests = '('.db::pdo()->count().') Guest';
            else:
                $guests = '('.db::pdo()->count().') Guests';
            endif;
        $results = null;
            $results .= '<div class="online-header">'."\r\n".'Currently '.$members.' And '.$guests.' Online'."\r\n".'</div>'."\r\n";
            $results .= '<div class="online">'."\r\n";   
        db::pdo()->query('SELECT * FROM `online` WHERE `uid` <> 0 AND `member` = "yes"');
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $user):
                    $results .= username($user->uid).', ';
                endforeach;
            endif;
            $results .= "\r\n".'</div>'."\r\n";
        return $results;
    }

    // function to display users on a particular page
    public function display_page() {
        $page = preg_replace(array('/&page=\w/i', '/index\w\.html/i', '/\?page=\w/i'), '', $_SERVER['REQUEST_URI']);
        db::pdo()->query('SELECT * FROM `online` WHERE `uid` <> 0 AND `member` = "yes" AND `page` = :page');
            db::pdo()->bind(array(':page' => $page));
        db::pdo()->execute();
            if(db::pdo()->count() == '1'):
                $members = '('.db::pdo()->count().') Member';
            else:
            
                $members = '('.db::pdo()->count().') Members';
            endif;
        db::pdo()->query('SELECT * FROM `online` WHERE `uid` = "0" AND `member` = "no" AND `page` = :page');
            db::pdo()->bind(array(':page' => $page));
        db::pdo()->execute();
            if(db::pdo()->count() == '1'):
                $guests = '('.db::pdo()->count().') Guest';
            else:
                $guests = '('.db::pdo()->count().') Guests';
            endif;
        $results = null;
            $results .= '<div class="online-header">'."\r\n".'Currently '.$members.' And '.$guests.' Viewing'."\r\n".'</div>'."\r\n";
            $results .= '<div class="online">'."\r\n";   
        db::pdo()->query('SELECT * FROM `online` WHERE `uid` <> 0 AND `member` = "yes" AND `page` = :page');
            db::pdo()->bind(array(':page' => $page));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $user):
                    $results .= username($user->uid).', ';
                endforeach;
            endif;
            $results .= "\r\n".'</div>'."\r\n";
        return $results;
    }
}
?>