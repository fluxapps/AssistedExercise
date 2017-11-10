$(document).ready(function() {
    $('input[name=\'show_used_hints\']').change(
        function(){
            if (this.checked) {
                $('#il_prop_cont_used_hints').attr('style', '');
            } else {
                $('#il_prop_cont_used_hints').attr('style', 'display:none');
            }
        });
});