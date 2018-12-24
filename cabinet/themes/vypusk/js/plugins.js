
let isLoadPage = false;

/**
 * Загрузка вкладок плагинов
 * @param url
 * @param callback
 */
var load = function(url, callback) {

    preloader.show();

    url = (url.indexOf('?') !== -1 ? url.replace('#', '&').replace('?', '#') : url);


    let plugin = getParameterByName('plugin', url);

    if ( ! plugin) {
        plugin = $('.plugins .plugin').eq(0)[0] ? $('.plugins .plugin').eq(0).data('name') : '';

        if (plugin) {
            location.href = url + (url.indexOf('#') !== -1 ? '&' : '?') + 'plugin=' + plugin;
        }

        return;
    }


    if (location.hash !== url.replace(/.*(#.*)/, '$1') &&
        location.pathname === url.substr(0, url.indexOf('#'))
    ) {
        location.hash = url.replace(/.*(#.*)/, '$1');

        if (typeof callback === 'function') {
            callback();
        }
    } else {

        $('.plugins .plugin').removeClass('active');

        if ($('.plugin-' + plugin)[0]) {
            $('.plugin-' + plugin).addClass('active');
        }

        if (isLoadPage) {
            return false;
        }

        isLoadPage = true;


        url = url.replace('#', '?');

        $.ajax({
            url: url,
            global: false,
            async: true,
            method: 'GET'
        }).done(function (result) {
            $('#plugin-content').html(result)
                .hide()
                .fadeIn('fast');

            preloader.hide();

            if (typeof callback === 'function') {
                callback();
            }

        }).fail(function (a, b, t) {
            preloader.hide();
            if (a.statusText !== 'abort') {
                if (!a.status) swal("Проверьте соединение с Интернет", 'Превышено время ожидания ответа', 'error').catch(swal.noop);
                else if (a.status === 500) swal("Во время обработки вашего запроса произошла ошибка.", '', 'error').catch(swal.noop);
                else if (a.status === 404) swal("Запрашиваемый ресурс не найден.", '', 'error').catch(swal.noop);
                else if (a.status === 403) swal("Время жизни вашей сессии истекло", 'Чтобы войти в систему заново, обновите страницу (F5)', 'error').catch(swal.noop);
                else swal("Произошла ошибка: " + a.statusText, '', 'error').catch(swal.noop);
            }
        }).always(function () {
            isLoadPage = false;
        });
    }
};


$(document).ready(function() {

    let plugin = getParameterByName('plugin', location.hash);

    if (plugin) {
        load(location.href);

    } else if (location.pathname === '/index.php' || location.pathname === '/') {
         plugin = $('.plugins .plugin').eq(0)[0] ? $('.plugins .plugin').eq(0).data('name') : '';

        if (plugin) {
            load(location.href + (location.href.indexOf('#') !== -1 ? '&' : '?') + 'plugin=' + plugin);
        }

    } else {
        let found = location.pathname.match(/^\/([a-zA-Z0-9_]+)\//);
        if (found[1]) {
            $('.plugins .plugin').removeClass('active');

            if ($('.plugin-' + found[1])[0]) {
                $('.plugin-' + found[1]).addClass('active');
            }
        }
    }


    $.datepicker.setDefaults($.datepicker.regional[ "ru_RU" ]);
});


$(window).on('hashchange', function() {
    load(location.href);
});
