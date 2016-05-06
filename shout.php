<?php
session_start();
/** ****************************************************
  *                @author: Ghost                      *
  *                @copyright: 2016                    *
  *                @description: this file is used by  *
  *                the jquery part of the shoutbox     *
  **************************************************** **/
foreach(glob('core/classes/*.php') as $class_file): require_once($class_file); endforeach;
foreach(glob('core/functions/*.php') as $function_file): require_once($function_file); endforeach;
if(file_exists('core/config.php')): require_once('core/config.php'); else: die('Cannot Find Configuration File'); endif;
    if(!user::init()->is_authentic() and preg_match('/true/i', $config->show_shouts_to_guests) or user::init()->is_authentic()):
        if(user::init()->is_authentic() and isset($_POST['shout'])):
            shoutbox::init()->shout($_SESSION['user'], $_POST['post']);
            $_SESSION['shout'] = $_POST['post'];
        endif;
    endif;
db::pdo()->close();
?>