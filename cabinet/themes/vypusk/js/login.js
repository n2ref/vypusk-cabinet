
var login = {

    /**
     * Авторизация
     * @param form
     */
    auth: function (form) {

        var pass = $('#input-password', form).val();
        $('#input-password').val(hex_md5(pass));

        var data = $(form).serialize();

        $('button[type=submit]', form)
            .attr('disabled', 'disabled')
            .addClass('in');

        $.ajax({
            'url': "index.php?page=login",
            'method': 'post',
            'dataType': 'json',
            'data': data

        }).done(function(result) {
            $('.error', form).text('').hide();
            $('.form-group', form).removeClass('has-error');

            if (result.status === 'success') {
                var back_url = getParameterByName('back_url', document.location.href);
                document.location.href = back_url ? back_url : 'index.php';

            } else {
                if (result.error_messages) {
                    $.each(result.error_messages, function (field, message) {

                        if (field === 'general') {
                            $('.error', form).text(message).show();

                        } else if (field === 'email') {
                            $('#input-email').parent().addClass('has-error');
                            $('#input-email ~ .error-message', form).text(message);

                        } else if (field === 'password') {
                            $('#input-password').parent().addClass('has-error');
                            $('#input-password ~ .error-message', form).text(message);
                        }
                    })
                } else {
                    $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();
                }
            }

        }).fail(function() {
            $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();

        }).always(function() {
            $('button[type=submit]', form)
                .removeAttr('disabled')
                .removeClass('in');

            $('#input-password').val(pass);
        });
    },


    /**
     * Регистрация
     * @param form
     */
    registration: function (form) {

        $('.error').text('').hide();
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
        if ( ! $('#input-firstname', form).val()) {
            $('#input-firstname').parent().addClass('has-error');
            $('#input-firstname ~ .error-message').text('Обязательное поле').show();
            is_error = true;
        }
        if ( ! $('#input-email', form).val()) {
            $('#input-email').parent().addClass('has-error');
            $('#input-email ~ .error-message').text('Обязательное поле').show();
            is_error = true;

        } else {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            if ( ! re.test($('#input-email', form).val())) {
                $('#input-email').parent().addClass('has-error');
                $('#input-email ~ .error-message').text('Неверный формат e-mail').show();
                is_error = true;
            }
        }

        if ($('#input-phone', form).val()) {
            var re2 = /^\+7 [\d]{3} [\d]{3} [\d]{4}$/;

            if ( ! re2.test($('#input-phone', form).val())) {
                $('#input-phone').parent().addClass('has-error');
                $('#input-phone ~ .error-message').text('Неверный номер').show();
                is_error = true;
            }
        }



        if ( ! is_error) {
            $('#input-password').val(hex_md5(pass1));
            $('#input-password-2').val(hex_md5(pass2));

            var data     = $(form).serialize();
            var back_url = getParameterByName('back_url', document.location.href);

            if (back_url) {
                data += '&back_url=' + encodeURIComponent(back_url);
            }

            $('button[type=submit]', form)
                .attr('disabled', 'disabled')
                .addClass('in');

            $.ajax({
                'url': "index.php?page=registration",
                'method': 'post',
                'dataType': 'json',
                'data': data
            }).done(function (result) {
                if (result.status === 'success') {
                    var email   = $('#input-email').val();
                    var message ='Ссылка с подтверждением регистрации отправлена на';

                    if (back_url.indexOf('licenses/order') === 0) {
                        message = 'Ссылка для оплаты отправлена на';

                    } else if (back_url.indexOf('licenses/demo') === 0) {
                        message = 'Ссылка для получения лицензии отправлена на';
                    }

                    $('.success')
                        .html(message + ' <br><span class="text-link">' + email + '</span>').show();
                    $('.form-controls').hide();

                } else {
                    if (result.error_messages) {
                        $.each(result.error_messages, function (field, message) {
                            if (field === 'general') {
                                $('.error', form).text(message).show();

                            } else if (field === 'email') {
                                $('#input-email').parent().addClass('has-error');
                                $('#input-email ~ .error-message').text(message);
                            }
                        })
                    } else {
                        $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();
                    }
                }

            }).fail(function () {
                $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();

            }).always(function () {
                $('#input-password').val(pass1);
                $('#input-password-2').val(pass2);

                $('button[type=submit]', form)
                    .removeAttr('disabled')
                    .removeClass('in');
            });
        }
    },


    /**
     * Восстановление пароля
     * @param form
     */
    forgotPassword: function (form) {

        var data     = $(form).serialize();
        var back_url = getParameterByName('back_url', document.location.href);

        if (back_url) {
            data += '&back_url=' + encodeURIComponent(back_url);
        }

        $('button[type=submit]', form)
            .attr('disabled', 'disabled')
            .addClass('in');

        $.ajax({
            'url': "index.php?page=forgot",
            'method': 'post',
            'dataType': 'json',
            'data': data

        }).done(function(result) {
            $('.error').text('').hide();
            $('.form-group').removeClass('has-error');

            if (result.status === 'success') {
                var email = $('#input-email', form).val();
                $('.success', form).html('Ссылка отправлена на <br><span class="text-link">' + email + '</span>').show();
                $('.form-controls', form).hide();

            } else {
                if (grecaptcha) {
                    grecaptcha.reset();
                }

                if (result.error_messages) {
                    $.each(result.error_messages, function (field, message) {
                        if (field === 'general') {
                            $('.error', form).text(message).show();

                        } else if (field === 'email') {
                            $('#input-email').parent().addClass('has-error');
                            $('#input-email ~ .error-message').text(message);
                        }
                    })
                } else {
                    $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();
                }
            }

        }).fail(function() {
            $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();

        }).always(function() {
            $('button[type=submit]', form)
                .removeAttr('disabled')
                .removeClass('in');
        });
    },


    /**
     * Сброс пароля
     * @param form
     * @param token
     */
    resetpassApprove: function (form, token) {

        var pass1 = $('#input-password', form).val();
        var pass2 = $('#input-password-2', form).val();

        if (pass1 !== pass2) {
            $('[type=password]').parent().addClass('has-error');
            $('[type=password] ~ .error-message', form).text('Пароли не совпадают').show();

        } else {
            $('#input-password').val(hex_md5(pass1));
            $('#input-password-2').val(hex_md5(pass2));

            var data     = $(form).serialize();
            var back_url = getParameterByName('back_url', document.location.href);

            $('button[type=submit]', form)
                .attr('disabled', 'disabled')
                .addClass('in');

            $.ajax({
                'url': "index.php?page=reset&token=" + token,
                'method': 'post',
                'dataType': 'json',
                'data': data

            }).done(function (result) {
                $('.error').text('').hide();
                $('.form-group').removeClass('has-error');

                if (result.status === 'success') {
                    $('.success').html('Пароль изменен. <br> Авторизуйтесь с новым паролем.').show();
                    $('.form-controls').hide();

                    if (back_url) {
                        setTimeout(function () {
                            document.location.href = back_url;
                        }, 2500);
                    }

                } else {
                    if (result.error_messages && result.error_messages.general) {
                        $('.error').text(result.error_messages.general).show();
                    } else {
                        $('.error').text('Ошибка. Попробуйте повторить попытку позже.').show();
                    }
                }

            }).fail(function () {
                $('.error', form).text('Ошибка. Попробуйте повторить попытку позже.').show();

            }).always(function () {
                $('#input-password').val(pass1);
                $('#input-password-2').val(pass2);

                $('button[type=submit]', form)
                    .removeAttr('disabled')
                    .removeClass('in');
            });
        }
    },


    /**
     *
     */
    refocusInputs: function() {
        $('input.form-control').each(function () {
            var input = this;
            setTimeout(function () {
                if ($(input).val()) {
                    $(input).addClass('used');
                } else {
                    $(input).removeClass('used');
                }
            }, 0);
        });
    }
};

$(document).ready(function() {
    $('input.form-control').blur(login.refocusInputs);

    setInterval(login.refocusInputs, 1000);


    if ($('#input-phone')[0]) {
        $("#input-phone").mask("+7 999 999 9999");
    }
});
