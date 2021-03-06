<?php session_start(); ?>
<?php foreach(glob('core/classes/*.php') as $class_file): require_once($class_file); endforeach; ?>
<?php foreach(glob('core/functions/*.php') as $function_file): require_once($function_file); endforeach; ?>
<?php if(file_exists('core/config.php')): require_once('core/config.php'); else: die('Cannot Find Configuration File'); endif; ?>
<?php if(user::init()->is_authentic()): ?>
<?php online::init()->log_offline(); ?>
<?php user::init()->logout(); ?>
<?php endif; ?>
<?php db::pdo()->close(); ?>
