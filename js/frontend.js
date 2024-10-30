(function ($) {
    $(function () {
        $('a.igf-download').on('click', function (e) {
            var data = {
                action: 'infogalore_folders_downloads_counter',
                security: INFOGALORE_FOLDERS.security,
                fileid: $(this).data('id')
            };

            $.post(INFOGALORE_FOLDERS.ajaxurl, data, function (response) {
            });
        });
    });
})(jQuery);