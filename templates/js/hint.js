$(document).ready(function() {

    //TODO: remove console.logs and unecessary comments. Handle document.selection and else hint_trigger_text click cases

    var hints = [];

    // boolean argument number_only is optional. If it is set the function returns only the counter.
    function increase_counter(number_only) {
        if(typeof increase_counter.counter == 'undefined') {
            increase_counter.counter = 1;
        } else if(number_only) {
            return increase_counter.counter;
        }
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

    /**
     *
     * The splice() method changes the content of a string by removing a range of
     * characters and/or adding new characters.
     *
     * @param string The string where the splicing is applied to.
     * @param start start Index at which to start changing the string.
     * @param rem An integer indicating the number of old chars to remove.
     * @param newSubStr The String that is spliced in.
     * @return {string} A new string with the spliced substring.
     */
    function splice(string, start, rem, newSubStr) {
        return string.slice(0, start) + newSubStr + string.slice(start + Math.abs(rem));
    }

    var task = $('#task')[0];

    function initaliseNameAttributes() {
        $('.hint_form').each(function (i) {
            var hint_index = i + 1;
            //TODO handle hidden input
            $( this ).find('input').each(function (i, el) {
                var input_jquery_object = $(el);
                var name_attr = input_jquery_object.attr('name');
                if (typeof name_attr !== typeof undefined && name_attr !== false) {
                    var old_name = input_jquery_object.attr('name');
                    start = old_name.indexOf('[') + 1;
                    var new_name = splice(old_name, start, 0, hint_index);
                    input_jquery_object.attr('name', new_name);
                }
            });
        })
    }

    function setNameAttribute(el) {
        el.find('input').each(function (i, el) {
            var input_jquery_object = $(el);
            var name_attr = input_jquery_object.attr('name');
            if (typeof name_attr !== typeof undefined && name_attr !== false) {
                var old_name = input_jquery_object.attr('name');
                var old_name_only_string = old_name.replace(/[0-9]/, '');
                start = old_name_only_string.indexOf('[') + 1;
                var new_name = splice(old_name_only_string, start, 0, increase_counter(true));
                input_jquery_object.attr('name', new_name);
            }
        })
    }

    initaliseNameAttributes();

    function initaliseRemoveBtnIds() {
        $('.remove_hint_btn').each(function(i) {
            $( this ).attr('id', 'remove_hint_'+  ++i);
        });
    }

    /**
     * Input name muss beim initialisieren, hinzufügen und entfernen eines Tipps gesetzt werden
     */

    initaliseRemoveBtnIds();

    function setRemoveBtnId(el) {
        remove_hint_btn = el.children('.col-sm-9').last().children('a');
        remove_hint_btn.attr('id', 'remove_hint_' + increase_counter(true));
    }

    function removeHintFromTask(hint_count) {
        text = task.value;
        var pos = parseInt(hint_count, 10);
        var regexExpression = '\\[hint ' + pos + '\\](.*?)\\[\\/hint\\]';
        console.log("REMOVE HINT FROM TASK regexExpression");
        console.log(regexExpression);
        var regex = new RegExp(regexExpression, 'i');
        var newText = text.replace(regex, '');
        task.value = newText;
        cleanHintCode();
    }
/*
    function getCleanedHintNumbers(hint_numbers) {
        for (i = 0; i < hint_numbers.length; i++) {
            //Array index starts at 0, but hint id should start at 1
            incrementer = i;
            hint_numbers[i] = ++incrementer;
        }
        return hint_numbers;
    }*/

    //TODO: make first argument array to reduce the number of function calls
    function adoptInputHintId(element_selectors, id, hint_id) {
        for(i=0; i < element_selectors.length; i++) {
            el = $(element_selectors[i]);
            el_name = el.attr("name");
            el_new_name = el_name.replace(new RegExp(id), hint_id);
            el.attr('name', el_new_name);
        }
/*        el = $(element_selector);
        el_name = el.attr("name");
        el_new_name = el_name.replace(new RegExp(id), hint_id);
        el.attr('name', el_new_name);*/
    }

    function cleanHintCode() {
        ta = $('#task');
        text = ta.val();
        console.log("clean Hint Code task text");
        console.log(text);
        var newText = text.replace(/\[hint[\s\S\d]*?\]/g, '[temp]');
        newText = newText.replace(/\[\/hint\]/g, '[/temp]');
        //TODO vermutung problem text.match und newText.match
        console.log(newText);

        var regex_hints = /\[hint (\d+)\]/gi;
        hint_matches = text.match(regex_hints);

        console.log("hint_matches");
        console.log(hint_matches);

        if (hint_matches) {
            var regex_number = /(\d+)/gi;

            hint_numbers = hint_matches.toString().match(regex_number);

            console.log("hint_numbers");
            console.log(hint_numbers);
            console.log("hint_numbers.length");
            console.log(hint_numbers.length);

            /*
            TODO: function loop through hint_numbers. Replace number of hint n with number of increment variable starting at 1
            comment that this handle the problem if a hint not at the end will be removed.
             */
/*            hint_numbers_cleaned = getCleanedHintNumbers(hint_numbers);

            console.log("hint_numbers_cleaned");
            console.log(hint_numbers_cleaned);*/

            for(var i = 0; i < hint_numbers.length; i++) {
                console.log("i");
                console.log(i);
                //adopt the ids of the remove btns
                //remove_btn_id = hint_numbers[i];

                //to select the right remove hint btns the original id has to be used. Cleaning of hints has to be made afterwards.
                id = hint_numbers[i];
                console.log("id");
                console.log(id);
                remove_btn = $('#remove_hint_' + id);
                console.log(remove_btn);
                var incrementer = i;
                console.log("incrementer");
                console.log(incrementer);
                var hint_id = ++incrementer;
                console.log("hint_id");
                console.log(hint_id);

/*                console.log("hint_numbers_cleaned[i]");
                console.log(hint_numbers_cleaned[i]);*/

                //remove_btn.attr('id', 'remove_hint_' + hint_numbers_cleaned[i]);
                remove_btn.attr('id', 'remove_hint_' + hint_id);

                //is_template = $("input[name=" + name_string + "]");
                is_template = $("input[name=hint\\[" + id + "\\]\\[is_template\\]]");

                lvl_1_hint = $("input[name=hint\\[" + id + "\\]\\[lvl_1_hint\\]]");

                lvl_1_minus_points = $("input[name=hint\\[" + id + "\\]\\[lvl_1_minus_points\\]]");

                lvl_2_hint = $("input[name=hint\\[" + id + "\\]\\[lvl_2_hint\\]]");

                lvl_2_minus_points = $("input[name=hint\\[" + id + "\\]\\[lvl_2_minus_points\\]]");

                selectors = [is_template, lvl_1_hint, lvl_1_minus_points, lvl_2_hint, lvl_2_minus_points];

                adoptInputHintId(selectors, id, hint_id);

                //replace the hint number id
                newText = newText.replace(/\[temp\]/, '[hint ' + hint_id + ']');
                newText = newText.replace(/\[\/temp\]/, '[/hint]');
            }
            task.value = newText;
        }
    }

    /*
    take the text from task where [hint n] correspond to the incremented value (take [hint n]test[/hint]
    insert the value in the text of the hint_to_label_text
     */
    function adoptHintToLabel() {
        //console.log("ADOPT HINT TO LABEL FUNCTION")
        var hint_tos = $('.hint_to');
        //console.log(hint_tos);

        for(i = 0; i < hint_tos.length;) {
            //console.log("i: " + i);
            hint_to_jquery_object = $(hint_tos[i]);
            //console.log(hint_to_jquery_object);
            hint_to_label = $(hint_to_jquery_object.children("div.col-sm-9")[0]).children("label");
            //console.log(hint_to_label);
            hint_to_label_text = $(hint_to_jquery_object.children("div.col-sm-9")[0]).children("label").text();
            //console.log(hint_to_label_text);
            hint_to_label.text(hint_to_label_text.replace(/[0-9]/, ++i));
            //console.log(hint_to_label.text());
        }
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


                hidden_form.css({ display: 'inline-block'});

                hidden_form.find("div.hint_to").children('.col-sm-9').children("label").text(task.value.substring(startPos, endPos) + task.value.substring(endPos, task.value.length));

                //hidden_form.closest('.hint_to').children('col-sm-9 > label').value = task.value.substring(startPos, endPos);

                //shown hint forms will be validated because of the value 1
                hidden_form.find('input[name*="[is_template]"]').val("1");

                hidden_form = copy_hidden_form;

                setRemoveBtnId(hidden_form);

                setNameAttribute(hidden_form);

                // set value of is_template to 0. This makes sure that this input will not be considered in check input
                hidden_form.find('input[name*="[is_template]"]').val("0");

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

            //the id of the not displayed hint form remove hint button has to be changed as well as the name attribute
            hidden_form = $('.hint_form').filter(function() {
                return $(this).css('display') == 'none';
            });

            hidden_form.children('.col-sm-9').last().children('a').attr('id', 'remove_hint_' + increase_counter(true));

/*            hidden_form.children('div.col-sm-9').each(function (i, el) {
                var input = $(el).find('input');
                var name_attr = input.attr('name');
                if (typeof name_attr !== typeof undefined && name_attr !== false) {
                    var old_name = input.attr('name');
                    var old_name_only_string = old_name.replace(/[0-9]/g, '');
                    input.attr('name', old_name_only_string + increase_counter(true));
                }
            });*/

            setNameAttribute(hidden_form);

            adoptHintToLabel();

            toggle_form_header_gui_and_label();
        });
    }, 2000);
});

