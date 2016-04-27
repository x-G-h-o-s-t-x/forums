<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'pagination.class.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class forums {

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

    /** function to display announcement board */
    public static function announcement() {
        $results = null;
        $announcement = null;
        $results .= '<div class="announcement-board"><div>';
            if(self::config()->announcement == null):
                $announcement = sanitize(self::config()->announcement);
            else:
                $announcement = '<br/>'.sanitize(self::config()->announcement);
            endif;
        $results .= 'Announcement Board:'.$announcement;
        $results .= '</div></div>';
        return $results;
    }

    /** function to display forums */
    public static function forums() {
        $results = null;
        db::pdo()->query('SELECT * FROM `forums` ORDER BY `id` ASC');
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $forum):
                    $results .= '<div class="forums">'.sanitize($forum->title).'</div>'."\r\n";
                    $results .= self::categories($forum->id);
                endforeach;
            endif;
        return $results;
    }

    /** function to display categories (part of forums function) */
    public static function categories($forum_id) {
        $results = null;
        db::pdo()->query('SELECT * FROM `categories` WHERE `forum_id` = :forum_id ORDER BY `id` ASC');
            db::pdo()->bind(array(':forum_id' => $forum_id));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $category):
                    $results .= '<div class="categories">'."\r\n";
                    $results .= '<div class="categories-left">'."\r\n";
                    $results .= '<b><a href="category.php?cid='.$category->id.'">'.sanitize($category->title).'</a></b><br/>'."\r\n";
                    $results .= sanitize($category->description).'<br/>'."\r\n";
                    $results .= '</div>'."\r\n";
                    $results .= '<div class="categories-center">'."\r\n";
                    $results .= self::count_topics($category->id);
                    $results .= self::count_replies($category->id);
                    $results .= '</div>'."\r\n";
                    $results .= '<div class="categories-right">'."\r\n";
                    $results .= self::last_post($category->id)."\r\n";
                    $results .= '</div>'."\r\n";
                    $results .= '</div>'."\r\n";
                endforeach;
            endif;
        return $results;
    }

    /** function to display all topics count (part of categories function) */
    public static function count_topics($category_id) {
        $results = null;
        db::pdo()->query('SELECT * FROM `topics` WHERE `category_id` = :category_id');
            db::pdo()->bind(array(':category_id' => $category_id));
        db::pdo()->execute();
            if(db::pdo()->count() == 1):
                $results .= db::pdo()->count().' topic<br/>'."\r\n";
            else:
                $results .= db::pdo()->count().' topics<br/>'."\r\n";
            endif;
        return $results;
    }

    /** function to display all replies count (part of categories function) */
    public static function count_replies($category_id) {
        $results = null;
        db::pdo()->query('SELECT * FROM `topics` WHERE `category_id` = :category_id');
            db::pdo()->bind(array(':category_id' => $category_id));
        db::pdo()->execute();
        $topics_count = db::pdo()->count();
        db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :category_id');
            db::pdo()->bind(array(':category_id' => $category_id));
        db::pdo()->execute();
            if(db::pdo()->count() - $topics_count == 1):
                $results .= db::pdo()->count() - $topics_count.' reply<br/>'."\r\n";
            else:
                $results .= db::pdo()->count() - $topics_count.' replies<br/>'."\r\n";
            endif;
        return $results;
    }

    /** function to display topic reply count (part of category_topics/search_topics functions) */
    public static function count_topic_replies($cid, $tid) {
        db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid');
            db::pdo()->bind(array(':cid' => $cid, ':tid' => $tid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                return db::pdo()->count()-1;
            endif;
    }

    /** function to display last post of all categories (part of categories function) */
    public static function last_post($category_id) {
        $results = null;
        db::pdo()->query('SELECT * FROM `topics` WHERE `category_id` = :category_id ORDER BY `reply_date` DESC LIMIT 1');
            db::pdo()->bind(array(':category_id' => $category_id));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $topic):
                    $postination = postination::init()->last_post($category_id, $topic->id, self::config()->posts_per_page);
                    db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :category_id AND `id` = :id LIMIT 1');
                        db::pdo()->bind(array(':category_id' => $category_id, ':id' => $postination[0]));
                    db::pdo()->execute();
                        if(db::pdo()->count() > 0):
                            foreach(db::pdo()->result() as $topic_post):
                                $post_creator = $topic_post->creator;
                                $post_date = $topic_post->date;
                            endforeach;
                        endif;
                    $results .= '<a href="'.seo('topic.php?cid='.$category_id.'&amp;tid='.$topic->id.'&amp;page='.$postination[1]).'#'.$postination[0].'">'.sanitize($topic->title).'</a><br/>';
                    $results .= 'by '.username($post_creator).'<br/>';
                    $results .= '<span>'.convert_datetime($post_date).'</span>';
                endforeach;
            endif;
        return $results;
    }

    /** function to display a category title */
    public static function category_title($cid) {
        db::pdo()->query('SELECT * FROM `categories` WHERE `id` = :id LIMIT 1');
            db::pdo()->bind(array(':id' => $cid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $category):
                    return sanitize($category->title);
                endforeach;
            else:
                return 'Category Does Not Exist!';
           endif;
    }

    /** function to display a topic title */
    public static function topic_title($cid, $tid) {
        db::pdo()->query('SELECT * FROM `topics` WHERE `category_id` = :category_id AND `id` = :id LIMIT 1');
            db::pdo()->bind(array(':category_id' => $cid, ':id' => $tid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $topic):
                    return sanitize($topic->title);
                endforeach;
            else:
                return 'Topic Does Not Exist!';
            endif;
    }

    /** function to display a forum title */
    public static function forum_title($cid) {
        db::pdo()->query('SELECT `forum_id` FROM `categories` WHERE `id` = :id LIMIT 1');
            db::pdo()->bind(array(':id' => $cid));
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $found):
                    db::pdo()->query('SELECT * FROM `forums` WHERE `id` = :id LIMIT 1');
                        db::pdo()->bind(array(':id' => $found->forum_id));
                    db::pdo()->execute();
                        if(db::pdo()->count() > 0):
                            foreach(db::pdo()->result() as $forum):
                                return sanitize($forum->title);
                            endforeach;
                        endif;
                    //return $forum;
                endforeach;
            else:
                return 'Forum Does Not Exist!';
           endif;
    }

    /** function to display new topics */
    public static function new_topics() {
        $results = null;
        db::pdo()->query('SELECT * FROM `topics` ORDER BY `date` DESC LIMIT 8');
        db::pdo()->execute();
        $results .= '<div class="sidebar-wrapper2">';
        $results .= '<div class="new-topics-header">Recent Topics</div>';
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $new_topic):
                    $results .= '<div class="new-topics">';
                    $results .= '<b><a href="'.seo('topic.php?cid='.$new_topic->category_id.'&amp;tid='.$new_topic->id.'&amp;page=1').'">'.sanitize($new_topic->title).'</a></b><br/>';
                    $results .= 'by '.username($new_topic->creator).', ';
                    $results .= '<span>';
                    $results .= convert_datetime($new_topic->date).'<br/>';
                    $results .= 'in <a href="category.php?cid='.$new_topic->category_id.'">'.self::category_title($new_topic->category_id).'</a><br/>';
                    $results .= 'replies: '.self::count_topic_replies($new_topic->category_id, $new_topic->id).'<br/>';
                    $results .= '</span>';
                    $results .= '</div>';
                endforeach;
            endif;
        $results .= '</div>';
        return $results;
    }

    /** function todisplay new posts */
    public static function new_posts() {
        $results = null;
        db::pdo()->query('SELECT * FROM `posts` ORDER BY `date` DESC LIMIT 8');
        db::pdo()->execute();
        $results .= '<div class="sidebar-wrapper2">';
        $results .= '<div class="new-posts-header">Recent Posts</div>';
            if(db::pdo()->count() > 0):
                foreach(db::pdo()->result() as $topics):
                    db::pdo()->query('SELECT * FROM `topics` WHERE `id` = :id LIMIT 1');
                        db::pdo()->bind(array(':id' => $topics->topic_id));
                    db::pdo()->execute();
                        if(db::pdo()->count() > 0):
                            foreach(db::pdo()->result() as $topic_post):
                                $postination = postination::init()->latest_topic_posts($topic_post->category_id, $topics->topic_id, $topics->date, self::config()->posts_per_page);
                                $results .= '<div class="new-posts">';
                                $results .= '<b><a href="'.seo('topic.php?cid='.$topic_post->category_id.'&amp;tid='.$topics->topic_id.'&amp;page='.$postination[1]).'#'.$postination[0].'">'.sanitize($topic_post->title).'</a></b><br/>';
                                    if(strlen($topics->content) > 30):
                                        $results .= substr(str_replace(array("<br/>\r\n", "<br/>\r", "<br/>\n", '<br/>', "\r\n", "\r", "\n"), ' ', sanitize($topics->content)), 0, 30).'...<br/>';
                                    else:
                                        $results .= str_replace(array("<br/>\r\n", "<br/>\r", "<br/>\n", '<br/>', "\r\n", "\r", "\n"), ' ',  sanitize($topics->content)).'<br/>';
                                    endif;
                                $results .= 'by '.username($topics->creator).', ';
                                $results .= '<span>';
                                $results .= convert_datetime($topics->date).'<br/>';
                                $results .= 'in <a href="category.php?cid='.$topic_post->category_id.'">'.self::category_title($topic_post->category_id).'</a><br/>';
                                $results .= '</span>';
                                $results .= '</div>';
                            endforeach;
                        endif;
                endforeach;
            endif;
        $results .= '</div>';
        return $results;
    }

    /** function to display a selected category */
    public static function category() {
        $results = null;
        $cid = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
        $category_title = self::category_title($cid);
        db::pdo()->query('SELECT `id` FROM `categories` WHERE `id` = :id LIMIT 1');
            db::pdo()->bind(array(':id' => $cid));
        db::pdo()->execute();
        $results .= '<div class="category-header">'.sanitize($category_title);
            if(db::pdo()->count() == 1 and user::init()->is_authentic()):
                $results .= '<a class="category-create-topic-btn" href="create.php?cid='.$cid.'">Create Topic</a>';
            endif;
        $results .= '<div class="padder"></div>';
        $results .= '</div>';
        $results .= '<div class="category-content">';
            if(db::pdo()->count() == 1):
                db::pdo()->query('SELECT * FROM `topics` WHERE `category_id` = :cid ORDER BY `reply_date` DESC');
                    db::pdo()->bind(array(':cid' => $cid));
                db::pdo()->execute();
                    if(db::pdo()->count() > 0):
                        $results .= self::category_table($cid);
                    else:
                        $results .= 'There are no topics in this category yet.';
                    endif;
            else:
                $results .= '<a href="index.php">Return To Forum Index</a><br/>';
                $results .= 'You are trying to view a category that does not exist yet.';
            endif;
        $results .= '</div>';
        return $results;
    }

    /** function to build a table in a selected category (part of category function) */
    public static function category_table($cid) {
        $results = null;
        $results .= '<div class="category">';
        $results .= '<div class="category-row">';
        $results .= '<div class="category-cell-1">Topic Title</div>';
        $results .= '<div class="category-cell-2">Last Poster</div>';
        $results .= '<div class="category-cell-3">Replies</div>';
        $results .= '<div class="category-cell-4">Views</div>';
        $results .= '</div>';
        $results .= self::category_topics($cid);
        $results .= '</div>';
        return $results;
    }

    /** function to display results found in a selected category (part of category_table function) */
    public static function category_topics($cid) {
        $results = null;
        $query = 'SELECT * FROM `topics` WHERE `category_id` = :cid ORDER BY `reply_date` DESC';
        $bind = array(':cid' => $cid);
        pagination::init()->paginator($query, $bind, self::config()->topics_per_page, 5, 'cid='.$cid.'&amp;');
            if(pagination::init()->count() > 0):
                foreach(pagination::init()->result() as $topic):
                    $results .= '<div class="category-row">';
                    $results .= '<div class="category-cell-1">';
                    $results .= '<a href="'.seo('topic.php?cid='.$cid.'&amp;tid='.$topic->id.'&amp;page=1').'">'.sanitize($topic->title).'</a><br/>';
                    $results .= 'Started by: '.username($topic->creator).' ';
                    $results .= '<span>'.convert_datetime($topic->date).'</span>';
                    $results .= '</div>';
                    $results .= '<div class="category-cell-2">';
                    $postination = postination::init()->last_post($cid, $topic->id, self::config()->posts_per_page);
                    db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid AND `id` = :id LIMIT 1');
                        db::pdo()->bind(array(':cid' => $cid, ':tid' => $topic->id, ':id' => $postination[0]));
                    db::pdo()->execute();
                        if(db::pdo()->count() > 0):
                            foreach(db::pdo()->result() as $topic_post):
                                $results .= username($topic_post->creator);
                            endforeach;
                        endif;
                    $results .= '</div>';
                    $results .= '<div class="category-cell-3">'.self::count_topic_replies($cid, $topic->id).'</div>';
                    $results .= '<div class="category-cell-4">'.$topic->views.'</div>';
                    $results .= '</div>';
                endforeach;
            endif;
        $results .= '<div class="category-content-pagination">'.pagination::init()->links().'</div>';
        return $results;
    }

    /** function to display a selected topic */
    public static function topic() {
        $results = null;
        $cid = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
        $tid = isset($_GET['tid']) ? (int)$_GET['tid'] : 0;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        db::pdo()->query('SELECT * FROM `topics` WHERE `category_id` = :cid AND `id` = :id LIMIT 1');
            db::pdo()->bind(array(':cid' => $cid, ':id' => $tid));
        db::pdo()->execute();
            if(db::pdo()->count() == 1):
                foreach(db::pdo()->result() as $topic):
                    $results .= '<div class="topic-title"><b>'.sanitize($topic->title).'</b></div>';
                    $query = 'SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid ORDER BY `id`';
                    $bind = array(':cid' => $cid, ':tid' => $tid);
                    pagination::init()->paginator($query, $bind, self::config()->posts_per_page, 5, 'cid='.$cid.'&amp;tid='.$tid.'&amp;');
                        if(pagination::init()->count() > 0):
                            foreach(pagination::init()->result() as $post):
                                $results .= '<div class="topic-content-header">';
                                $results .= '<span>'.convert_datetime($post->date).'</span>';
                                $results .= '<a style="float:right; text-decoration:none;" href="'.seo('topic.php?cid='.$cid.'&amp;tid='.$tid.'&amp;page='.$page).'#'.$post->id.'">#'.$post->id.'</a><a id="'.$post->id.'"></a>';
                                $results .= '</div>';
                                $results .= '<div class="topic-content">';
                                $results .= '<div class="topic-content-left">';
                                $results .= self::topic_user($post->creator);
                                $results .= '</div>';
                                $results .= '<div class="topic-content-right">';
                                $results .= bbcodes(sanitize($post->content));
                                $results .= '</div>';
                                $results .= '</div>';
                                $results .= '<div class="topic-content-footer">';
                                    if(user::init()->is_authentic() and staff::init()->perm('topics')):
                                        $results .= staff::init()->del_topic_btn($cid, $tid, $post->date);
                                    endif;
                                    if(user::init()->is_authentic() and staff::init()->perm('posts')):
                                        $results .= staff::init()->del_post_btn($cid, $tid, $post->date, $post->id);
                                    endif;
                                $results .= '</div>';
                            endforeach;
                        endif;
                    $results .= '<div class="topic-footer">'.pagination::init()->links().'</div>';
                        if(user::init()->is_authentic()):
                            $results .= '<div class="topic-footer-reply">';
                            $results .= '<form action="'.seo('reply.php?cid='.$cid.'&amp;tid='.$tid).'" method="post">';
                            $results .= 'Reply To Topic<br/>';
                            $results .= '<textarea name="reply" rows="15" cols="75"></textarea><br/>';
                            $results .= '<input class="topic-footer-reply-btn" type="submit" name="submit" value="Reply" />';
                            $results .= '</form>';
                            $results .= '</div>';
                        endif;
                    $new_views = $topic->views + 1;
                    db::pdo()->query('UPDATE `topics` SET `views` = :views WHERE `category_id` = :cid AND `id` = :id LIMIT 1');
                        db::pdo()->bind(array(':views' => $new_views, ':cid' => $cid, ':id' => $tid));
                    db::pdo()->execute();
                endforeach;
            else:
                $results .= '<a href="'.seo('index.php').'">Return To Forum Index</a><br/>';
                $results .= 'The topic you are trying to view does not exist.';
            endif;
        return $results;
    }

    /** function to display the creator of topic/post (part of topic function) */
    public static function topic_user($id) {
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

    /** function to create a topic */
    public static function create_topic() {
        $cid = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
        $title = $_POST['title'];
        $content = $_POST['content'];
        $creator = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
            if(user::init()->is_authentic()):
                db::pdo()->query('INSERT INTO `topics` (`category_id`, `title`, `creator`, `date`, `reply_date`) VALUES (:cid, :title, :creator, now(), now())');
                    db::pdo()->bind(array(':cid' => $cid, ':title' => $title, ':creator' => $creator));
                $topics = db::pdo()->execute();
                $new_topic_id = db::pdo()->lastInsertId();
                db::pdo()->query('INSERT INTO `posts` (`category_id`, `topic_id`, `creator`, `content`, `date`) VALUES (:cid, :new_topic_id, :creator, :content, now())');
                    db::pdo()->bind(array(':cid' => $cid, ':new_topic_id' => $new_topic_id, ':creator' => $creator, ':content' => $content));
                $topic_posts = db::pdo()->execute();
                    if($topics and $topic_posts):
                        $postination = postination::init()->first_post($cid, $new_topic_id);
                        return header('Location:'.seo('topic.php?cid='.$cid.'&amp;tid='.$new_topic_id.'&amp;page='.$postination[1]).'#'.$postination[0]);
                    else:
                        return 'Sorry there was a problem creating your topic. Please go <a href="'.seo('create.php?cid='.$cid).'">back</a> and try again.';
                    endif;
            else:
                return header('Location:'.seo('logout.php'));
            endif;
    }

    /** function to reply to a topic */
    public static function topic_reply() {
        $cid = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
        $tid = isset($_GET['tid']) ? (int)$_GET['tid'] : 0;
        $reply = $_POST['reply'];
        $creator = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
            if(user::init()->is_authentic()):
                db::pdo()->query('INSERT INTO `posts` (`category_id`, `topic_id`, `creator`, `content`, `date`) VALUES (:cid, :tid, :creator, :reply, now())');
                    db::pdo()->bind(array(':cid' => $cid, ':tid' => $tid, ':creator' => $creator, ':reply' => $reply));
                $topic_posts = db::pdo()->execute();
                db::pdo()->query('UPDATE `topics` SET `reply_date` = now() WHERE id = :id LIMIT 1');
                    db::pdo()->bind(array(':id' => $tid));
                $topics = db::pdo()->execute();
                    if($topic_posts && $topics):
                        $postination = postination::init()->last_post($cid, $tid, self::config()->posts_per_page);
                        return header('Location:'.seo('topic.php?cid='.$cid.'&amp;tid='.$tid.'&amp;page='.$postination[1]).'#'.$postination[0]);
                    else:
                        return 'There was a problem posting your reply. Please go <a href="'.seo('topic.php?cid='.$cid.'&amp;tid='.$tid.'&amp;page=1').'">back</a> and try again in a few moments.';
                    endif;
            else:
                return header('Location:'.seo('logout.php'));
            endif;
    }

    /** function to build search form for use in header on pages */
    public static function search_form() {
        $results = null;
        $results .= '<form action="search.php" method="post">'."\r\n";
        $results .= '<input class="search-input" type="text" name="search_data" />'."\r\n";
        $results .= '<input class="search-btn" type="submit" name="search" value="Search"/>'."\r\n";
        $results .= '</form>'."\r\n";
        return $results;
    }

    /** function to search through created topics */
    public static function search(){
        $results = null;
        $data = isset($_SESSION['search']) ? $_SESSION['search'] : null;
        if($data == ''):
            $results .= 'No Search Was Initiated As The Search Term Was Found To Be Blank<br/>';
        else:
            $found_word = explode(' ', trim($data));
                foreach($found_word as $number => $search_word):
                    $search[$number] = '`title` LIKE ?';
                    $bind[$number+1] = '%'.$search_word.'%';
                endforeach;
            db::pdo()->query('SELECT * FROM `topics` WHERE '.implode(' OR ', $search));
                db::pdo()->bind($bind);
            db::pdo()->execute();
                if(db::pdo()->count() > 0):
                    $results .= '<div class="search">';
                    $results .= '<div class="search-row">';
                    $results .= '<div class="search-cell-1">Topic Title</div>';
                    $results .= '<div class="search-cell-2">Last Poster</div>';
                    $results .= '<div class="search-cell-3">Replies</div>';
                    $results .= '<div class="search-cell-4">Views</div>';
                    $results .= '</div>';
                    $query = 'SELECT * FROM `topics` WHERE '.implode(' OR ', $search).' ORDER BY `reply_date` DESC';
                    pagination::init()->paginator($query, $bind, self::config()->topics_per_page, 5, '');
                        if(pagination::init()->count() > 0):
                            foreach(pagination::init()->result() as $topic):
                                for($i=0; $i<count($found_word); $i++):
                                    $topic_title = preg_replace('/'.trim($found_word[$i]).'/i', '<b>$0</b>', sanitize($topic->title));
                                endfor;
                                $results .= '<div class="search-row">';
                                $results .= '<div class="search-cell-1">';
                                $results .= '<a href="'.seo('topic.php?cid='.$topic->category_id.'&amp;tid='.$topic->id.'&amp;page=1').'">'.$topic_title.'</a><br/>';
                                $results .= 'Started by: '.username($topic->creator).' ';
                                $results .= '<span>'.convert_datetime($topic->date).'</span>';
                                $results .= '</div>';
                                $results .= '<div class="search-cell-2">';
                                $postination = postination::init()->last_post($topic->category_id, $topic->id, self::config()->posts_per_page);
                                db::pdo()->query('SELECT * FROM `posts` WHERE `category_id` = :cid AND `topic_id` = :tid AND `id` = :id LIMIT 1');
                                    db::pdo()->bind(array(':cid' => $topic->category_id, ':tid' => $topic->id, ':id' => $postination[0]));
                                db::pdo()->execute();
                                    if(db::pdo()->count() > 0):
                                        foreach(db::pdo()->result() as $topic_post):
                                            $results .= username($topic_post->creator);
                                        endforeach;
                                    endif;
                                $results .= '</div>';
                                $results .= '<div class="search-cell-3">'.self::count_topic_replies($topic->category_id, $topic->id).'</div>';
                                $results .= '<div class="search-cell-4">'.$topic->views.'</div>';
                                $results .= '</div>';
                            endforeach;
                        endif;
        $results .= '<div class="search-content-pagination">'.pagination::init()->links().'</div>';
                    $results .= '</div>';
                else:
                    $results .= 'Your Search Returned No Results, Try To Use More Words To Widen The Search.<br/>';
                endif;
        endif;
        return $results;
    }
}
?>