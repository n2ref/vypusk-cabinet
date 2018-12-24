
/**
 * @constructor
 */
function OrdersTeachers() {}


/**
 *
 * @param form
 * @param orderId
 */
OrdersTeachers.addTeachers = function (form, orderId) {

    preloader.show();
    $('button[type=submit]', form).attr('disabled', 'disabled');

    let data = $(form).serialize();

    $.ajax({
        'url': "/data/orders/add/teachers",
        'method': 'post',
        'dataType': 'json',
        'data': data + '&order_id=' + orderId
    }).done(function (result) {
        if (result.status === 'success') {
           load('/?plugin=orders&order_id=' + orderId + '&action=teachers');

        } else {
            let message = result.error_message ? result.error_message : 'Попробуйте повторить попытку позже';
            swal('Ошибка', message, 'error').catch(swal.noop);
        }

    }).fail(function () {
        swal('Ошибка', 'Попробуйте повторить попытку позже', 'error').catch(swal.noop);

    }).always(function () {
        preloader.hide();
        $('button[type=submit]', form).removeAttr('disabled');
    });
};


/**
 *
 * @param form
 * @param orderId
 * @param teacherId
 */
OrdersTeachers.editTeacher = function (form, orderId, teacherId) {

    preloader.show();
    $('button[type=submit]', form).attr('disabled', 'disabled');

    let data = $(form).serialize();

    $.ajax({
        'url': "/data/orders/edit/teacher",
        'method': 'post',
        'dataType': 'json',
        'data': data + '&order_id=' + orderId + '&teacher_id=' + teacherId
    }).done(function (result) {
        if (result.status === 'success') {
           load('/?plugin=orders&order_id=' + orderId + '&action=teachers');

        } else {
            let message = result.error_message ? result.error_message : 'Попробуйте повторить попытку позже';
            swal('Ошибка', message, 'error').catch(swal.noop);
        }

    }).fail(function () {
        swal('Ошибка', 'Попробуйте повторить попытку позже', 'error').catch(swal.noop);

    }).always(function () {
        preloader.hide();
        $('button[type=submit]', form).removeAttr('disabled');
    });
};


/**
 *
 * @param orderId
 * @param teacherId
 */
OrdersTeachers.deleteTeachers = function (orderId, teacherId) {


    swal({
        title: 'Вы уверены?',
        text: 'Это действие удалит выбранного ученика',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: "Удалить",
        cancelButtonText: "Отмена"
    }).then(
        function(result) {

            preloader.show();

            $.ajax({
                'url': "/data/orders/delete/teachers",
                'method': 'post',
                'dataType': 'json',
                'data': {
                    'order_id' : orderId,
                    'teacher_id' : teacherId
                }
            }).done(function (result) {
                if (result.status === 'success') {
                    load('/?plugin=orders&order_id=' + orderId + '&action=teachers');

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