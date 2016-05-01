<?php
/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/
/** if(Guest AND Has Permission OR Is Member) = Show Shoutbox */
if(!user::init()->is_authentic() and preg_match('/true/i', $config->show_shouts_to_guests) or user::init()->is_authentic()):
    if(user::init()->is_authentic() and isset($_POST['shout'])):
        shoutbox::init()->shout($_SESSION['user'], $_POST['post']);
        $_SESSION['shout'] = $_POST['post'];
    endif;
?>
<script type="text/javascript" src="core/js/shout_idle.js"></script>
<div class="wrapper">
    <script type="text/javascript" src="core/js/shout_hide.js"></script>
    <div class="shoutbox-header">Shoutbox <a id="shoutbox-refresh" href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">[refresh]</a></div>
    <div class="shoutbox-announcement" id="shoutbox-announcement"></div>
        <div class="shoutbox-content" id="shoutbox-content">
        <?php echo shoutbox::init()->display(); ?>
        </div>
        <div class="shoutbox-form" id="shoutbox-form">
<?php if(user::init()->is_authentic()): ?>
            <script type="text/javascript" src="core/js/shout_hijack.js"></script>
                <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" id="shout_form">
                    <input class="shoutbox-input" type="text" name="post" id="post" value="" maxlength="300"/>
                    <input class="shoutbox-submit-btn" name="shout" id="shout" value="Shout" type="submit"/>
                </form>
            <script type="text/javascript" src="core/js/shout.js"></script>
<?php else: ?>
            You must be logged in to use the shoutbox
<?php endif; ?>
        </div>
</div>
<?php endif; ?>