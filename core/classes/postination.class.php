<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'postination.class.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class postination {
 
    protected function __construct() { /** Thou shalt not construct that which is unconstructable! */ }
    protected function __clone() { /** Me not like clones! Me smash clones! */ }
    public function __wakeup() { throw new Exception('Cannot unserialize singleton'); }
    private static $instance;

    // call to start the postination instance
    public static function init() {
        $class = get_called_class(); // late-static-bound class name
            if(!isset(self::$instance[$class])):
                self::$instance[$class] = new static;
            endif;
        return self::$instance[$class];
    }

    /** function to return latest topic post location (returns array() of latest topic post/page)
      * @example postination::init()->latest_topic_posts(category_id, topic_id, topic_date, posts_per_page)
      *
      * @usage $postination = postination::init()->latest_topic_posts($topic_post->category_id, $topics->topic_id, $topics->date, self::config()->posts_per_page);
      * @usage $latest_topic_post_id = $postination[0];
      * @usage $page = $postination[1];
      */
    public static function latest_topic_posts($cid, $tid, $post_date, $topics_per_page) {
        $id = null;
        $page = null;
        db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid AND `date` = :post_date LIMIT 1');
            db::pdo()->bind(array(':cid' => $cid, ':tid' => $tid, ':post_date' => $post_date));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $post):
                    $id = $post->id;
                    db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid AND `id` < :id');
                        db::pdo()->bind(array(':cid' => $cid, ':tid' => $tid, ':id' => $id));
                    db::pdo()->execute();
                    $row_number = db::pdo()->count() + 1;
                        if($row_number <= $topics_per_page):
                            $page = 1;
                        else:
                            $page = ceil($row_number / $topics_per_page);
                        endif;
                endforeach;
            endif;
        return array($id, $page);
    }
    
    /** function to return last post location (returns array() of last post/page)
      * @example postination::init()->last_post(category_id, topic->id, posts_per_page)
      *
      * @usage $postination = postination::init()->last_post($category_id, $topic->id, self::config()->posts_per_page);
      * @usage $last_post_id = $postination[0];
      * @usage $page = $postination[1];
      */
    public static function last_post($cid, $tid, $topics_per_page) {
        $last_post = null;
        $page = null;
        db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid');
            db::pdo()->bind(array(':cid' => $cid, ':tid' => $tid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                $page = ceil(db::pdo()->count() / $topics_per_page);
                db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid ORDER BY `date` DESC LIMIT 1');
                    db::pdo()->bind(array(':cid' => $cid, ':tid' => $tid));
                db::pdo()->execute();
                    foreach(db::pdo()->result() as $post):
                        $last_post = $post->id;
                    endforeach;
            endif;
        return array($last_post, $page);
    }

    /** function to return first post location (returns array() of first post/page)
      * @example postination::init()->first_post(category_id, topic->id)
      *
      * @usage $postination = postination::init()->first_post($category_id, $topic->id);
      * @usage $first_post_id = $postination[0];
      * @usage $page = $postination[1];
      */
    public static function first_post($cid, $tid) {
        $first_post = null;
        $page = null;
        db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid LIMIT 1');
            db::pdo()->bind(array(':cid' => $cid, ':tid' => $tid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $post):
                    $first_post = $post->id;
                    $page = 1;
                endforeach;
            endif;
        return array($first_post, $page);
    }
}
?>