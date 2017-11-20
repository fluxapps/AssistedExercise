$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();

    var tooltips = $('#question_text').children('a');

    var existing_hint_data = $('.existing-hint-data');
    var data_hints = [];
    var data_level_1 = [];
    var data_level_2 = [];
    var data_level_1_minus_points = [];
    var data_level_2_minus_points = [];
    existing_hint_data.each( function (index, element) {
        data_hints.push(JSON.parse($(element).attr('data-hints')));
        data_level_1.push(JSON.parse($(element).attr('data-level-1')));
        data_level_2.push(JSON.parse($(element).attr('data-level-2')));
        data_level_1_minus_points.push(JSON.parse($(element).attr('data-level-1-minus-points')));
        data_level_2_minus_points.push(JSON.parse($(element).attr('data-level-2-minus-points')));
    });

    tooltips.each( function(index, element) {
        $(element).attr('data-toggle', 'popover');
        var hint_number = index + 1;
        $(element).attr('title', 'Hint ' + hint_number);

        data_level_1_element = data_level_1[index];
        lvl_1_hint = data_level_1_element.hint;
        lvl_1_hint_point_id = data_level_1_element.point_id;

        for(var i = 0; i < data_level_1_minus_points.length; i++) {
            var obj_data_level_1 = data_level_1_minus_points[i];

            if(lvl_1_hint_point_id === obj_data_level_1.id) {
                lvl_1_hint_minus_points = obj_data_level_1.minus_points;
            }
        }

        data_level_2_element = data_level_2[index];
        lvl_2_hint = data_level_2_element.hint;
        lvl_2_hint_point_id = data_level_2_element.point_id;


        for(var i = 0; i < data_level_2_minus_points.length; i++) {
            var obj_data_level_2 = data_level_2_minus_points[i];

            if(lvl_2_hint_point_id === obj_data_level_2.id) {
                lvl_2_hint_minus_points = obj_data_level_2.minus_points;
            }
        }

        $(element).attr('data-html', 'true');

        if(data_level_2) {
            $(element).attr('data-content', 'If you use a hint the maximum possible points will be decreased. The following hints are available: \n' + "<a data-hint-id='" + hint_number + "' data-level='1'>Level 1 hint</a> Minus Points: " + lvl_1_hint_minus_points  + " <a data-hint-id='" + hint_number + "' data-level='2'>Level 2 hint</a> Minus Points: " + lvl_2_hint_minus_points)
        } else {
            $(element).attr('data-content', 'If you use a hint the maximum possible points will be decreased. The following hints are available: \n' + "<a data-hint-id='" + hint_number + "' data-level='1'>Level 1 hint</a> Minus Points: " + lvl_1_hint_minus_points)
        }
        $(element).attr('data-placement', 'top');
    });

    var hint_minus_points = {};
    var data_level_object = {};

    function noteUsedHints(hint_id, data_level) {

        for(var i=0; i < data_hints.length; i++) {
            if(data_hints[i].hint_number == hint_id) {
                var db_hint_id = data_hints[i].id;
                break;
            }
        }

        var data_level_key = "data_level_" + data_level;

        if( !(db_hint_id in hint_minus_points) ) {
            hint_minus_points[db_hint_id] = {};
        }

        if( (data_level_key in hint_minus_points[db_hint_id]) ) {
            return;
        }

        if(data_level == 1) {
            for(var i=0; i < data_level_1.length; i++) {
                if(db_hint_id == data_level_1[i].hint_id) {
                    var points_id = data_level_1[i].point_id;
                    break;
                }
            }
            for(var i=0; i < data_level_1_minus_points.length; i++) {
                if(points_id == data_level_1_minus_points[i].id) {
                    var minus_points = data_level_1_minus_points[i].minus_points;
                    break;
                }
            }
        } else {
            for(var i=0; i < data_level_2.length; i++) {
                if(db_hint_id == data_level_2[i].hint_id) {
                    var points_id = data_level_2[i].point_id;
                    break;
                }
            }
            for(var i=0; i < data_level_2_minus_points.length; i++) {
                if(points_id == data_level_2_minus_points[i].id) {
                    var minus_points = data_level_2_minus_points[i].minus_points;
                    break;
                }
            }
        }

        if(data_level == 1) {
            hint_minus_points[db_hint_id].data_level_1 = minus_points;
        } else {
            hint_minus_points[db_hint_id].data_level_2 = minus_points;
        }

        $('#used_hints').val(JSON.stringify(hint_minus_points));
    }

    var saved_popover_content = [];

    function createBackLink(hint_id, data_level) {
        var data_array = [];
        if(data_level == 1) {
            data_array = data_level_1;
        } else {
            data_array = data_level_2;
        }

        var hint_data = data_array[hint_id - 1].hint;
        return hint_data + " <a data-hint-id='" + hint_id + "' data-level='"+data_level+"' data-identifier='back_link'>Back</a>";
    }

    function prepareBackLinkEvent() {
        $('.popover-content').children("a[data-identifier='back_link']").on('click', function(event) {
            var hint_id = $(event.target).attr('data-hint-id');
            var data_level = $(event.target).attr('data-level');
            var popover_link = $("a.hint-popover-link[data-hint-id='"+hint_id+"']").first();

            popover_link.attr('data-content', saved_popover_content[hint_id][data_level]);
            popover_link.popover('show');

            prepareLinkToHintData();
        })
    }

    function prepareLinkToHintData() {

        $('.popover-content').children('a').on('click', function(event) {

            var hint_id = $(event.target).attr('data-hint-id');
            var data_level = $(event.target).attr('data-level');
            noteUsedHints(hint_id, data_level);

            //save old popover_content before removal
            var popover_content = $(event.target).parent();
            if(!saved_popover_content[hint_id]) {
                saved_popover_content[hint_id] = [];
            }

            if(popover_content.html().indexOf('back_link') >= 0) {


            } else {

                saved_popover_content[hint_id][data_level] = popover_content.html();
            }

            var popover_link = $("a.hint-popover-link[data-hint-id='"+hint_id+"']").first();
            var new_content = createBackLink(hint_id, data_level);

            popover_link.attr('data-content', new_content);
            popover_link.popover('show');

            prepareBackLinkEvent();
        });
    }

    $('#task').children('a.hint-popover-link').on('click', function() {

        setTimeout(function() {
            prepareLinkToHintData();
        }, 10);
    });

    if($('input[name=\'show_hints\']').is(":checked")) {
        toggleHints(true);
    } else {
        toggleHints(false);
    }

     $('[data-toggle="popover"]').popover();

    function toggleHints(show_hints) {
        if (show_hints) {
            tooltips.removeAttr('style');
        } else {
            tooltips.attr('style', 'display:none;');
        }
    }

    $('input[name=\'show_hints\']').change(
        function(){
            if (this.checked) {
                toggleHints(true);
                $(this[0]).checked = false;
            } else {
                toggleHints(false);
                $(this[0]).checked = true;
            }
        });
});

