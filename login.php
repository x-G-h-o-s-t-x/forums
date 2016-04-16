<?php session_start(); ?>
<?php header('Content-type: text/html; charset=UTF-8'); ?>
<?php foreach(glob('core/classes/*.php') as $class_file): include($class_file); endforeach; ?>
<?php foreach(glob('core/functions/*.php') as $function_file): include($function_file); endforeach; ?>
<?php if(file_exists('core/config.php')): require('core/config.php'); else: die('Cannot Find Configuration File'); endif; ?>
<?php debug($config->debug); ?>
<?php user::init()->is_remembered(); ?>
<?php if(isset($_POST['login'])): ?>
    <?php $username = $_POST['username']; ?>
    <?php $password = $_POST['password']; ?>
    <?php $login = user::init()->login($username, $password); ?>
<?php endif; ?>
<!doctype html>
<html lang="en">

<head>
    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <?php if(isset($login) and $login == true): ?>
        <meta http-equiv="Refresh" content="5; url=<?php echo seo('index.php'); ?>" />
    <?php endif; ?>
    <title><?php echo $config->site_name; ?></title>
    <link rel="stylesheet" type="text/css" href="core/css/<?php echo $theme; ?>.css" media="screen"/>
    <link rel="shortcut icon" type="image/ico" href="core/images/celtic_cross.ico"/>
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
            <li><a href="<?php echo seo('logout.php'); ?>">Logout</a></li>
        <?php else: ?>
            <li><a class="active" href="<?php echo seo('login.php'); ?>">Login</a></li>
            <li><a href="<?php echo seo('register.php'); ?>">Register</a></li>
        <?php endif; ?>
        </ul>
    </nav>

    <div class="secondary-nav">
        <div>
            <a href="<?php echo seo('index.php'); ?>">Home</a> &gt; 
            <a href="<?php echo seo('login.php'); ?>">Login</a>
        </div>
    </div>

    <div class="wrapper">
        <div class="login-header">Login:</div>
        <div class="login-content">
        <?php if(isset($login) and $login == true): ?>
            Welcome <?php echo sanitize($_SESSION['user']); ?><br/>
            You have logged in successfully. you will now be redirected back to the home page.<br/>in 5 seconds...
        <?php elseif(isset($login) and $login == false): ?>
            Username And/Or Password Do Not Match.<br/><br/>
            Click <a href="<?php echo seo('login.php'); ?>">Here</a> To Try Again<br/><br/>
        <?php else: ?>
            <div class="login">
                <div class="form-wrapper">
                    <form action="<?php echo seo('login.php'); ?>" method="post">
                        <div class="form-item">
                            <input type="text" name="username" required="required" placeholder="Username">
                        </div>
                        <div class="form-item">
                            <input type="password" name="password" required="required" placeholder="Password">
                        </div>
                    <input type="checkbox" name="remember" value="1"> Remember Me
                        <div class="button-panel">
                            <input type="submit" class="button" title="Login" name="login" value="Login">
                        </div>
                    </form>
                    <div class="form-footer">
                        <p><a href="<?php  echo seo('forgot.php'); ?>">Forgot password?</a></p>
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

    </body>
</html>