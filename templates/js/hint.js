$(document).ready(function() {

    var task = $('#task')[0];
    var counter = 0;

    function increase_counter(number_only) {
        if(number_only) {
            return counter;
        }

        counter++;
        return counter;
    }

    function decrease_counter() {
        return counter--;
    }

    var hint_form_group_selector = $('.hint_form:first-child');

    function toggle_form_header_gui_and_label() {
        if($('.hint_form').filter(function() {return $(this).css('display') == 'inline-block'}).length) {
            hint_form_group_selector.parents('.form-group').prev().css('display', '');
            hint_form_group_selector.parents('.form-group').children('.control-label').css('display','');
        } else {
            hint_form_group_selector.parents('.form-group').prev().css('display', 'none');
            hint_form_group_selector.parents('.form-group').children('.control-label').css('display','none');
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

    function initaliseNameAttributes() {
        $('.hint_form').each(function (i) {
            var hint_index = i;
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

    function initaliseRemoveBtnIds() {
        $('.remove_hint_btn').each(function(i) {
            $( this ).attr('id', 'remove_hint_'+  i);
        });
    }

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

    function adoptInputHintId(element_selectors, id, hint_id) {
        for(i=0; i < element_selectors.length; i++) {
            el = $(element_selectors[i]);
            el_name = el.attr("name");
            el_new_name = el_name.replace(new RegExp(id), hint_id);
            el.attr('name', el_new_name);
        }
    }

    function cleanHintCode() {
        ta = $('#task');
        text = ta.val();
        var newText = text.replace(/\[hint[\s\S\d]*?\]/g, '[temp]');
        newText = newText.replace(/\[\/hint\]/g, '[/temp]');

        var regex_hints = /\[hint (\d+)\]/gi;
        hint_matches = text.match(regex_hints);

        if (hint_matches) {
            var regex_number = /(\d+)/gi;

            hint_numbers = hint_matches.toString().match(regex_number);

            for(var i = 0; i < hint_numbers.length; i++) {
                id = hint_numbers[i];
                remove_btn = $('#remove_hint_' + id);
                var incrementer = i;
                var hint_id = ++incrementer;

                remove_btn.attr('id', 'remove_hint_' + hint_id);

                hint_to_hidden_input = $("input[name=hint\\[" + id + "\\]\\[label\\]]");

                hint_id_hidden_input = $("input[name=hint\\[" + id + "\\]\\[hint_id\\]]");

                is_template = $("input[name=hint\\[" + id + "\\]\\[is_template\\]]");

                lvl_1_hint = $("input[name=hint\\[" + id + "\\]\\[lvl_1_hint\\]]");

                lvl_1_minus_points = $("input[name=hint\\[" + id + "\\]\\[lvl_1_minus_points\\]]");

                lvl_2_hint = $("input[name=hint\\[" + id + "\\]\\[lvl_2_hint\\]]");

                lvl_2_minus_points = $("input[name=hint\\[" + id + "\\]\\[lvl_2_minus_points\\]]");

                selectors = [hint_to_hidden_input, hint_id_hidden_input,is_template, lvl_1_hint, lvl_1_minus_points, lvl_2_hint, lvl_2_minus_points];

                adoptInputHintId(selectors, id, hint_id);

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
        var hint_tos = $('.hint_to');

        for(i = 0; i < hint_tos.length; i++) {
            hint_to_jquery_object = $(hint_tos[i]);
            hint_to_label = $(hint_to_jquery_object.children("div.col-sm-9")[0]).children("label");
            hint_to_label_text = $(hint_to_jquery_object.children("div.col-sm-9")[0]).children("label").text();
            hint_to_label.text(hint_to_label_text.replace(/[0-9]/, i));
        }
    }

    function setHintToLabelByHiddenInput() {
        var hint_tos = $('.hint_to');

        for(i = 0; i < hint_tos.length; i++) {
            hint_to_jquery_object = $(hint_tos[i]);
            hint_to_label = $(hint_to_jquery_object.children("div.col-sm-9")[0]).children("label");
            hint_to_label_text = $(hint_to_jquery_object.children("div.col-sm-9")[0]).children("label").text();

            hidden_hint_to_label_input = hint_to_label.siblings("input[name*='label']");
            hint_to_label.text(hidden_hint_to_label_input.val());
        }
    }

    function adoptIncreaseCounter() {
        var hint_form = $(".hint_form");

        if(hint_form.length > 1) {
            for(i = 1; i < hint_form.length; i++) {
                increase_counter();
            }
        }
    }

    function loadExistingHintData() {
        var existing_hint_data = $('.existing-hint-data');
        if(existing_hint_data.length) {
            existing_hint_data.each( function(index, element) {

                var hint_form = $(".hint_form");
                var hidden_form = hint_form.filter(function() {
                    return $(this).css('display') == 'none';
                });

                var hint_data_form = hidden_form.clone(true);

                $(hint_data_form).css({ display: 'inline-block' });

                $(hint_data_form).appendTo(hidden_form.closest('.col-sm-9'));

                jquery_element = $(element);

                json_object_data_hints = $.parseJSON(jquery_element.attr('data-hints'));

                is_template_input = hint_data_form.find('input[name*="[is_template]"]');

                $(is_template_input).val(json_object_data_hints.is_template);

                hint_to = hint_data_form.find('.hint_to');

                hint_to_label = $(hint_to).children("div.col-sm-9").children("label");

                hint_to_label.text(json_object_data_hints.label);

                json_object_data_level_1 = $.parseJSON(jquery_element.attr('data-level-1'));

                lvl_1_hint_input = $(hint_data_form.find("input[name*='lvl_1_hint']"));

                lvl_1_hint_input.val(json_object_data_level_1.hint);

                hint_id = $(hint_data_form.find("input[name*='hint_id']"));

                hint_id.val(json_object_data_hints.id);

                json_object_data_level_1_minus_points = $.parseJSON(jquery_element.attr('data-level-1-minus-points'));

                lvl_1_minus_ponts_input = $(hint_data_form.find("input[name*='lvl_1_minus_points']"));

                lvl_1_minus_ponts_input.val(json_object_data_level_1_minus_points.minus_points);

                json_object_data_level_2 = $.parseJSON(jquery_element.attr('data-level-2'));

                lvl_2_hint_input = $(hint_data_form.find("input[name*='lvl_2_hint']"));

                lvl_2_hint_input.val(json_object_data_level_2.hint);

                json_object_data_level_2_minus_points = $.parseJSON(jquery_element.attr('data-level-2-minus-points'));

                lvl_2_minus_ponts_input = $(hint_data_form.find("input[name*='lvl_2_minus_points']"));

                lvl_2_minus_ponts_input.val(json_object_data_level_2_minus_points.minus_points);
            });
            toggle_form_header_gui_and_label();
        }
    }

    toggle_form_header_gui_and_label();

    adoptHintToLabel();

    setHintToLabelByHiddenInput();

    loadExistingHintData();

    adoptIncreaseCounter();

    initaliseNameAttributes();

    initaliseRemoveBtnIds();

    $('#hint_trigger_text').click(function() {
        var sel;
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

            var hint_form = $(".hint_form");
            var hidden_form = hint_form.filter(function() {
                return $(this).css('display') == 'none';
            });

            var append_target = hidden_form.closest('.col-sm-9');
            var new_hint_form = hidden_form.clone(true);

            new_hint_form.appendTo(append_target);

            new_hint_form.css({ display: 'inline-block'});

            new_hint_form.find("div.hint_to").children('.col-sm-9').children("label").text(task.value.substring(startPos, endPos) + task.value.substring(endPos, task.value.length));

            new_hint_form.find('input[name*="[is_template]"]').val("1");

            setRemoveBtnId(new_hint_form);

            setNameAttribute(new_hint_form);

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

            $( event.target ).closest( ".hint_form" ).remove();
            decrease_counter();

            hidden_form = $('.hint_form').filter(function() {
                return $(this).css('display') == 'none';
            });

            hidden_form.children('.col-sm-9').last().children('a').attr('id', 'remove_hint_' + increase_counter(true));

            setNameAttribute(hidden_form);

            adoptHintToLabel();

            toggle_form_header_gui_and_label();
        });
    }, 2000);

    $("input[name='cmd\[update\]']").on("click", function (e) {
        var hint_tos = $('.hint_to');

        for(i = 0; i < hint_tos.length; i++) {

            hint_to_jquery_object = $(hint_tos[i]);

            hint_to_label = $(hint_to_jquery_object.children("div.col-sm-9")[0]).children("label");

            hint_to_label_text = $(hint_to_jquery_object.children("div.col-sm-9")[0]).children("label").text();

            hidden_hint_to_label_input = hint_to_label.siblings("input[name*='label']");

            hidden_hint_to_label_input.val(hint_to_label_text);
        }
        hidden_form = $('.hint_form').filter(function() {
            return $(this).css('display') == 'none';
        });

        hidden_form.find('input[name*="[is_template]"]').val("0");
    })
});
