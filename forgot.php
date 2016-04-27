<?php session_start(); ?>
<?php header('Content-type: text/html; charset=UTF-8'); ?>
<?php foreach(glob('core/classes/*.php') as $class_file): require_once($class_file); endforeach; ?>
<?php foreach(glob('core/functions/*.php') as $function_file): require_once($function_file); endforeach; ?>
<?php if(file_exists('core/config.php')): require_once('core/config.php'); else: die('Cannot Find Configuration File'); endif; ?>
<?php debug($config->debug); ?>
<?php user::init()->is_remembered(); ?>
<?php online::init()->check_online(); ?>
<?php online::init()->check_offline(); ?>
<?php if(isset($_POST['forgot'])): ?>
    <?php $username = $_POST['username']; ?>
    <?php $authentication = $_POST['authentication']; ?>
    <?php $password = $_POST['password']; ?>
    <?php $forgotpassword = user::init()->forgotpassword($username, $authentication, $password); ?>
<?php endif; ?>
<!doctype html>
<html lang="en">

<head>
    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <?php if(isset($forgotpassword) and $forgotpassword == true): ?>
        <meta http-equiv="Refresh" content="5; url=<?php echo seo('index.php'); ?>" />
    <?php endif; ?>
    <title><?php echo $config->site_name; ?></title>
    <link rel="stylesheet" type="text/css" href="core/css/<?php echo $theme; ?>.css" media="screen"/>
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
            <li><a href="<?php echo seo('index.php'); ?>">Home</a></li>
        <?php if(user::init()->is_authentic()): ?>
            <li><a href="<?php echo seo('cpanel.php'); ?>">Cpanel</a></li>
            <li><a href="<?php echo seo('logout.php'); ?>">Logout</a></li>
        <?php else: ?>
            <li><a class="active" href="<?php echo seo('login.php'); ?>">Login</a></li>
            <li><a href="<?php echo seo('register.php'); ?>">Register</a></li>
        <?php endif; ?>
        </ul>
    </nav>

    <?php echo pm::init()->announcement(); ?>

    <div class="secondary-nav">
        <div>
            <a href="<?php echo seo('index.php'); ?>">Home</a> &gt; 
            <a href="<?php echo seo('login.php'); ?>">Login</a> &gt; 
            <a href="<?php echo seo('forgot.php'); ?>">Forgot Password</a>
        </div>
    </div>

    <div class="wrapper">
        <div class="search-form">
            <?php echo forums::init()->search_form(); ?>
        </div>
    </div>

    <div class="wrapper">
        <div class="forgot-header">Forgot Password:</div>
        <div class="forgot-content">
        <?php if(isset($forgotpassword) and $forgotpassword == true): ?>
            Thank You <?php echo $username; ?>. you can now login with your new password. " <b><?php echo $password; ?></b> "<br/>
            you will now be redirected back to the home page. in 5 seconds...
        <?php elseif(isset($forgotpassword) and $forgotpassword == false): ?>
            Authentication Code Do Not Match Account.<br/><br/>
            Click <a href="<?php echo seo('forgot.php'); ?>">here</a> to try again.<br/><br/>
        <?php else: ?>
            <div class="forgot">
                <div class="form-wrapper">
                    <form action="<?php echo seo('forgot.php'); ?>" method="post">
                        <div class="form-item">
                            <input type="text" name="username" required="required" placeholder="Username">
                        </div>
                        <div class="form-item">
                            * required: your own secret code or phrase.<br/>
                            which you made on registration.<br/>
                            <input type="text" name="authentication" required="required" placeholder="Authentication">
                        </div>
                        <div class="form-item">
                            <input type="password" name="password" required="required" placeholder="New Password">
                        </div>
                        <div class="button-panel">
                            <input type="submit" class="button" title="Reset Password" name="forgot" value="Reset Password">
                        </div>
                    </form>
                    <div class="form-footer">
                        <p>already a member? &nbsp;<a href="<?php echo seo('login.php'); ?>">Sign In</a></p>
                        <p><a href="<?php echo seo('register.php'); ?>">Create an account</a></p>
                    </div>
                </div>
            </div>
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