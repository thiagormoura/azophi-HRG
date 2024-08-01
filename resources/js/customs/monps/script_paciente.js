$(document).ready(function () {
  $('#monps-modal').on('click', () => {
    $('#modalMonPs').remove();
    $.ajax({
      url: "paciente/monps",
      type: "GET",
      async: false
    }).done((responseText) => {
      if (responseText.success === true) {
        $('#monps-container').html(responseText.modal);
        var modalMonPs = new bootstrap.Modal(document.getElementById('modalMonPs'))
        modalMonPs.show()
        return;
      } else if (responseText.success === false) {
        showError(responseText.message);
        return;
      }
      window.location.href = responseText;
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
    });
  })
});