(function (FOLDER, $) {
    FOLDER.media_frame = false;
    FOLDER.files = [];
    FOLDER.selected_id = 0;

    /**
     * Initialize files array with saved ids.
     */
    FOLDER.initFiles = function () {
        var ids = $('#folder_file_ids').val();
        if (ids) {
            FOLDER.files = $.map(ids.split(','), function (id) {
                return parseInt(id);
            });
        }
    };

    /**
     * Update file ids list according to current files list.
     */
    FOLDER.refreshFilesList = function () {
        var sorted = [];
        var count = 0;
        $('#igf-adm-folder-files tr').each(function () {
            sorted.push($(this).data('file-id'));
            count += 1;
        });
        $('#folder_file_ids').val(sorted.join(','));

        if (0 === count) {
            $('#igf-adm-empty-folder-message').show();
        } else {
            $('#igf-adm-empty-folder-message').hide();
        }
    };

    /**
     * Initialize and/or open media frame.
     * @param title
     * @param button_text
     */
    FOLDER.openModal = function (id, title, button_text) {
        if (!id) {
            id = 0;
        }
        FOLDER.selected_id = id;

        if (!title) {
            title = 'Add Files to Folder';
        }
        if (!button_text) {
            button_text = 'Add Files';
        }

        if (false === FOLDER.media_frame) {
            FOLDER.media_frame = wp.media.frames.files_frame = wp.media({
                title: title,
                button: {
                    text: button_text
                },
                multiple: 'add',
                toolbar: 'select'
            })
                .on('open', function () {
                    var selection = FOLDER.media_frame.state().get('selection');
                    if (selection) {
                        selection.set();
                    }

                    if (FOLDER.selected_id > 0) {
                        var attachment = wp.media.attachment(FOLDER.selected_id);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    }
                })
                .on('select', function () {
                    var files = FOLDER.media_frame.state().get('selection').toJSON();

                    $.each(files, function (i, file) {
                        if (file && file.id) {
                            FOLDER.addFile(file);
                        }
                    });
                });
        }

        FOLDER.media_frame.open();
    };

    /**
     * Add uploaded file to files list.
     * @param file
     */
    FOLDER.addFile = function (file) {
        var added_new = true;
        var $template;

        if (-1 == $.inArray(file.id, FOLDER.files)) {
            $template = $($('#igf-adm-folder-file-template').val());
            $template.attr('data-file-id', file.id);
        } else {
            added_new = false;
            $template = $('[data-file-id="' + file.id + '"]');
        }

        $template.find('.igf-adm-icon img').attr('src', file.icon);
        $template.find('.igf-adm-filename').html(file.filename);
        $template.find('.igf-adm-filename').attr('href', file.url);
        $template.find('.igf-adm-title').html(file.title);
        $template.find('.igf-adm-size').html(file.filesizeHumanReadable);
        var shortcode = $('#igf-adm-file-shortcode-template').val().replace(/"0"/, '"' + file.id + '"');
        $template.find('.igf-adm-shortcode input').val(shortcode).attr('size', shortcode.length);

        if (added_new) {
            $('#igf-adm-folder-files tbody').append($template);

            FOLDER.files.push(file.id);
            FOLDER.refreshFilesList();
        }
    };

    /**
     * Remove file from files list.
     * @param id
     */
    FOLDER.removeFile = function (id) {
        var index = $.inArray(id, FOLDER.files);
        if (-1 !== index) {
            FOLDER.files.splice(index, 1);
        }
        $('[data-file-id="' + id + '"]').remove();

        FOLDER.refreshFilesList();
    };

    $(function () {
        FOLDER.initFiles();

        $('#igf-adm-add-folder-files').on('click', function (e) {
            e.preventDefault();
            FOLDER.openModal(
                0,
                $(this).data('modal-title'),
                $(this).data('modal-button-text')
            );
        });

        $('#igf-adm-folder-files tbody')
            .on('click', 'a.igf-adm-remove', function (e) {
                e.preventDefault();
                if (window.confirm(INFOGALORE_FOLDERS_MEDIA.confirmation_text)) {
                    var $selected = $(this).parents('tr:first');
                    var id = $selected.data('file-id');
                    FOLDER.removeFile(id);
                }
            })
            .on('click', 'a.igf-adm-edit', function () {
                var $selected = $(this).parents('tr:first');
                var id = $selected.data('file-id');
                FOLDER.openModal(id);
            })
            .sortable({
                handle: '.igf-adm-icon img',
                stop: function () {
                    FOLDER.refreshFilesList();
                }
            });
    });
})(window.IG_FOLDERS_MEDIA = window.IG_FOLDERS_MEDIA || {}, jQuery);
