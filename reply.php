<?php session_start(); ?>
<?php header('Content-type: text/html; charset=UTF-8'); ?>
<?php foreach(glob('core/classes/*.php') as $class_file): require_once($class_file); endforeach; ?>
<?php foreach(glob('core/functions/*.php') as $function_file): require_once($function_file); endforeach; ?>
<?php if(file_exists('core/config.php')): require_once('core/config.php'); else: die('Cannot Find Configuration File'); endif; ?>
<?php debug($config->debug); ?>
<?php user::init()->is_remembered(); ?>
<?php online::init()->check_online(); ?>
<?php online::init()->check_offline(); ?>
<?php $cid = isset($_GET['cid']) ? (int)$_GET['cid'] : 0; ?>
<?php $tid = isset($_GET['tid']) ? (int)$_GET['tid'] : 0; ?>
<?php $category = forums::init()->category_title($cid); ?>
<?php $topic = forums::init()->topic_title($cid, $tid); ?>
<!doctype html>
<html lang="en">

<head>
    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<?php if(!user::init()->is_authentic()): ?>
    <meta http-equiv="Refresh" content="0; url=<?php echo seo('index.php'); ?>" />
    <?php die(); ?>
<?php endif; ?>
    <title><?php echo $category; ?></title>
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
            <a href="<?php echo seo('category.php?cid='.$cid); ?>"><?php echo $category; ?></a> &gt; 
            <a href="<?php echo seo('topic.php?cid='.$cid.'&amp;tid='.$tid.'&amp;page=1'); ?>"><?php echo $topic; ?></a> &gt; 
            <a href="<?php echo seo('reply.php?cid='.$cid.'&amp;tid='.$tid); ?>">Topic Reply</a>
        </div>
    </div>

    <div class="wrapper">
        <div class="reply-topic-header">Topic Reply</div>
        <div class="reply-topic-content">
        <?php if(isset($_POST['submit'])): ?>
            <?php if($_POST['reply'] == ''): ?>
                You did not fill anything in.<br/>
                Please <a href="<?php echo seo('topic.php?cid='.$cid.'&amp;tid='.$tid.'&amp;page=1'); ?>">return</a>
                to the previous page and try again.
            <?php else: ?>
                <?php echo forums::init()->topic_reply(); ?>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-left"><?php echo theme_form(); ?></div>
        <div class="footer-right"><?php echo $copyright; ?></div>
        <div class="padder"></div>
    </footer>

    <?php db::pdo()->close(); ?>
    </body>
</html>