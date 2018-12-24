
/**
 * @constructor
 */
function OrdersDocuments() {}


/**
 *
 * @param orderId
 * @param documentId
 * @param status
 */
OrdersDocuments.sign = function (orderId, documentId, status) {

    swal({
        title: 'Вы уверены?',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: status === 'accepted' ? '#5cb85c' : '#f0ad4e',
        confirmButtonText: "Да",
        cancelButtonText: "Отмена"
    }).then(
        function(result) {

            preloader.show();

            $.ajax({
                'url': "/data/orders/sign/documents",
                'method': 'post',
                'dataType': 'json',
                'data':  {
                    'order_id' : orderId,
                    'document_id' : documentId,
                    'status' : status,
                }
            }).done(function (result) {
                if (result.status === 'success') {
                   load('/?plugin=orders&order_id=' + orderId + '&action=documents');

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
