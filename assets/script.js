(function($) {

    $(document).ready(function () {
        $('#wis-load').on('submit', function (e) {

            e.preventDefault();

            $('#wis-load .spinner').css('visibility', 'visible');

            let data = {
                action: 'wis_load',
                urls: $(this).find('[name="url"]').val()
            };
    
            $.post(ajaxurl, data, function (resp) {
                $('#wis-load .spinner').css('visibility', 'hidden');

                if (resp.status == 'error') {
                    $('.wis-msg').addClass(resp.status).text(resp.message);
                } else {
                    $('.wis-loaded').append(resp);
                }
            });

            $(this).find('[name="url"]').val('');

        });
    });

})(jQuery);