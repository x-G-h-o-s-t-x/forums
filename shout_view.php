<?php
session_start();
/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/
require_once('core/classes/db.class.php');
require_once('core/functions/site.funcs.php');
require_once('core/classes/shoutbox.class.php');
echo shoutbox::init()->display();
db::pdo()->close();
?>