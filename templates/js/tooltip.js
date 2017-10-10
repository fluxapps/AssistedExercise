$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();

    var tooltips = $('#task').children('a');

    /**
     * 1) json parse all data
     * 2) save the parsed data in arrays
     * 3) get in tooltips each the object at the corresponding index
     * 4) save the needed object data in data-content
     */

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

    console.log(data_hints);

    tooltips.each( function(index, element) {
        $(element).attr('data-toggle', 'popover');
        var hint_number = index + 1;
        $(element).attr('title', 'Hint ' + hint_number);
        $(element).attr('data-content', 'test');
        $(element).attr('data-placement', 'top');
        //$(element).attr('data-trigger', 'focus');
    });
    $('[data-toggle="popover"]').popover();
});

