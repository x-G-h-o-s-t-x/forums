/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/
$(document).ready(function () {
    var idleTime = 0, refresh = 10000, timeout = 300000; // refresh 10 secs (10000 ms), timeout 5 mins (300000 ms)

    document.getElementById('shoutbox-announcement').style.display = 'none'; //do not show alert
    
    //increment the idleTime counter/refresh shoutbox every 10 seconds.
    var idleInterval = setInterval(idleRefresh, refresh);

    //reset timer, remove alert, refresh shoutbox when announcement button is clicked
    $(this).on('click', '#shoutbox-announcement-btn', function() {
        idleTime = 0; //reset timer
        document.getElementById('shoutbox-announcement').style.display = 'none'; //remove alert
        $(".shoutbox-content").load("shout_view.php"); //refresh shoutbox
    });

    //reset timer, remove alert, refresh shoutbox when enter key is used within shoutbox input field & field is not blank
    $(this).on('keypress', '.shoutbox-input', function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
            if(keycode == '13' && $(".shoutbox-input").val() != '') {
                idleTime = 0; //reset timer
                document.getElementById('shoutbox-announcement').style.display = 'none'; //remove alert
                $(".shoutbox-content").load("shout_view.php"); //refresh shoutbox
            }
    });

    //refresh shoutbox, alert user if inactive/timeout.
    function idleRefresh() {
        idleTime = idleTime + refresh;
            if(idleTime < timeout) {
                $(".shoutbox-content").load("shout_view.php"); //refresh shoutbox
            } else {
                document.getElementById('shoutbox-announcement').style.display = 'block'; //alert user
                var element = ".shoutbox-content", background = $(element).css("background-color"), color = $(element).css("color"), border = $(element).css("border-left");
                $("#shoutbox-announcement").css({"background": background, "color": color, "font-size": "0.75rem", "text-align": "center", "padding": "2px", "border": border, "border-top": "none"});
                $("#shoutbox-announcement").html('You are currently flagged as idle. Click <button id="shoutbox-announcement-btn">here</button> to un-flag.');
                $("#shoutbox-announcement-btn").css({"background": "transparent", "color": "#0044FF", "cursor": "pointer", "border": "none"});
            }
    }
});