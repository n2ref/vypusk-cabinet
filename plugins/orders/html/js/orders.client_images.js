/**
 * @constructor
 */
function OrdersClientImages() {}
OrdersClientImages.isLoad = false;
OrdersClientImages.fileupTemplate =
    '<div id="fileup-[INPUT_ID]-[FILE_NUM]" class="fileup-file [TYPE]" style="background-image: url(\'[PREVIEW_SRC]\');">' +
        '<i class="fileup-remove fa fa-times" onclick="$.fileup(\'[INPUT_ID]\', \'remove\', \'[FILE_NUM]\');" title="[REMOVE]"></i>' +
        '<div class="fileup-container">' +
            '<div class="fileup-description">' +
                '<span class="fileup-name">[NAME]</span>' +
            '</div>' +
            '<div class="fileup-controls">' +
                '<span class="fileup-upload" onclick="$.fileup(\'[INPUT_ID]\', \'upload\', \'[FILE_NUM]\');">[UPLOAD]</span>' +
                '<span class="fileup-abort" onclick="$.fileup(\'[INPUT_ID]\', \'abort\', \'[FILE_NUM]\');" style="display:none">[ABORT]</span>' +
            '</div>' +
            '<div class="fileup-result"></div>' +
            '<div class="fileup-progress">' +
                '<div class="fileup-progress-bar"></div>' +
            '</div>' +
        '</div>' +
        '<div class="fileup-clear"></div>' +
    '</div>';

/**
 * @param form
 */
OrdersClientImages.send = function (form) {

    if (OrdersClientImages.isLoad) {
        return false;
    }

    $('button[type=submit]', form)
        .attr('disabled', 'disabled')
        .addClass('in');

    OrdersClientImages.isLoad = true;

    swal({
        title: 'Сохранение',
        text: 'Это может занять пару минут',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        onOpen: function() {
            swal.showLoading();
        }
    });


    $.ajax({
        'url': "/data/orders/save/client/materials",
        'method': 'post',
        'dataType': 'json',
        'data': $(form).serialize()

    }).done(function(result) {
        if (result.status === 'success') {
            swal({
                title: 'Готово!',
                type: "success"
            }).then(
                function(result) {
                    load('/?plugin=orders&order_id=' + form.order_id.value + '&action=clients_images');

                }, function(dismiss) {
                    load('/?plugin=orders&order_id=' + form.order_id.value + '&action=clients_images');
                }
            );

        } else {
            if (result.error_message) {
                swal('Ошибка', result.error_message, 'error').catch(swal.noop);
            } else {
                swal('Ошибка', 'Попробуйте повторить попытку позже.', 'error').catch(swal.noop);
            }
        }

    }).fail(function() {
        swal('Ошибка', 'Попробуйте повторить попытку позже.', 'error').catch(swal.noop);

    }).always(function() {
        $('button[type=submit]', form)
            .removeAttr('disabled')
            .removeClass('in');

        OrdersClientImages.isLoad = false;
    });
};


/**
 * @param orderId
 * @return {boolean}
 */
OrdersClientImages.sendVerification = function (orderId) {

    swal({
        text: 'Нажимая отправить вы подтверждаете, что все страницы альбома заполнены.',
        type: "question",
        showCancelButton: true,
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: "Да, отправить",
        cancelButtonText: "Отмена"
    }).then(
        function(result) {
            preloader.show();

            $.ajax({
                'url': "/data/orders/verification/client/materials",
                'method': 'post',
                'dataType': 'json',
                'data': {
                    order_id : orderId
                }
            }).done(function(result) {
                preloader.hide();

                if (result.status === 'success') {
                    swal({
                        title: 'Готово!',
                        text: 'Ваши материалы отправлены на проверку. Ожидайте ответа нашего менеджера',
                        type: "success"
                    });

                } else {
                    if (result.error_message) {
                        swal('Ошибка', result.error_message, 'error').catch(swal.noop);
                    } else {
                        swal('Ошибка', 'Попробуйте повторить попытку позже.', 'error').catch(swal.noop);
                    }
                }


            }).fail(function() {
                preloader.hide();
                swal('Ошибка', 'Попробуйте повторить попытку позже.', 'error').catch(swal.noop);
            });

        }, function(dismiss) {}
    );
};


/**
 * @param response
 * @param file_number
 * @param file
 * @param sectionName
 */
