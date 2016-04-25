<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'user.class.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class user {

    protected function __construct() { /** Thou shalt not construct that which is unconstructable! */ }
    protected function __clone() { /** Me not like clones! Me smash clones! */ }
    public function __wakeup() { throw new Exception('Cannot unserialize singleton'); }
    private static $instance;

    // call to start the user instance
    public static function init() {
        $class = get_called_class(); // late-static-bound class name
            if(!isset(self::$instance[$class])):
                self::$instance[$class] = new static;
            endif;
        return self::$instance[$class];
    }

    // registration
    public static function registration($username, $password, $authentication) {
        db::pdo()->query('SELECT * FROM `users` WHERE `username` = :username LIMIT 1');
            db::pdo()->bind(array(':username' => $username));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                return false;
            elseif(strlen($username) > 25):
                return false;
            elseif(strlen($username) < 3):
                return false;
            else:
                $password = password_hash($password, PASSWORD_DEFAULT);
                $authentication = password_hash($authentication, PASSWORD_DEFAULT);
                $authkey = md5(sha1(substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 4)), 0, 248)));
                db::pdo()->query('INSERT INTO `users` (`id`, `username`, `password`, `authentication`, `authkey`) VALUES (NULL, :username, :password, :authentication, :authkey)');
                    $placeholders = array(':username' => $username, ':password' => $password, ':authentication' => $authentication, ':authkey' => $authkey);
                    db::pdo()->bind($placeholders);
                db::pdo()->execute();
                db::pdo()->query('SELECT * FROM `users` WHERE `username` = :username LIMIT 1');
                    db::pdo()->bind(array(':username' => $username));
                db::pdo()->execute();
                    if(db::pdo()->count() > 0):
                        foreach(db::pdo()->result() as $user):
                            $_SESSION['uid'] = $user->id;
                            $_SESSION['user'] = $user->username;
                            $_SESSION['authkey'] = md5(sha1($user->username)).md5(sha1($user->authkey)).md5(sha1($user->password));
                        endforeach;
                    endif;
                return true;
            endif;
    }

    // login
    public static function login($username, $password) {
        db::pdo()->query('SELECT * FROM `users` WHERE `username` = :username LIMIT 1');
            db::pdo()->bind(array(':username' => $username));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $user):
                    if(password_verify($password, $user->password)):
                        $_SESSION['uid'] = $user->id;
                        $_SESSION['user'] = $user->username;
                        $_SESSION['authkey'] = md5(sha1($user->username)).md5(sha1($user->authkey)).md5(sha1($user->password));
                            if(isset($_POST['remember'])):
                                setcookie('remember[id]', $user->id, time() +3600 * 24 * 365, '/');
                                setcookie('remember[user]', $user->username, time() +3600 * 24 * 365, '/');
                                setcookie('remember[authkey]', md5(sha1($user->authkey)), time() +3600 * 24 * 365, '/');
                            endif;
                        return true;
                    else:
                        return false;
                    endif;
                endforeach;
            else:
                return false;
            endif;
    }

    // logout
    public static function logout() {
        if(isset($_COOKIE['remember'])):
            setcookie('remember[id]', '', time() -3600 * 24 * 365, '/');
            setcookie('remember[user]', '', time() -3600 * 24 * 365, '/');
            setcookie('remember[authkey]', '', time() -3600 * 24 * 365, '/');
            unset($_COOKIE['remember']);
        endif;
        session_destroy();
        header('Location:'.seo('index.php'));
    }

    // forgotpassword
    public static function forgotpassword($username, $authentication, $password) {
        db::pdo()->query('SELECT * FROM `users` WHERE `username` = :username LIMIT 1');
            db::pdo()->bind(array(':username' => $username));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $user):
                    if(password_verify($authentication, $user->authentication)):
                        $password = password_hash($password, PASSWORD_DEFAULT);
                        db::pdo()->query('UPDATE `users` SET `password` = :password WHERE `username` = :username LIMIT 1');
                            db::pdo()->bind(array(':password' => $password, ':username' => $username));
                        db::pdo()->execute();
                        return true;
                    else:
                        return false;
                    endif;
                endforeach;
            else:
                return false;
            endif;
    }

    // function to help secure user session hijacking attempts
    public static function is_authentic() {
        if(isset($_SESSION['user']) and !empty($_SESSION['user'])):
            db::pdo()->query('SELECT * FROM `users` WHERE `username` = :username LIMIT 1');
                db::pdo()->bind(array(':username' => $_SESSION['user']));
            db::pdo()->execute();
                if(db::pdo()->count() > 0):
                    foreach(db::pdo()->result() as $user):
                        $authkey = md5(sha1($user->username)).md5(sha1($user->authkey)).md5(sha1($user->password));
                            if($_SESSION['authkey'] == $authkey and $_SESSION['uid'] == $user->id):
                                return true;
                            else:
                                return false;
                            endif;
                    endforeach;
                endif;
        endif;
    }

    // function to remember user if have selected to be remembered
    public static function is_remembered() {
        if(isset($_COOKIE['remember']) and !isset($_SESSION['user'])):
            db::pdo()->query('SELECT * FROM `users` WHERE `username` = :username LIMIT 1');
                db::pdo()->bind(array(':username' => $_COOKIE['remember']['user']));
            db::pdo()->execute();
                if(db::pdo()->count() > 0):
                    foreach(db::pdo()->result() as $user):
                            if($_COOKIE['remember']['authkey'] == md5(sha1($user->authkey)) and $_COOKIE['remember']['id'] == $user->id):
                                $_SESSION['uid'] = $user->id;
                                $_SESSION['user'] = $user->username;
                                $_SESSION['authkey'] = md5(sha1($user->username)).md5(sha1($user->authkey)).md5(sha1($user->password));
                                return true;
                            else:
                                return false;
                            endif;
                    endforeach;
                endif;
        endif;
    }
}
?>