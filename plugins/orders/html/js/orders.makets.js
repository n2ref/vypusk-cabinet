/**
 * @constructor
 */
function OrdersMakets() {}
OrdersMakets.isLoad = false;

/**
 * @param form
 * @param orderId
 * @param sectionId
 * @return {boolean}
 */
OrdersMakets.send = function (form, orderId, sectionId) {

    if (OrdersMakets.isLoad) {
        return false;
    }

    $('button[type=submit]', form)
        .attr('disabled', 'disabled')
        .addClass('in');

    OrdersMakets.isLoad = true;

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
        'url': "/data/orders/save/page/comment",
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
                    load('/?plugin=orders&order_id=' + orderId + '&action=makets&section_id=' + sectionId);

                }, function(dismiss) {
                    load('/?plugin=orders&order_id=' + orderId + '&action=makets&section_id=' + sectionId);
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

        OrdersMakets.isLoad = false;
    });
};


/**
 * @param response
 * @param file_number
 * @param file
 */
OrdersMakets.onSuccess = function (response, file_number, file) {

    try {
        let sectionFilesRaw = $('#upload-files').val();
        let sectionFiles    = sectionFilesRaw ? JSON.parse(sectionFilesRaw) : [];
        let fileInfo        = JSON.parse(response);

        sectionFiles.push(fileInfo);
        file.tmp_name = fileInfo.tmp_name;

        $('#upload-files').val(JSON.stringify(sectionFiles));

    } catch (e) {
        // ignore
    }
};


/**
 *
 * @param fileWrapper
 * @param total
 * @param file_number
 */
OrdersMakets.onRemove = function (fileWrapper, total, file_number) {

    try {
        let sectionFilesRaw = $('#upload-files').val();
        let sectionFiles    = sectionFilesRaw ? JSON.parse(sectionFilesRaw) : [];

        let sectionFilesRemoveRaw = $('#upload-files-remove').val();
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

        $('#upload-files').val(JSON.stringify(sectionFiles));
        $('#upload-files-remove').val(JSON.stringify(sectionFilesRemove));

    } catch (e) {
        // ignore
    }
};


/**
 * @param sectionId
 * @param orderId
 * @param maketId
 */
OrdersMakets.cancelled = function (sectionId, orderId, maketId) {

    swal({
        title: 'Вы уверены?',
        text: 'После этого мы ознакомимся с вашими замечаниями и постараемся их выполнить',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: "Да, уверен!",
        cancelButtonText: "Отмена"
    }).then(
        function(result) {

            preloader.show();

            $.ajax({
                'url': "/data/orders/set/section/status",
                'method': 'post',
                'dataType': 'json',
                'data': {
                    'status' : 'cancelled',
                    'section_id' : sectionId
                }
            }).done(function (result) {
                if (result.status === 'success') {
                    swal({
                        title: 'Готово!',
                        type: "success"
                    }).then(
                        function(result) {
                            load('/?plugin=orders&order_id=' + orderId + '&action=makets&maket_id=' + maketId);

                        }, function(dismiss) {
                            load('/?plugin=orders&order_id=' + orderId + '&action=makets&maket_id=' + maketId);
                        }
                    );

                } else {
                    let message = result.error_message ? result.error_message : 'Попробуйте повторить попытку позже';
                    swal('Ошибка', message, 'error').catch(swal.noop);
                }

            }).fail(function () {
                swal('Ошибка', 'Попробуйте повторить попытку позже', 'error').catch(swal.noop);

            }).always(function () {
                preloader.hide();
            });

        }, function(dismiss) {}
    );
};


/**
 * @param sectionId
 * @param orderId
 * @param maketId
 */
OrdersMakets.accepted = function (sectionId, orderId, maketId) {

    swal({
        title: 'Вы точно все проверили?',
        type: "question",
        showCancelButton: true,
        confirmButtonColor: '#0097e6',
        confirmButtonText: "Да, точно!",
        cancelButtonText: "Отмена"
    }).then(
        function(result) {

            preloader.show();

            $.ajax({
                'url': "/data/orders/set/section/status",
                'method': 'post',
                'dataType': 'json',
                'data': {
                    'status' : 'accepted',
                    'section_id' : sectionId
                }
            }).done(function (result) {
                if (result.status === 'success') {
                    swal({
                        title: 'Готово!',
                        type: "success"
                    }).then(
                        function(result) {
                            load('/?plugin=orders&order_id=' + orderId + '&action=makets&maket_id=' + maketId);

                        }, function(dismiss) {
                            load('/?plugin=orders&order_id=' + orderId + '&action=makets&maket_id=' + maketId);
                        }
                    );

                } else {
                    let message = result.error_message ? result.error_message : 'Попробуйте повторить попытку позже';
                    swal('Ошибка', message, 'error').catch(swal.noop);
                }

            }).fail(function () {
                swal('Ошибка', 'Попробуйте повторить попытку позже', 'error').catch(swal.noop);

            }).always(function () {
                preloader.hide();
            });

        }, function(dismiss) {}
    );
};



$(document).ready(function () {
    lightbox.option({
        'albumLabel': "Страница %1 из %2"
    });
});