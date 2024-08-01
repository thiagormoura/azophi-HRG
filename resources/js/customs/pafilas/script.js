$(document).ready(() => {
    if ($('#pafilas-container').length > 0) {
        setInterval(() => {
            $.ajax({
                url: "pafilas/filas",
                type: "GET",
            }).done(function (responseText) {
                $('#pafilas-container').html(responseText);
            }).fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            }).always(() => {
            });
        }, 60 * 1000); // 1 minuto
    }
});
