jQuery(document).ready(function ($) {
    $('#currency_selector').submit(function (e) {
        e.preventDefault();
        nonce = set_currency_cookie_ajax.ajax_nonce;
        currency = $("#select-currency").val();
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: set_currency_cookie_ajax.ajaxurl,
            data: { action: "set_currency_cookie", currency_selector: currency, nonce: nonce },
            success: function (response) {
                if (response.type == "success") {
                    location.reload(true);
                }
            }
        });
    });
});