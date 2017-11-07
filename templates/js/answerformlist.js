$(document).ready(function() {

    $('.show-hide-comments').click(function(event) {
        if ( $(event.target).css('background-image').indexOf('downarrow') >= 0) {
            uparrow_value = $(event.target).css('background-image').replace('downarrow', 'uparrow');
            $(event.target).css('background-image', uparrow_value);
            $(event.target).parent(".comment-counter").siblings('.comment-wrapper').children('.comments').children(".comment-content").css({ display: 'none' });
            $(event.target).parent(".comment-counter").siblings('.comment-wrapper').children('.create-comment-link').removeClass('col-sm-offset-3');
            $(event.target).parent(".comment-counter").siblings('.comment-wrapper').children('.comment-create-form').removeClass('col-sm-offset-3');
        } else {
            downarrow_value = $(event.target).css('background-image').replace('uparrow', 'downarrow');
            $(event.target).css('background-image', downarrow_value);
            $(event.target).parent(".comment-counter").siblings('.comment-wrapper').children('.comments').children(".comment-content").css({ display: 'block' });
            $(event.target).parent(".comment-counter").siblings('.comment-wrapper').children('.create-comment-link').addClass('col-sm-offset-3');
            $(event.target).parent(".comment-counter").siblings('.comment-wrapper').children('.comment-create-form').addClass('col-sm-offset-3');
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

            if(number_of_comments == 2) {
                var language = $('html').attr('lang');
                if(language = 'en') {
                    new_counter_text = new_counter_text.replace('Comment', 'Comments');
                } else if(language = 'de') {
                    new_counter_text = new_counter_text.replace('Kommentar', 'Kommentare');
                }
            }
            $(event.target).closest('.comment-wrapper').siblings('.comment-counter').children('.comment-counter-label').text(new_counter_text);

            //after the comment was safed empty the textarea
            $(event.target).parent('.comment-links').siblings('.comment-create-form').children('.form-group').children('.comment-textarea').val('');

        }
    });

    $('.discard-link').click(function(event) {
        $(event.target).parent('.comment-links').siblings(".comment-create-form").css({ display: 'none' });
        $(event.target).parent(".comment-links").css({ display: 'none' });
        $(event.target).parent('.comment-links').siblings(".create-comment-link").css({ display: 'block' });
        $(event.target).parent('.comment-links').siblings(".create-comment-link").children('a').css({ display: 'block' });
    });

    function showDownVotingArrowOnVotedAnswer() {
        $('.answer_form').each(function (i, el) {
            is_voted_by_current_user = $(el).children('input[name*="[is_voted_by_current_user]"]').val();
            if(is_voted_by_current_user == 1) {
                $(el).find('.vote-down-off').css('display', '');
            }
        });
    }

/*    function hidePreviousErrorMessage(target_error_message) {
        /!*target_error_message = $(event.target).siblings('.voting_error');*!/
        $('.voting_error').each(function (i, el) {
            if(!$(el).is(target_error_message)) {
                $(el).css('display', 'none');
            }
        })
    }*/

    function hideAllErrorMessages() {
        /*target_error_message = $(event.target).siblings('.voting_error');*/
        $('.voting_error').each(function (i, el) {
            $(el).css('display', 'none');
        })
    }


    function hasUserAlreadyVoted() {
        alreadyVoted = false;
        $('.answer_form').each(function (i, el) {
            is_voted_by_current_user = $(el).children('input[name*="[is_voted_by_current_user]"]').val();
            if(is_voted_by_current_user == 1) {
                $(el).find('.vote-down-off').css('display', '');
                alreadyVoted = true;
                return true;
            }
        });
        return alreadyVoted;
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

    $('.vote-up-off').click(function(event) {
        if(hasUserAlreadyVoted()) {
            target_error_message = $(event.target).siblings('.voting_error');
            //hidePreviousErrorMessage(target_error_message);
            target_error_message.css('display', '');
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
        hideAllErrorMessages();
        $(event.target).siblings('.vote-down-off').css('display', 'none');
        counter_value = $(event.target).siblings('.vote-count-post').text();
        new_counter_value = --counter_value;
        $(event.target).siblings('.vote-count-post').text(new_counter_value);
        $(event.target).siblings('.voting_error').css('display', 'none');
    });

    /*
    execute function on initialisation and if the user clicks the upvoting and downvoting arrow
    Disable submit btn if the user has not already voted for an answer
    enable submit btn if the user has voted for an answer
     */
    $("input[name='cmd\[update\]']").on("click", function (e) {

    });

    showDownVotingArrowOnVotedAnswer();
});