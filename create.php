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
<?php $category = forums::init()->category_title($cid); ?>
<?php $title = isset($_POST['title']) ? $_POST['title'] : null; ?>
<?php $content = isset($_POST['content']) ? $_POST['content'] : null; ?>
<?php $form = <<<form
<form action="create.php?cid=$cid" method="post">
Topic Title<br/><input class="create-topic-input" type="text" name="title" value="$title" maxlength="150" /><br/>
Topic Content<br/><textarea class="create-topic-textarea" name="content" value="$content" rows="10" cols="75"></textarea><br/>
<input class="create-topic-btn" type="submit" name="submit" value="Create Topic" />
</form>
form;
?>
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
            <a href="<?php echo seo('create.php?cid='.$cid); ?>">Create Topic</a>
        </div>
    </div>

    <div class="wrapper">
        <div class="create-topic-header">Create Topic</div>
        <div class="create-topic-content">
        <?php if(isset($_POST['submit'])): ?>
            <?php if($_POST['title'] == '' or $_POST['content'] == ''): ?>
                You did not fill in all fields.
                <?php echo $form, "\r\n"; ?>
            <?php else: ?>
                <?php echo forums::init()->create_topic(); ?>
            <?php endif; ?>
        <?php else: ?>
            <?php echo $form, "\r\n"; ?>
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