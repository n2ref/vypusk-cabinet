
var profile = {



    /**
     * Сохранение данных профиля
     * @param form
     */
    save: function (form) {

        var is_error = false;

        $('.error', form).text('').hide();
        $('.form-group', form).removeClass('has-error');

        if ( ! $('#input-firstname', form).val()) {
            $('#input-firstname', form).parent().parent().addClass('has-error');
            $('#input-firstname ~ .error-message', form).text('Обязательное поле').show();
            is_error = true;
        }
        if ( ! $('#input-email', form).val()) {
            $('#input-email', form).parent().parent().addClass('has-error');
            $('#input-email ~ .error-message', form).text('Обязательное поле').show();
            is_error = true;

        } else {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            if ( ! re.test($('#input-email', form).val())) {
                $('#input-email', form).parent().parent().addClass('has-error');
                $('#input-email ~ .error-message', form).text('Неверный формат e-mail').show();
                is_error = true;
            }
        }

        if ($('#input-phone', form).val()) {
            var re2 = /^\+7 [\d]{3} [\d]{3} [\d]{4}$/;

            if ( ! re2.test($('#input-phone', form).val())) {
                $('#input-phone', form).parent().parent().addClass('has-error');
                $('#input-phone ~ .error-message', form).text('Неверный номер').show();
                is_error = true;
            }
        }



        if ( ! is_error) {
            var data = $(form).serialize();

            $('button[type=submit]', form)
                .attr('disabled', 'disabled')
                .addClass('in');

            $.ajax({
                'url': "/data/profile/save/profile",
                'method': 'post',
                'dataType': 'json',
                'data': data

            }).done(function (result) {
                if (result.status === 'success') {
                    location.href = '/#plugin=profile';

                    if (result.new_email) {
                        swal('', 'Для смены пароля мы отправили вам сообщение на указанную электорнную почту. Откройте его и перейдите по указанной ссылке.', 'info').catch(swal.noop);
                    }

                } else {
                    if (result.error_messages) {
                        $.each(result.error_messages, function (field, message) {
                            if (field === 'general') {
                                $('.error', form).text(message).show();

                            } else if (field === 'email') {
                                $('#input-email', form).parent().parent().addClass('has-error');
                                $('#input-email ~ .error-message', form).text(message);

                            } else if (field === 'firstname') {
                                $('#input-firstname', form).parent().parent().addClass('has-error');
                                $('#input-firstname ~ .error-message', form).text(message);

                            } else if (field === 'phone') {
                                $('#input-phone', form).parent().parent().addClass('has-error');
                                $('#input-phone ~ .error-message', form).text(message);
                            }
                        })
                    } else {
                        $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();
                    }
                }

            }).fail(function () {
                $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();

            }).always(function () {
                $('button[type=submit]', form)
                    .removeAttr('disabled')
                    .removeClass('in');
            });
        }
    },


    /**
     * Смена пароля
     * @param form
     */
    changePassword: function(form) {

        $('.error, .error-message').text('').hide();
        $('.form-group').removeClass('has-error');

        var is_error = false;
        var pass1    = $('#input-password', form).val();
        var pass2    = $('#input-password-2', form).val();

        if ( ! pass1) {
            $('#input-password').parent().addClass('has-error');
            $('#input-password ~ .error-message').text('Обязательное поле').show();
            is_error = true;
        }
        if ( ! pass2) {
            $('#input-password-2').parent().addClass('has-error');
            $('#input-password-2 ~ .error-message').text('Обязательное поле').show();
            is_error = true;
        }
        if (pass1 && pass2 && pass1 !== pass2) {
            $('[type=password]').parent().addClass('has-error');
            $('[type=password] ~ .error-message', form).text('Пароли не совпадают').show();
            is_error = true;
        }


        if ( ! is_error) {
            $('button[type=submit]', form)
                .attr('disabled', 'disabled')
                .addClass('in');

            $.ajax({
                'url': "/data/profile/change/password",
                'method': 'post',
                'dataType': 'json',
                'data': {
                    'new_password': hex_md5(pass1)
                }
            }).done(function (result) {
                if (result.status === 'success') {
                    swal('Пороль изменен', '', 'success').catch(swal.noop);

                } else {
                    if (result.error_message) {
                        $('.error', form).text(result.error_message).show();
                    } else {
                        $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();
                    }
                }

            }).fail(function () {
                $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();

            }).always(function () {
                $('button[type=submit]', form)
                    .removeAttr('disabled')
                    .removeClass('in');
            });
        }
    },


    /**
     * Отправка сообщения в техподдержку
     * @param form
     */
    sendSupport: function(form) {

        $('.error, .error-message').text('').hide();
        $('.form-group').removeClass('has-error');

        let is_error = false;
        let title    = $('#input-title', form).val();
        let message  = $('#input-message', form).val();

        if ( ! title) {
            $('#input-title').parent().addClass('has-error');
            $('#input-title ~ .error-message').text('Обязательное поле').show();
            is_error = true;
        }
        if ( ! message) {
            $('#input-message').parent().addClass('has-error');
            $('#input-message ~ .error-message').text('Обязательное поле').show();
            is_error = true;
        }


        if ( ! is_error) {
            $('button[type=submit]', form)
                .attr('disabled', 'disabled')
                .addClass('in');

            $.ajax({
                'url': "/data/profile/send/support",
                'method': 'post',
                'dataType': 'json',
                'data': {
                    'title':   title,
                    'message': message
                }
            }).done(function (result) {
                if (result.status === 'success') {
                    swal('Сообщение отправлено!', 'Ваша заявка будет рассмотрена в ближайшее рабочее время. После этого наши специалисты свяжутся с вами', 'success').catch(swal.noop);

                    $('#input-title', form).val('');
                    $('#input-message', form).val('');
                } else {
                    if (result.error_message) {
                        $('.error', form).text(result.error_message).show();
                    } else {
                        $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();
                    }
                }

            }).fail(function () {
                $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();

            }).always(function () {
                $('button[type=submit]', form)
                    .removeAttr('disabled')
                    .removeClass('in');
            });
        }
    },


    /**
     *
     * @param input
     */
    checkControl: function (input) {
        setTimeout(function () {
            if ($(input).val()) {
                $(input).addClass('used');
            } else {
                $(input).removeClass('used');
            }
        }, 0);
    }
};

$(document).ready(function() {
    $('#plugin-content input.form-control').each(function () {
        profile.checkControl(this);
    });
    $('#plugin-content input.form-control').blur(function() {

        profile.checkControl(this);
    });

    if ($('#input-phone')[0]) {
        $("#input-phone").mask("+7 999 999 9999");
    }
});
