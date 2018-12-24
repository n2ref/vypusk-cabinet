
var preloader = {

    /**
     *
     */
    show : function() {
        if ( ! $("#preloader")[0]) {
            var $preloader = $('<div id="preloader">' +
                '<div class="lock-screen"></div>' +
                '<div class="block" ><div class="spinner-icon"></div> Загрузка...</div>' +
                '</div>');
            $('body > section').prepend($preloader);

            $preloader.find('.lock-screen').css('opacity', '0').animate({'opacity':'0.2'}, 100);
            $preloader.find('.block').css('opacity', '0').animate({'opacity':'1'}, 100);
        }
    },


    /**
     *
     */
    hide : function() {
        $("#preloader > ").animate({'opacity':'0'}, 100, function() { $("#preloader").remove() });
    }
};


/**
 * @param name
 * @param url
 * @returns {*}
 */
function getParameterByName(name, url) {
    if ( ! url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?#&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if ( ! results) return '';
    if ( ! results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}


alert = function(title, message) {
    swal(title, message).catch(swal.noop);
};


$(document).ready(function() {
    $('footer .copyright .year').text(new Date().getFullYear());

    $(document).click(function() {
        if ($('.coreui-tabs-container > ul .dropdown.open')[0]) {
            $('.coreui-tabs-container > ul .dropdown.open').removeClass('open');
        }
    });

    $('.coreui-tabs-container > ul .dropdown-toggle').click(function() {
        $(this).parent().toggleClass('open');
        return false;
    });
});