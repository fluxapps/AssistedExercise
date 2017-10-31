$(document).ready(function() {

    $('.show-hide-comments').click(function(event) {
        if ( $(event.target).attr('background-image') == 'url(../../images/downarrow.png)' ) {
            $(event.target).attr('background-image', 'url(../../images/uparrow.png');
            $(event.target).closest( ".comment-content" ).css({ display: block });
        } else {
            $(event.target).attr('background-image', 'url(../../images/downarrow.png');
            $(event.target).closest( ".comment-content" ).css({ display: none });
        }
    });

    $('.create-comment-link').click(function(event) {
        $(event.target).css({ display: none });
        $(event.target).closest(".comment-wrapper").children(".comment-content").css({ display: block });
        $(event.target).closest(".comment-create-form").css({ display: block });
        $(event.target).closest(".comment-links").css({ display: block });
    });

    function get_comment_as_json_string(textarea_value, answer_id) {
        var comment = {
            answer_id: answer_id,
            comment_data: textarea_value
        };
        return JSON.stringify(comment);
    }

    function getNextAvailableCommentId() {
        target_url = window.location.href.replace("cmd=edit", "cmd=getNexAvailableCommentId");
        $.ajax({
            url: target_url,
            success: function(result) {
                return result;
            }
        })
    }

//save the next available id in a hidden span. Every time a comment is added increment the number by one. This is only needed to make sure that the comment id is unique. When the new comment data will be persisted the ids will be set automatically.

    $('.save-link').click(function(event) {
        /*
        1) copy existing comment / or hidden comment
        2) save the comment data in a hidden input as json string
        3) paste the text from the input in the new comment
        4) hide the comment-links
        5) show the create-comment-link
         */
        if(!$.trim($(event.target).closest('.comment-textarea').val())) {
            $(".comment-form-error-message").css({ display: block });
            console.log("The input is not allowed to be empty");
        } else {
            textarea_value = $(event.target).closest('.comment-textarea').val();
            comment_as_json_string = get_comment_as_json_string(textarea_value, $(event.target).closest('.answer_form').attr('id'));
            $(".new-comment-data").attr('data-new-comments', comment_as_json_string);

            new_comment = $(event.target).closest('.comment-wrapper').children('.comment-content:last-child').clone(true);
            new_comment.attr('id', getNextAvailableCommentId());
            $(new_comment>span).text(textarea_value);

            $(event.target).closest(".comment-create-form").css({ display: none });
            $(event.target).closest(".comment-links").css({ display: none });
            $(event.target).closest(".create-comment-link").css({ display: none });
        }
    });

    $('.di  scard-link').click(function(event) {

    })
});