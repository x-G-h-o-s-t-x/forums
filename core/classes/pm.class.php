<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'pm.class.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class pm {

    protected function __construct() { /** Thou shalt not construct that which is unconstructable! */ }
    protected function __clone() { /** Me not like clones! Me smash clones! */ }
    public function __wakeup() { throw new Exception('Cannot unserialize singleton'); }
    private static $instance;

    // call to start the pm instance
    public static function init() {
        $class = get_called_class(); // late-static-bound class name
            if(!isset(self::$instance[$class])):
                self::$instance[$class] = new static;
            endif;
        return self::$instance[$class];
    }

    //private navbar
    public static function navbar() {
        $results = null;
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
        $results .= '<div class="private-nav-header">Private Messages</div>';
        $results .= '<div class="private-nav-content">';
        $results .= '<ul>';
        $results .= '<li><a href="?act=compose">Compose</a></li>';
        db::pdo()->query('SELECT * FROM `private` WHERE `to` = :to AND `read` = "no" LIMIT 50');
            db::pdo()->bind(array(':to' => $id));
        db::pdo()->execute();
            if(db::pdo()->count() == 0):
                $results .= '<li><a href="?act=inbox">Inbox</a></li>';
            else:
                $results .= '<li><b><a href="?act=inbox">Inbox ('.db::pdo()->count().')</a></b></li>';
            endif;
        $results .= '<li><a href="?act=outbox">Outbox</a></li>';
        $results .= '</ul>';
        $results .= '</div>';
        return $results;
    }

    //private navigation
    public static function navigation() {
        $results = null;
        $act = isset($_GET['act']) ? sanitize($_GET['act']) : null;
            if($act == '' or $act == 'inbox'):
                $results .= self::inbox();
            elseif($act == 'outbox'):
                $results .= self::outbox();
            elseif($act == 'read'):
                $results .= self::private_message();
            elseif($act == 'compose'):
                $results .= '<div class="private-compose-header">Compose</div>';
                $results .= '<div class="private-compose-content">';
                    if(isset($_POST['submit'])):
                        if($_POST['to'] == '' || $_POST['title'] == '' || $_POST['content'] == ''):
                            $results .= 'You did not fill in all fields.<br/><br/>'.self::compose_form();
                        else:
                            $results .= self::compose();
                        endif;
                    else:
                        $results .= self::compose_form();
                    endif;
                $results .= '</div>';
            endif;
        return $results;
    }

    //compose private
    public static function compose() {
        $from = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
        $to = isset($_POST['to']) ? $_POST['to'] : null;
        $title = isset($_POST['title']) ? $_POST['title'] : null;
        $content = isset($_POST['content']) ? $_POST['content'] : null;
        db::pdo()->query('SELECT * FROM `users` WHERE `username` = :username LIMIT 1');
            db::pdo()->bind(array(':username' => $to));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $user):
                    $id = $user->id;
                endforeach;
            else:
               return 'the user you are trying to contact does not exist in our database';
            endif;
        db::pdo()->query('INSERT INTO `private` (`to`, `from`, `title`, `content`, `date`, `read`, `delete_to`, `delete_from`) VALUES (:to, :from, :title, :content, now(), "no", "no", "no")');
            db::pdo()->bind(array(':to' => $id, ':from' => $from, ':title' => $title, ':content' => $content));
        $compose = db::pdo()->execute();
            if($compose):
                return header("Location:?act=inbox");
            else:
                return 'There was a problem creating your private message. Please try again.';
            endif;
    }

    //compose private form
    public static function compose_form() {
        $results = null;
        $results .= '<form action="?act=compose" method="post">';
        $results .= 'Recipient<br/>';
        $results .= '<input class="private-compose-input" type="text" name="to" maxlength="150" /><br/>';
        $results .= 'Title<br/>';
        $results .= '<input class="private-compose-input" type="text" name="title" maxlength="150" /><br/>';
        $results .= 'Content<br/>';
        $results .= '<textarea class="private-compose-textarea" name="content" rows="10" cols="75"></textarea><br/>';
        $results .= '<input class="private-compose-btn" type="submit" name="submit" value="Send Private" />';
        $results .= '</form>';
        return $results;
    }

    //inbox
    public static function inbox() {
        self::delete_inbox();
        $results = null;
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
        $results .= '<div class="private-main-header">Inbox</div>';
        $query = 'SELECT * FROM `private` WHERE `to` = :to AND `delete_to` <> "yes" ORDER BY `date` DESC';
        $bind = array(':to' => $id);
        pagination::init()->paginator($query, $bind, 10, 5, 'act=inbox&amp;');
            if(pagination::init()->count() > 0):
                $results .= '<form action="" method="post">';
                    foreach(pagination::init()->result() as $private):
                        $results .= '<div class="private-main-content">';
                            if($private->read == 'no'):
                                $results .= '<input type="checkbox" name="id'.$private->id.'" value="'.$private->id.'" /> ';
                                $results .= '<b><a href="?act=read&amp;pid='.$private->id.'">'.sanitize($private->title).'</a></b> - (unread)<br/>';
                            else:
                                $results .= '<input type="checkbox" name="id'.$private->id.'" value="'.$private->id.'" /> ';
                                $results .= '<a href="?act=read&amp;pid='.$private->id.'">'.sanitize($private->title).'</a><br/>';
                            endif;
                        $results .= '<span>Sent from '.username($private->from).', '.convert_datetime($private->date).'</span><br/>';
                        $results .= '</div>';
                    endforeach;
                $results .= '<div class="private-main-footer">';
                $results .= '<div class="left">';
                $results .= '<input class="private-delete-btn" type="submit" name="delete_inbox" value="Delete" />';
                $results .= '</div>';
                $results .= '<div class="right">';
                $results .= pagination::init()->links();
                $results .= '</div>';
                $results .= '<div class="padder"></div>';
                $results .= '</div>';
                $results .= '</form>';
            else:
        $results .= '<div class="private-main-content">You have no messages in your inbox.<br/></div>';
        $results .= '<div class="private-main-footer"></div>';
            endif;
        return $results;
    }

    //outbox
    public static function outbox() {
        self::delete_outbox();
        $results = null;
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
        $results .= '<div class="private-main-header">Outbox</div>';
        $query = 'SELECT * FROM `private` WHERE `from` = :from AND `delete_from` <> "yes" ORDER BY `date` DESC';
        $bind = array(':from' => $id);
        pagination::init()->paginator($query, $bind, 10, 5, 'act=sent&amp;');
            if(pagination::init()->count() > 0):
                $results .= '<form action="" method="post">';
                    foreach(pagination::init()->result() as $private):
                        $results .= '<div class="private-main-content">';
                        $results .= '<input type="checkbox" name="id'.$private->id.'" value="'.$private->id.'" /> ';
                        $results .= '<a href="?act=read&amp;pid='.$private->id.'">'.sanitize($private->title).'</a><br/>';
                        $results .= '<span>Sent to '.username($private->to).', '.convert_datetime($private->date).'</span><br/>';
                        $results .= '</div>';
                    endforeach;
                $results .= '<div class="private-main-footer">';
                $results .= '<div class="left">';
                $results .= '<input class="private-delete-btn" type="submit" name="delete_outbox" value="Delete" />';
                $results .= '</div>';
                $results .= '<div class="right">';
                $results .= pagination::init()->links();
                $results .= '</div>';
                $results .= '<div class="padder"></div>';
                $results .= '</div>';
                $results .= '</form>';
            else:
        $results .= '<div class="private-main-content">You have no messages in your outbox.<br/></div>';
        $results .= '<div class="private-main-footer"></div>';
            endif;
        return $results;
    }

    //delete privates from users inbox
    public static function delete_inbox() {
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
            if(isset($_POST['delete_inbox'])):
                foreach($_POST as $key => $value):
                        if($key != 'delete_inbox'):
                            db::pdo()->query('UPDATE `private` SET `delete_to` = "yes" WHERE `id` = :id AND `to` = :to LIMIT 1');
                                db::pdo()->bind(array(':id' => $value, ':to' => $id));
                            db::pdo()->execute();
                        endif;
                endforeach;
                self::delete_privates();
                return header("Location:?act=inbox");
            endif;
    }

    //delete privates from users outbox
    public static function delete_outbox() {
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
            if(isset($_POST['delete_outbox'])):
                foreach($_POST as $key => $value):
                        if($key != 'delete_outbox'):
                            db::pdo()->query('UPDATE `private` SET `delete_from` = "yes" WHERE `id` = :id AND `from` = :from LIMIT 1');
                                db::pdo()->bind(array(':id' => $value, ':from' => $id));
                            db::pdo()->execute();
                        endif;
                endforeach;
                self::delete_privates();
                return header("Location:?act=outbox");
            endif;
    }

    //completely remove privates from database once both sender and recipient has opted to delete them.
    public static function delete_privates() {
        db::pdo()->query('SELECT * FROM `private` WHERE `delete_to` = "yes" AND `delete_from` = "yes"');
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $private):
                    db::pdo()->query('DELETE FROM `private` WHERE `id` = :id');
                        db::pdo()->bind(array(':id' => $private->id));
                    db::pdo()->execute();
                endforeach;
            endif;
    }

    //private announcement
    public static function announcement() {
        $results = null;
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
            if(user::init()->is_authentic()):
                db::pdo()->query('SELECT * FROM `private` WHERE `to` = :to AND `read` = "no" LIMIT 50');
                        db::pdo()->bind(array(':to' => $id));
                    db::pdo()->execute();
                    if(db::pdo()->count() > 0):
                        if(db::pdo()->count() == 1):
                            $results .= '<div class="private-announcement">';
                            $results .= '<a href="'.seo('cpanel.php').'">** You Have ('.db::pdo()->count().') New Private Message **</a>';
                            $results .= '</div>';
                        else:
                            $results .= '<div class="private-announcement">';
                            $results .= '<a href="'.seo('cpanel.php').'">** You Have ('.db::pdo()->count().') New Private Messages **</a>';
                            $results .= '</div>';
                        endif;
                    endif;
            endif;
        return $results;
    }

    public static function private_message() {
        $results = null;
        $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
        db::pdo()->query('SELECT * FROM `private` WHERE `id` = :id LIMIT 1');
            db::pdo()->bind(array(':id' => $pid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $private):
                    if($private->to == $id or $private->from == $id):
                        $results .= '<div class="private-content-header">';
                        $results .= '<span>'.convert_datetime($private->date).'</span>';
                        $results .= '</div>';
                        $results .= '<div class="private-content">';
                        $results .= '<div class="private-content-left">';
                        $results .= self::private_message_user($private->from);
                        $results .= '</div>';
                        $results .= '<div class="private-content-right">';
                        $results .= bbcodes(sanitize($private->content));
                        $results .= '</div>';
                        $results .= '</div>';
                        $private->title = preg_replace('/^RE:\s(.*?)$/i', '\\1', $private->title);
                        $results .= '<div class="private-content-footer"></div>';
                            if(user::init()->is_authentic()):
                                $results .= '<div class="private-footer-reply">';
                                $results .= '<form action="?act=compose" method="post">';
                                $results .= 'Reply To Private<br/>';
                                $results .= '<input type="hidden" name="title" value="RE: '.sanitize($private->title).'" />';
                                $results .= '<input type="hidden" name="to" value="'.username($private->from).'" />';
                                $results .= '<textarea name="content" rows="15" cols="75"></textarea><br/>';
                                $results .= '<input class="private-footer-reply-btn" type="submit" name="submit" value="Reply" />';
                                $results .= '</form>';
                                $results .= '</div>';
                            endif;
                        db::pdo()->query('UPDATE `private` SET `read` = "yes" WHERE `id` = :id LIMIT 1');
                            db::pdo()->bind(array(':id' => $pid));
                        db::pdo()->execute();
                    else:
                        $results .= 'you are trying to view a private message that does NOT belong to you<br/>';
                    endif;
                endforeach;
            else:
                $results .= 'you are trying to view a private message that does not exist in the database<br/>';
            endif;
        return $results;
    }

    public static function private_message_user($id) {
        $results = null;
        db::pdo()->query('SELECT * FROM `users` WHERE `id` = :id');
            db::pdo()->bind(array(':id' => $id));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $user):
                    if($user->avatar != null):
                        $results .= '<div class="user-img-div">';
                        $results .= '<img src="'.sanitize($user->avatar).'" alt="" height="100" width="100"/><br/>';
                        $results .= '</div>';
                    endif;
                    $rank = $user->rank;
                endforeach;
            endif;
        db::pdo()->query('SELECT * FROM `online` WHERE `uid` = :id AND `member` = "yes"');
            db::pdo()->bind(array(':id' => $id));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                $online = '<img src="core/images/online.png" alt="" height="10" width="10"/>';
            else:
                $online = '<img src="core/images/offline.png" alt="" height="10" width="10"/>';
            endif;
        $results .= '<b>'.username($id).'</b> '.$online.'<br/>';
        $results .= rank_status($rank);
        $results .= '<div class="user-stats-div"><br/>';
        db::pdo()->query('SELECT * FROM `topics` WHERE `creator` = :id');
            db::pdo()->bind(array(':id' => $id));
        db::pdo()->execute();
        $results .= 'topics made: '.db::pdo()->count().'<br/>';
        db::pdo()->query('SELECT * FROM `posts` WHERE `creator` = :id');
            db::pdo()->bind(array(':id' => $id));
        db::pdo()->execute();
        $results .= 'posts made: '.db::pdo()->count().'<br/><br/>';
        $results .= '</div>';
        return $results;
    }
}
?>