OrdersClientImages.onSuccess = function (response, file_number, file, sectionName) {

    try {
        let sectionFilesRaw = $('#upload-files-' + sectionName).val();
        let sectionFiles    = sectionFilesRaw ? JSON.parse(sectionFilesRaw) : [];
        let fileInfo        = JSON.parse(response);

        sectionFiles.push(fileInfo);
        file.tmp_name = fileInfo.tmp_name;

        $('#upload-files-' + sectionName).val(JSON.stringify(sectionFiles));

    } catch (e) {
        // ignore
    }
};


/**
 *
 * @param fileWrapper
 * @param total
 * @param file_number
 * @param sectionName
 */
OrdersClientImages.onRemove = function (fileWrapper, total, file_number, sectionName) {

    try {
        let sectionFilesRaw = $('#upload-files-' + sectionName).val();
        let sectionFiles    = sectionFilesRaw ? JSON.parse(sectionFilesRaw) : [];

        let sectionFilesRemoveRaw = $('#upload-files-remove-' + sectionName).val();
        let sectionFilesRemove    = sectionFilesRemoveRaw ? JSON.parse(sectionFilesRemoveRaw) : [];

        $.each(sectionFiles, function (key, sectionFile) {
            if (fileWrapper.file.id !== undefined) {
                if (sectionFile.id !== undefined && fileWrapper.file.id === sectionFile.id) {
                    sectionFilesRemove.push(sectionFile.id);
                    sectionFiles.splice(key, 1);
                    return false;
                }

            } else if (fileWrapper.file.tmp_name !== undefined) {
                if (sectionFile.tmp_name !== undefined && fileWrapper.file.tmp_name === sectionFile.tmp_name) {
                    sectionFiles.splice(key, 1);
                    return false;
                }
            }
        });

        $('#upload-files-' + sectionName).val(JSON.stringify(sectionFiles));
        $('#upload-files-remove-' + sectionName).val(JSON.stringify(sectionFilesRemove));

    } catch (e) {
        // ignore
    }
};


/**
 * @constructor
 */
function OrdersClientsImages() {}
OrdersClientsImages.mod_src = 'mod/orders/v2.0.0';


/**
 * @param orderId
 * @param pageId
 * @param section
 * @param pageNum
 */
OrdersClientsImages.showEditPage = function(orderId, pageId, section, pageNum) {

    $('#main_body').append(
        '<div class="modal fade" tabindex="-1" id="page-modal">' +
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>' +
        '<h4 class="modal-title">Материалы страницы</h4>' +
        '</div>' +
        '<div class="modal-body">' +
        '<div class="text-center">' +
        '<img src="' + OrdersClientsImages.mod_src + '/html/img/load.gif" alt="loading"/> Загрузка' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>'
    );

    let $modal = $('#page-modal');

    $modal.find('.modal-body').html(
        '<div style="text-align:center">' +
        '<img src="' + OrdersClientsImages.mod_src + '/html/img/load.gif" alt="loading"/> Загрузка</div>'
    ).load('index.php?module=orders&action=album&page=edit_materials_client_page&order_id=' + orderId + '&page_id=' + pageId + '&section=' + section + '&page_num=' + pageNum);
    $modal.modal('show');

    $('#page-modal').on('hidden.bs.modal', function (e) {
        $('#page-modal').remove();
    });
};


/**
 * @param orderId
 * @param pupilId
 * @param pageId
 */
OrdersClientsImages.showEditPupil = function(orderId, pupilId, pageId) {

    $('#main_body').append(
        '<div class="modal fade" tabindex="-1" id="page-modal">' +
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>' +
        '<h4 class="modal-title">Материалы ученика</h4>' +
        '</div>' +
        '<div class="modal-body">' +
        '<div class="text-center">' +
        '<img src="' + OrdersClientsImages.mod_src + '/html/img/load.gif" alt="loading"/> Загрузка' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>'
    );

    let $modal = $('#page-modal');

    $modal.find('.modal-body').load('index.php?module=orders&action=album&page=edit_materials_client_pupil&order_id=' + orderId + '&pupil_id=' + pupilId + '&page_id=' + pageId);
    $modal.modal('show');

    $('#page-modal').on('hidden.bs.modal', function (e) {
        $('#page-modal').remove();
    });
};