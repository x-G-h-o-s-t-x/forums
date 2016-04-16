<?php
// Prevent direct access from url to this config file
if (stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'config.php')):
    header('Location:../index.php');
    die();
endif;

db::pdo()->query('SELECT * FROM `config`');
db::pdo()->execute();
$config = db::pdo()->result()[0];

$theme = theme();

/** Copyright, (NO NEED TO TOUCH) */
$validate = '<a href="http://validator.w3.org/check/referer" target="_blank">html5</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer" target="_blank">css3</a>';
$copyright = $validate.' &#169; '.$config->site_name.' - '.gmdate('Y').'<br/>Created By Ghost<br/>';
?>