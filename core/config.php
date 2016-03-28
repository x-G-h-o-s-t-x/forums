<?php
/** Prevent direct access from url to this config file */
if (stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'config.php')):
    header("Location:../index.php");
    die();
endif;

db::pdo()->query('SELECT * FROM `config`');
db::pdo()->execute();
$config = db::pdo()->result()[0];

?>