<?php
session_start();
/** ****************************************************
  *                @author: Ghost                      *
  *                @copyright: 2016                    *
  *                @description: this file is used by  *
  *                the jquery part of the shoutbox     *
  **************************************************** **/
require_once('core/classes/db.class.php');
require_once('core/functions/site.funcs.php');
require_once('core/classes/shoutbox.class.php');
echo shoutbox::init()->shoutouts();
db::pdo()->close();
?>