(function ($) {
    $(function () {
        // folder lookup
        $('#igf-adm-filter-folder-parent').suggest(ajaxurl + '?action=infogalore_folders_folder_lookup');

        // copy shortcode to clipboard
        $('#igf-adm-folder-shortcode').on('click', function () {
            $(this).select();
            document.execCommand('copy');

            $('#igf-adm-folder-shortcode-hint').hide();
            $('#igf-adm-folder-shortcode-copied').show();
        });

        // create folder dialog
        $('#igf-adm-add-subfolder').on('click', function (event) {
            event.preventDefault();

            var parent_id = $(this).data('parent-id');
            var buttons = {};
            buttons[INFOGALORE_FOLDERS_ADMIN.ok_label] = function () {
                var title = $('#igf-adm-folder-name').val().trim();
                if ('' !== title) {
                    var data = {
                        action: 'infogalore_folders_create_folder',
                        parent_id: parent_id,
                        title: title,
                        nonce: $('#igf-adm-subfolders-nonce').val()
                    };
                    $('#igf-adm-folder-name').val('');
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: data,
                        success: function (data) {
                            if (data) {
                                window.location.href = data;
                            }
                        }
                    });

                    $(this).dialog("close");
                }
            };
            buttons[INFOGALORE_FOLDERS_ADMIN.cancel_label] = function () {
                $('#igf-adm-folder-name').val('');
                $(this).dialog("close");
            };

            //construct the dialog
            $("#igf-adm-add-folder-dialog").dialog({
                autoOpen: false,
                title: INFOGALORE_FOLDERS_ADMIN.prompt_label,
                modal: true,
                buttons: buttons
            });

            $("#igf-adm-add-folder-dialog").dialog("open");
        });

        // sortable folders in hierarchy metabox
        $('#igf-adm-subfolders').sortable({
            items: 'li',
            handle: '.igf-adm-subfolder-icon',
            stop: function () {
                var sorted = [];
                $('#igf-adm-subfolders li').each(function () {
                    sorted.push($(this).data('id'));
                });

                var data = {
                    action: 'infogalore_folders_sort_folders',
                    parent_id: $('#igf-adm-subfolders').data('parent-id'),
                    sorted: sorted.join(','),
                    nonce: $('#igf-adm-subfolders-nonce').val()
                };
                $('#igf-adm-subfolders-loading').show();

                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: data,
                    success: function (data) {
                        $('#igf-adm-subfolders-loading').hide();
                    }
                });
            }
        });

        $('#igf-adm-folder-files tbody')
            .on('mouseenter', '.igf-adm-shortcode input', function (e) {
                var title = $(this).val() + '<br>';
                title += INFOGALORE_FOLDERS_ADMIN.file_shortcode_tooltip;

                $('<p class="igf-adm-shortcode-tooltip"></p>')
                    .html(title)
                    .appendTo('body')
                    .fadeIn('slow');
            })
            .on('mouseleave', '.igf-adm-shortcode input', function (e) {
                $('.igf-adm-shortcode-tooltip').remove();
            })
            .on('mousemove', '.igf-adm-shortcode input', function (e) {
                var mousex = e.pageX + 20; //Get X coordinates
                var mousey = e.pageY + 10; //Get Y coordinates
                $('.igf-adm-shortcode-tooltip').css({top: mousey, left: mousex});
            })
            .on('click', '.igf-adm-shortcode input', function (e) {
                e.preventDefault();

                $(this).select();
                document.execCommand('copy');

                var $n = $('<div class="igf-adm-file-shortcode-copied">' + INFOGALORE_FOLDERS_ADMIN.file_shortcode_copied + '</div>');
                $n.appendTo($(this).parent()).fadeIn(100, function () {
                    $(this).fadeOut(2000);
                });
            });
    });
})(jQuery);
