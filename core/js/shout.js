/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/
    /* attach a submit handler to the form */
    $("#shout_form").submit(function(event) {

        /* stop form from submitting normally */
        event.preventDefault();

        /* get some values from elements on the page: */
        var $form = $(this), url = $form.attr('action');

        /* send the data using post */
        var posting = $.post(url, { shout: true, post: $('#post').val() });

        /* clear the post input field and refresh the shoutbox */
        posting.done(function(data) {
        document.getElementById("post").value = null;
        $(".shoutbox-content").load("shout_view.php");
        });
    });