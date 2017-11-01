$(document).ready(function() {

    $('.show-hide-comments').click(function(event) {
        if ( $(event.target).css('background-image').indexOf('downarrow') >= 0) {
            uparrow_value = $(event.target).css('background-image').replace('downarrow', 'uparrow');
            $(event.target).css('background-image', uparrow_value);
            $(event.target).parent(".comment-counter").siblings('.comment-wrapper').children('.comments').children(".comment-content").css({ display: 'none' });
        } else {
            downarrow_value = $(event.target).css('background-image').replace('uparrow', 'downarrow');
            $(event.target).css('background-image', downarrow_value);
            $(event.target).parent(".comment-counter").siblings('.comment-wrapper').children('.comments').children(".comment-content").css({ display: 'block' });
        }
    });

    $('.create-comment-link').click(function(event) {
        $(event.target).css({ display: 'none' });
        $(event.target).closest(".comment-wrapper").children(".comment-content").css({ display: 'block' });
        console.log($(event.target));
        $(event.target).parent(".create-comment-link").siblings(".comment-create-form").css({ display: 'block' });
        $(event.target).parent(".create-comment-link").siblings(".comment-links").css({ display: 'block' });
    });

    function get_comment_as_json_string(textarea_value, answer_id) {
        var comment = {
            answer_id: answer_id,
            comment_data: textarea_value
        };
        return JSON.stringify(comment);
    }

    function getNextAvailableCommentId() {
        target_url = window.location.href.replace("cmd=edit", "cmd=getNextAvailableCommentId");
        return $.ajax({
            url: target_url
        })
    }

    function getNumberOfComments(event_target) {
        debugger;
        return event_target.parent('.comment-links').siblings('.comments').children('.comment-content').length;
    }

    $('.save-link').click(function(event) {
        textarea_value = $(event.target).parent('.comment-links').siblings('.comment-create-form').children('.form-group').children('.comment-textarea').val();
        if(!$.trim(textarea_value)) {
            $(event.target).parent('.comment-links').siblings('.comment-create-form').children(".comment-form-error-message").css({ display: 'block' });
        } else {
            comment_as_json_string = get_comment_as_json_string(textarea_value, $(event.target).closest('.answer_form').attr('id'));
            $(".new-comment-data").attr('data-new-comments', comment_as_json_string);

            new_comment = $(event.target).parent('.comment-links').siblings('.comments').children('.comment-content:last-child').clone(true);
            var promise = getNextAvailableCommentId();
            promise.success(function (data) {
                new_comment.attr('id', data);
            });

            $(new_comment).children('span').text(textarea_value);

            new_comment.appendTo('.comments');

            $(event.target).parent('.comment-links').siblings('.comment-create-form').css({ display: 'none' });
            $(event.target).parent('.comment-links').siblings(".create-comment-link").css({ display: 'block' });
            $(event.target).parent('.comment-links').siblings(".create-comment-link").children('a').css({ display: 'block' });
            $(event.target).parent(".comment-links").css({ display: 'none' });
            $(event.target).parent('.comment-links').siblings('.comment-create-form').children(".comment-form-error-message").css({ display: 'none' });

            old_counter_text = $(event.target).closest('.comment-wrapper').siblings('.comment-counter').children('.comment-counter-label').text();
            number_of_comments = getNumberOfComments($(event.target));
            new_counter_text = old_counter_text.replace(/\d+/ ,number_of_comments);

            if(number_of_comments = 2) {
                var language = $('html').attr('lang');
                if(language = 'en') {
                    new_counter_text = new_counter_text.replace('Comment', 'Comments');
                } else if(language = 'de') {
                    new_counter_text = new_counter_text.replace('Kommentar', 'Kommentare');
                }
            }
            $(event.target).closest('.comment-wrapper').siblings('.comment-counter').children('.comment-counter-label').text(new_counter_text);
        }
    });

    $('.discard-link').click(function(event) {
        $(event.target).parent('.comment-links').siblings(".comment-create-form").css({ display: 'none' });
        $(event.target).parent(".comment-links").css({ display: 'none' });
        $(event.target).parent('.comment-links').siblings(".create-comment-link").css({ display: 'block' });
        $(event.target).parent('.comment-links').siblings(".create-comment-link").children('a').css({ display: 'block' });
    });

    /* Execute function on click on the up arrow (on load is handled in the corresponding input gui)
    1) loop through each answer form
    2) check if the value of the hidden input with name answer[][answer_id] is 1
    3) if the value is one show the down arrow
    4) if the user has already votet for one of the answers
    5) inform the user that he first has to downvote the other answer
        a) alternatives: - info text
                         - disable up voting links


     */

    function hasUserAlreadyVoted() {
        alreadyVoted = false;
        $('.answer_form').each(function (i, el) {
            debugger;
            is_voted_by_current_user = $(el).children('input[name*="[is_voted_by_current_user]"]').val();
            if(is_voted_by_current_user == 1) {
                $(el).find('.vote-down-off').css('display', '');
                alreadyVoted = true;
                return true;
            }
        });
        if(!alreadyVoted) {
            return false;
        } else {
            return true;
        }
    }

    function resetPreviousUpvotings() {
        $('.answer_form').each(function (i, el) {
            if($(el).children('input[name*="[is_voted_by_current_user]"]').val()) {
                counter_value = $(el).children('.vote').children('.vote-count-post').text();
                new_counter_value = counter_value--;
                $(el).children('.vote').children('.vote-count-post').text(new_counter_value);
            }
            $(el).children('input[name*="[is_voted_by_current_user]"]').val(0);
            $(el).find('.vote-down-off').css('display', 'none');
        })
    }

    /*
    1) save in a hidden input for which answer the current user voted
    1) check if user has already votet
    2) yes
        a) display a window which informs the user that he has already votet for the answer xy and that he has to downvote the answer
        b) show at the answer which the user upvotet the downvoting arrow
     */

    $('.vote-up-off').click(function(event) {
        debugger;
        if(hasUserAlreadyVoted()) {
            $(event.target).siblings('.voting_error').css('display', '');

        } else {
            resetPreviousUpvotings();
            $(event.target).closest('.answer_form').children('input[name*="[is_voted_by_current_user]"]').val(1);
            $(event.target).siblings('.vote-down-off').css('display', '');
            counter_value = $(event.target).siblings('.vote-count-post').text();
            new_counter_value = ++counter_value;
            $(event.target).siblings('.vote-count-post').text(new_counter_value);
        }
    });

    $('.vote-down-off').click(function(event) {
        resetPreviousUpvotings();
        $(event.target).siblings('.vote-down-off').css('display', 'none');
        counter_value = $(event.target).siblings('.vote-count-post').text();
        new_counter_value = --counter_value;
        $(event.target).siblings('.vote-count-post').text(new_counter_value);
    })
});