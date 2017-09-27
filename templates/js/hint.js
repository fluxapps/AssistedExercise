$(document).ready(function() {

    //TODO: remove console.logs and unecessary comments. Handle document.selection and else hint_trigger_text click cases

    var hints = [];

    // boolean argument get_current_counter is optional
    function increase_counter(get_current_counter) {
        if(typeof increase_counter.counter == 'undefined') {
            increase_counter.counter = 1;
        } else if(get_current_counter) {
            return increase_counter.counter;
        }
        //console.log(increase_counter.counter);
        return increase_counter.counter++;
    }

    function decrease_counter() {
        return increase_counter.counter--;
    }

    var hint_counter = (function () {
        var i = 1;

        return function () {
            return i++;
        }
    })();

    function toggle_form_header_gui_and_label() {
        if($('.hint_form').filter(function() {return $(this).css('display') == 'inline-block'}).length) {
            $('h3.ilHeader:contains("Hints")').parent().css('display', '');
            $('label.control-label:contains("Hints")').parent().css('display','');
        } else {
            $('h3.ilHeader:contains("Hints")').parent().css('display', 'none');
            $('label.control-label:contains("Hints")').parent().css('display','none');
        }
    }

    var task = $('#task')[0];


    function initaliseRemoveBtnIds() {
        $('.remove_hint_btn').each(function(i) {
            $( this ).attr('id', 'remove_hint_'+  ++i);
        });
    }

    initaliseRemoveBtnIds();

    function setRemoveBtnId(el) {
        remove_hint_btn = el.children('.col-sm-9').last().children('a');
        remove_hint_btn.attr('id', 'remove_hint_' + increase_counter(true));
    }

    function removeHintFromTask(hint_count) {
        text = task.value;
        var pos = parseInt(hint_count, 10);
        var regexExpression = '\\[hint ' + pos + '\\](.*?)\\[\\/hint\\]';
        var regex = new RegExp(regexExpression, 'i');
        var newText = text.replace(regex, '');
        task.value = newText;
        cleanHintCode();
    }


        /*
        1) get all hint numbers in [hint n]s
        2) save them into an array
        3) make a loop which starts at index 0*, loop as long as there are elements in the array of hint numbers
        4) search the corresponding remove_hint_btn which has the id of the actual hint number in the array
        5) change the id of the remove_btn to the value of the increment variable
        6) change the number of [hint n] to the value of the incremented variable
            a) replace n on the corresponding hint in the hint_matches array
        7) return the number of hints
         */

    function cleanHintCode() {
        text = task.value;
        var newText = text.replace(/\[hint[\s\S\d]*?\]/g, '[temp]');
        newText = newText.replace(/\[\/hint\]/g, '[/temp]');

        var regex_hints = /\[hint (\d+)\]/gi;
        hint_matches = text.match(regex_hints);
        console.log("MATCHES");
        console.log(hint_matches);

        var regex_number = /(\d+)/gi;
        hint_numbers = hint_matches.toString().match(regex_number);
        console.log("HINT NUMBERS");
        console.log(hint_numbers);

        console.log("HINT NUMBERS LENGTH");
        console.log(hint_numbers.length);

        for(var i = 0; i <= hint_numbers.length; i++) {

            console.log("ITERATION " + i);
            //adopt the ids of the remove btns
            remove_btn_id = hint_numbers[i];
            console.log("REMOVE BTN ID");
            console.log(remove_btn_id);
            remove_btn = $('#remove_hint_' + remove_btn_id);
            console.log("REMOVE BTN");
            console.log(remove_btn);
            var incrementer = i;
            var hint_id = ++incrementer;
            console.log("HINT ID");
            console.log(hint_id);
            remove_btn.attr('id', 'remove_hint_' + hint_id);
            //replace the hint number id
            newText = newText.replace(/\[temp\]/, '[hint ' + hint_id + ']');
            newText = newText.replace(/\[\/temp\]/, '[/hint]');
        }
        task.value = newText;
    }

    toggle_form_header_gui_and_label();

    $('#hint_trigger_text').click(function() {
            var sel, range;
            var code_start = "[hint " + increase_counter() + "]";
            var code_end = "[/hint]";

            if (document.selection) {
                //For browsers like Internet Explorer
                task.focus();
                sel = document.selection.createRange();
                sel.text = code_start + sel.text + code_end;
                task.focus();
            }
            else if (task.selectionStart || task.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                var startPos = task.selectionStart;
                var endPos = task.selectionEnd;
                var scrollTop = task.scrollTop;

                task.value = task.value.substring(0, startPos)
                    + code_start
                    + task.value.substring(startPos, endPos)
                    + code_end
                    + task.value.substring(endPos, task.value.length);

                task.focus();
                task.scrollTop = scrollTop;
                //selects all hint forms
                var hint_form = $(".hint_form");
                //select the hint form which is not displayed
                var hidden_form = hint_form.filter(function() {
                    return $(this).css('display') == 'none';
                });
                //append_target is the wrapper for the hint forms
                var append_target = hint_form.closest('.col-sm-9');
                var copy_hidden_form = hidden_form.clone(true);
                copy_hidden_form.appendTo(append_target);

                console.log("Remove BTN ID Hidden Form");
                console.log(hidden_form.children('.col-sm-9').last().children('a').attr('id'));

                hidden_form.css({ display: 'inline-block'});

                console.log("START TEST HINT TO");
                //console.log(hidden_form);
                //console.log(hidden_form.children("div.hint_to"));
                //console.log(hidden_form.children().eq(0));
                console.log(hidden_form.find("div.hint_to").children('.col-sm-9').children("label"));

                hidden_form.find("div.hint_to").children('.col-sm-9').children("label").text(task.value.substring(startPos, endPos) + task.value.substring(endPos, task.value.length));
                //console.log(hidden_form.closest("div.hint_to"));

                //hidden_form.closest('.hint_to').children('col-sm-9 > label').value = task.value.substring(startPos, endPos);

                console.log("Remove BTN ID copy hidden form");
                console.log(copy_hidden_form.children('.col-sm-9').last().children('a').attr('id'));

                hidden_form = copy_hidden_form;

                setRemoveBtnId(hidden_form);

                toggle_form_header_gui_and_label();

            } else {
                task.value += code_start + code_end;
                task.focus();
            }
    });

    setTimeout(function() {
        var remove_hint_btn_var = $( ".remove_hint_btn");

        $( ".remove_hint_btn").on( "click", function( event ) {
            var getPosition = $(this).attr('id');
            var pos = getPosition.split('_');
            removeHintFromTask(pos[2]);

            alert("test");
            $( event.target ).closest( ".hint_form" ).remove();
            decrease_counter();


            //the id of the not displayed hint form has to be changed
            hidden_form = $('.hint_form').filter(function() {
                return $(this).css('display') == 'none';
            });


            hidden_form.children('.col-sm-9').last().children('a').attr('id', 'remove_hint_' + increase_counter(true));

            toggle_form_header_gui_and_label();
        });
    }, 2000);
});

