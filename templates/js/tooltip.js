$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();

    var tooltips = $('#task').children('a');

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
        //$(element).attr('data-trigger', 'focus');
    });

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

            // EINKOMMENTIEREN
            popover_link.attr('data-content', saved_popover_content[hint_id][data_level]);
            popover_link.popover('show');

            // AUSKOMMENTIEREN
            /*
            popover_link.popover({
                html: saved_popover_content[hint_id][data_level]
            });
            */
            prepareLinkToHintData();
        })
    }

    function prepareLinkToHintData() {

        $('.popover-content').children('a').on('click', function(event) {

            console.log('clicked link inside popover content');

/*            debugger;

            var link_element = event.target;

            var popover_link = $('*[data-toggle="popover"]').first();

            var replace_link_element = popover_link;

            var id = event.target.id;
            var data_level = event.target.attributes[1].value;
            var popover_content = $(event.target).parent();

            saved_popover_content[id] = [];
            saved_popover_content[id][data_level] = popover_content.html();

            var temp_id = id;

            $(replace_link_element).attr('data-toggle', 'popover');

            $(replace_link_element).attr('title', 'Hint ' + id);

            if(data_level == 1) {

                lvl_1_hint_data = data_level_1[--temp_id].hint;
                $(replace_link_element).attr('data-content', lvl_1_hint_data + " <a id='" + id + "' data-level='1' data-identifier='back_link'>Back</a>");
                /!*popover_content.html(lvl_1_hint_data + " <a id='" + id + "' data-level='1' data-identifier='back_link'>Back</a>");*!/
            } else {

                lvl_2_hint_data = data_level_2[--temp_id].hint;

                $(replace_link_element).attr('data-content', lvl_2_hint_data + " <a id='" + id + "' data-level='2' data-identifier='back_link'>Back</a>");
                /!*popover_content.html(lvl_2_hint_data + " <a id='" + id + "' data-level='2' data-identifier='back_link'>Back</a>");*!/
            }
            popover_link.replaceWith(replace_link_element);

            if(data_level == 1) {
                popover_content.html(lvl_1_hint_data + " <a id='" + id + "' data-level='1' data-identifier='back_link'>Back</a>");
            } else {
                popover_content.html(lvl_2_hint_data + " <a id='" + id + "' data-level='2' data-identifier='back_link'>Back</a>");
            }

            $('[data-toggle="popover"]').popover();*/



            var hint_id = $(event.target).attr('data-hint-id');
            var data_level = $(event.target).attr('data-level');
            //save old popover_content before removal
            var popover_content = $(event.target).parent();
            //save the data in an array
            if(!saved_popover_content[hint_id]) {
                saved_popover_content[hint_id] = [];
            };


            console.log('popover_content.html():');
            console.dir(popover_content.html());

            // WEGNEHMEN
            if(popover_content.html().indexOf('back_link') >= 0) {


            } else {

                saved_popover_content[hint_id][data_level] = popover_content.html();
            }

            console.log('saved_popover_content:');
            console.dir(saved_popover_content);

            var popover_link = $("a.hint-popover-link[data-hint-id='"+hint_id+"']").first();
            var new_content = createBackLink(hint_id, data_level);

            console.log('new_content:');
            console.log(new_content);
            console.log('popover_link:');
            console.dir(popover_link);

            // EINKOMMENTIEREN
            popover_link.attr('data-content', new_content);
            popover_link.popover('show');

            // AUSKOMMENTIEREN
            /*
            popover_link.popover({
                html: new_content
            });
            */

/*            var popover = popover_content.parents('div.popover');
            glyphicon = popover.prev('a').children('span.glyphicon');
            var position = glyphicon.position();*/
/*            var absolute_position = glyphicon.offset();
/!*            var position_left = popover.css('left');
            popover.attr('style', 'top:-79px;left:' + position_left + ';display: block');*!/
            popover.attr('style', 'top:-79px;left:' + absolute_position.left + ';display: block');*/
            prepareBackLinkEvent();
        });
    }

    $('#task').children('a.hint-popover-link').on('click', function() {

        console.log('clicked hint link that should cause a popover to appear');

        setTimeout(function() {
            prepareLinkToHintData();
/*            $('.popover-content').children('a').on('click', function(event) {
                var id = event.target.id;
                var data_level = event.target.attributes[1].value;
                var popover_content = $(event.target).parent();
                //save the data in an array
                saved_popover_content[id] = [];
                saved_popover_content[id][data_level] = popover_content.html();

                /!**
                 * 1)replace popover_content inner HTML with the corresponding lvl_1_hint_data
                 *  a) and a link with the corresponding hint_id
                 *  b) if the link is clicked
                 *      -get the value of the saved_popver_content array
                 *      -replace popover-content with the old data
                 *!/
                var temp_id = id;
                if(data_level == 1) {
                    lvl_1_hint_data = data_level_1[--temp_id].hint;
                    popover_content.html(lvl_1_hint_data + " <a id='" + id + "' data-level='1' data-identifier='back_link'>Back</a>");
                } else {
                    lvl_2_hint_data = data_level_2[--temp_id].hint;
                    popover_content.html(lvl_2_hint_data + " <a id='" + id + "' data-level='2' data-identifier='back_link'>Back</a>");
                }
                prepareBackLinkEvent();
            });*/
        }, 10);
    });
     $('[data-toggle="popover"]').popover();
});

