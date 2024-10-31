jQuery( function ( $ ) {
    $('select[data-select="roles"]').select2({
        width : 350
    });
});

jQuery(document).ready(function($) {
    $('[data-action--delete]').click(function () {
        if (!showNotice.warn()) {
            return false;
        }
    });

    $('#submit').click(function() {

        var form = $(this).parents('form');

        if (!validateForm(form)) {
            return false;
        }
    });
});