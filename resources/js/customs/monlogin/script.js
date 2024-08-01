
$(document).ready(() => {
    $('#search-monlogin').on('click', () => {
        let userLogin = $('#monlogin-user-login').val();

        $.ajax({
            type: "POST",
            data: `userLogin=${userLogin}`,
        }).done(function (responseText) {
            $('#monlogin-user-login-table').html(responseText);
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    })
});