<?php session_start(); ?>
<?php header('Content-type: text/html; charset=UTF-8'); ?>
<?php foreach(glob('core/classes/*.php') as $class_file): require_once($class_file); endforeach; ?>
<?php foreach(glob('core/functions/*.php') as $function_file): require_once($function_file); endforeach; ?>
<?php if(file_exists('core/config.php')): require_once('core/config.php'); else: die('Cannot Find Configuration File'); endif; ?>
<?php debug($config->debug); ?>
<?php user::init()->is_remembered(); ?>
<?php online::init()->check_online(); ?>
<?php online::init()->check_offline(); ?>
<?php $cid = (isset($_POST['cid']) ? (int)$_POST['cid'] : (isset($_GET['cid']) ? (int)$_GET['cid'] : 0)); ?>
<?php $tid = (isset($_POST['tid']) ? (int)$_POST['tid'] : (isset($_GET['tid']) ? (int)$_GET['tid'] : 0)); ?>
<?php $category = forums::init()->category_title($cid); ?>
<?php $topic = forums::init()->topic_title($cid, $tid); ?>
<?php $forum = forums::init()->forum_title($cid); ?>
<?php if(isset($_POST['delete_topic']) and user::init()->is_authentic() and staff::init()->perm('topics')): ?>
<?php $delete_topic = staff::init()->delete_topic(); ?>
<?php elseif(isset($_POST['delete_post']) and user::init()->is_authentic() and staff::init()->perm('posts')): ?>
<?php $delete_post = staff::init()->delete_post(); ?>
<?php endif; ?>
<!doctype html>
<html lang="en">

<head>
<?php $slash = '/'; ?>
<?php if(dirname($_SERVER['PHP_SELF']) == '/' OR dirname($_SERVER['PHP_SELF']) == '\\'): $slash = str_replace('/', '', $slash); endif; ?>
    <base href="http://<?php echo $_SERVER['SERVER_NAME'].str_replace('\\', '', dirname($_SERVER['PHP_SELF'])).$slash; ?>" />
    <!--[if IE]></base><![endif]-->
    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<?php if(isset($delete_topic) and $delete_topic == true): ?>
    <meta http-equiv="Refresh" content="3; url=<?php echo seo('category.php?cid='.$cid); ?>" />
<?php elseif(isset($delete_topic) and $delete_topic == false or isset($delete_post)): ?>
    <meta http-equiv="Refresh" content="3; url=<?php echo seo('topic.php?cid='.$cid.'&amp;tid='.$tid.'&amp;page=1'); ?>" />
<?php endif; ?>
    <title><?php echo $topic; ?></title>
    <link rel="stylesheet" type="text/css" href="core/css/<?php echo $theme; ?>.css" media="screen" />
    <link rel="shortcut icon" type="image/ico" href="core/images/celtic_cross.ico" />
</head>
    <body>

    <header>
        <div class="logo">
            <img src="<?php echo $config->site_logo; ?>" alt=""/><br/>
        </div>
        <div class="padder"></div>
    </header>

    <nav>
        <ul>
            <li><a class="active" href="<?php echo seo('index.php'); ?>">Home</a></li>
        <?php if(user::init()->is_authentic()): ?>
            <li><a href="<?php echo seo('cpanel.php'); ?>">Cpanel</a></li>
            <li><a href="<?php echo seo('logout.php'); ?>">Logout</a></li>
        <?php else: ?>
            <li><a href="<?php echo seo('login.php'); ?>">Login</a></li>
            <li><a href="<?php echo seo('register.php'); ?>">Register</a></li>
        <?php endif; ?>
        </ul>
    </nav>

    <?php echo pm::init()->announcement(); ?>

    <div class="secondary-nav">
        <div>
            <a href="<?php echo seo('index.php'); ?>">Home</a> &gt; 
            <a href="<?php echo seo('index.php'); ?>"><?php echo $forum; ?></a> &gt; 
            <a href="<?php echo seo('category.php?cid='.$cid); ?>"><?php echo $category; ?></a> &gt; 
            <a href="<?php echo seo('topic.php?cid='.$cid.'&amp;tid='.$tid.'&amp;page=1'); ?>"><?php echo $topic; ?></a>
        </div>
    </div>

    <div class="wrapper">
        <div class="search-form">
            <?php echo forums::init()->search_form(); ?>
        </div>
    </div>

    <div class="wrapper">
    <?php if(isset($delete_topic) and $delete_topic == true): ?>
        <div class="topic-header">Delete Topic</div>
        <div class="topic">
        The Topic And All Its Posts Was Deleted Successfully.<br/>Redirecting Please Wait...
        </div>
    <?php elseif(isset($delete_topic) and $delete_topic == false): ?>
        <div class="topic-header">Delete Topic</div>
        <div class="topic">
        There Was A Problem With The Deletion Of The Topic And Its Posts.<br/>Redirecting Please Wait...
        </div>
    <?php elseif(isset($delete_post) and $delete_post == true): ?>
        <div class="topic-header">Delete Post</div>
        <div class="topic">
        The Post Was Deleted Successfully.<br/>Redirecting Please Wait...
        </div>
    <?php elseif(isset($delete_post) and $delete_post == false): ?>
        <div class="topic-header">Delete Post</div>
        <div class="topic">
        There Was A Problem With The Deletion Of The Post.<br/>Redirecting Please Wait...
        </div>
    <?php else: ?>
        <?php echo forums::init()->topic(); ?>
    <?php endif; ?>
    </div>

    <div class="wrapper">
        <?php echo online::init()->display_page(); ?>
    </div>

    <footer>
        <div class="footer-left"><?php echo theme_form(); ?></div>
        <div class="footer-right"><?php echo $copyright; ?></div>
        <div class="padder"></div>
    </footer>

    <?php db::pdo()->close(); ?>
    </body>
</html>