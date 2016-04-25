<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'staff.class.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class staff {

    protected function __construct() {/** Thou shalt not construct that which is unconstructable! */}
    protected function __clone() {/** Me not like clones! Me smash clones! */}
    public function __wakeup() {throw new Exception("Cannot unserialize singleton");}
    private static $instance;

    // call to start the staff instance
    public static function init() {
        $class = get_called_class(); // late-static-bound class name
            if(!isset(self::$instance[$class])):
                self::$instance[$class] = new static;
            endif;
        return self::$instance[$class];
    }

    public static function config() {
        db::pdo()->query('SELECT * FROM `config`');
        db::pdo()->execute();
        return db::pdo()->result()[0];
    }

    public static function perm($perm) {
        if(user::init()->is_authentic()):
            $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
            db::pdo()->query('SELECT * FROM `users` WHERE `id` = :id LIMIT 1');
                db::pdo()->bind(array(':id' => $id));
            db::pdo()->execute();
                if(db::pdo()->count() > 0):
                    foreach(db::pdo()->result() as $user):
                        if($user->rank > 0 and $user->rank <= 3):
                            db::pdo()->query('SELECT * FROM `staff` WHERE `uid` = :uid AND `rank` = :rank LIMIT 1');
                                db::pdo()->bind(array(':uid' => $id, ':rank' => $user->rank));
                            db::pdo()->execute();
                                if(db::pdo()->count() > 0):
                                    foreach(db::pdo()->result() as $staff):
                                        if($staff->$perm == 'true'):
                                            return true;
                                        else:
                                            return false;
                                        endif;
                                    endforeach;
                                endif;
                        endif;
                    endforeach;
                endif;
        endif;
    }

    public static function navbar() {
        $results = null;
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
        if(rank($id) == '1'):
            $results .= '<div class="staff-cpanel-nav-header">Staff Cpanel</div>';
            $results .= '<div class="staff-cpanel-nav-content">';
            $results .= '<ul>';
            $results .= '<li><a href="?act=site_config">Site Config</a></li>';
            $results .= '</ul>';
            $results .= '</div>';
        endif;
        return $results;
    }
    
    public static function navigation() {
        $results = null;
        $id = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
        $act = isset($_GET['act']) ? sanitize($_GET['act']) : null;
            if($act == 'site_config' and rank($id) == '1'):
                if(isset($_POST['update_config'])):
                    $results .= self::update_config();
                endif;
                $results .= self::update_config_form();
            endif;
        return $results;
    }
    
    public static function update_config_form() {
        $results = null;
        $config_description = array(
            'site_name' => 'Site Name:',
            'site_logo' => 'Site Logo:',
            'announcement' => 'Announcement Board:',
            'timezone' => 'Timezone: (Default: Europe/London)<br/>full list of <a href="http://php.net/manual/en/timezones.php" target="_blank">Supported timezones</a>',
            'debug' => 'Debug: (true/false)<br/>turns error reporting on/off',
            'seo' => 'Seo: (true/false)',
            'topics_per_page' => 'Topics Per Page:',
            'posts_per_page' => 'Posts Per Page:',
            'auto_delete_shouts' => 'Auto Delete Shouts: (true/false)',
            'show_shouts_to_guests' => 'Show Shouts To Guests: (true/false)',
        );

        $results .= '<div class="staff-cpanel-main-header">Site Config</div>';
        $results .= '<div class="staff-cpanel-main-content">';
        $results .= '<form action="?act=site_config" method="post">';
        $results .= '<table>';
            foreach(self::config() as $key => $value):
                $results .= '<tr>';
                $results .= '<td class="td1">'.$config_description[$key].'<br/>'.'</td>';
                $results .= '<td class="td2"><input class="staff-site-config-input" type="text" name="'.$key.'" value="'.(isset($value) ? $value : '').'"/><br/></td>';
                $results .= '</tr>';
            endforeach;
        $results .= '</table>';
        $results .= '<input class="staff-site-config-btn" type="submit" name="update_config" value="update config" />';
        $results .= '</form>';
        $results .= '</div>';
        return $results;
    }

    public static function update_config() {
        foreach($_POST as $key => $value):
            if($key != 'update_config'):
                db::pdo()->query('UPDATE `config` SET :key = :value');
                    db::pdo()->bind(array(':key' => $key, ':value' => $value));
                db::pdo()->execute();
            endif;
        endforeach;
        return header('Location:?act=site_config');
    }

    public static function del_topic_btn($cid, $tid, $post_date) {
        $results = null;
        db::pdo()->query('SELECT `date` FROM `topics` WHERE `date` = :date AND `id` = :id LIMIT 1');
            db::pdo()->bind(array(':date' => $post_date, ':id' => $tid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $topic):
                    $topic_date = $topic->date;
                endforeach;
            endif;
        if(isset($topic_date) == $post_date):
            $results .= '<form class="staff-delete" action="topic.php" method="post">';
            $results .= '<input type="hidden" name="cid" value="'.$cid.'" />';
            $results .= '<input type="hidden" name="tid" value="'.$tid.'" />';
            $results .= '<input class="staff-delete-btn" type="submit" name="delete_topic" value="Delete Topic"/>';
            $results .= '</form>';
        endif;
        return $results;
    }

    public static function del_post_btn($cid, $tid, $post_date, $post_id) {
        $results = null;
        db::pdo()->query('SELECT `date` FROM `topics` WHERE `date` = :date AND `id` = :id LIMIT 1');
            db::pdo()->bind(array(':date' => $post_date, ':id' => $tid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $topic):
                    $topic_date = $topic->date;
                endforeach;
            endif;
        if(!isset($topic_date) == $post_date):
            $results .= '<form class="staff-delete" action="topic.php" method="post">';
            $results .= '<input type="hidden" name="cid" value="'.$cid.'" />';
            $results .= '<input type="hidden" name="tid" value="'.$tid.'" />';
            $results .= '<input type="hidden" name="pid" value="'.$post_id.'" />';
            $results .= '<input class="staff-delete-btn" type="submit" name="delete_post" value="Delete Post"/>';
            $results .= '</form>';
        endif;
        return $results;
    }

    public static function delete_topic() {
        $tid = isset($_POST['tid']) ? (int)$_POST['tid'] : 0;
        db::pdo()->query('DELETE FROM `topics` WHERE `id` = :id');
            db::pdo()->bind(array(':id' => $tid));
        $topics = db::pdo()->execute();
        db::pdo()->query('DELETE FROM `posts` WHERE `topic_id` = :tid');
            db::pdo()->bind(array(':tid' => $tid));
        $posts = db::pdo()->execute();
            if(isset($topics) and isset($posts)) {
                return true;
            } else {
                return false;
            }
    }

    public static function delete_post() {
        $tid = isset($_POST['tid']) ? (int)$_POST['tid'] : 0;
        $pid = isset($_POST['pid']) ? (int)$_POST['pid'] : 1;
        db::pdo()->query('DELETE FROM `posts` WHERE `id` = :pid AND `topic_id` = :tid');
            db::pdo()->bind(array(':pid' => $pid, ':tid' => $tid));
        $posts = db::pdo()->execute();
            if(isset($posts)) {
                return true;
            } else {
                return false;
            }
    }
}
?>