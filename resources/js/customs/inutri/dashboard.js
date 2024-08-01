$(document).ready(() => {

  var calendarInutri = flatpickr('#startDateInutri', {
    allowInput: true,
    dateFormat: "d/m/Y",
    defaultDate: [new Date().fp_incr(-15), new Date()],
    position: "below center",
    disableMobile: "true",
    locale: ptBr,
    "plugins": [new rangePlugin({ input: "#endDateInutri" })],
    onReady(selectedDates, value, datepicker) {
    },
    onOpen(selectedDates, value, datepicker) {
      calendarInutri.set('maxDate', false)
      calendarInutri.set('minDate', false)
    },
    onChange(selectedDates, value, datepicker) {
      if (value != '') {
        calendarInutri.set('maxDate', selectedDates[0].fp_incr(120));
        calendarInutri.set('minDate', selectedDates[0].fp_incr(-120))
      }
    }
  });
  getDashboardInutri();

  $('#search-calendar-inutri').click(() => {
    getDashboardInutri();
  })

  function getDashboardInutri() {
    if($('#startDateInutri').length === 0) return;
    let dataInicio = calendarInutri.selectedDates[0] ? formatDate(calendarInutri.selectedDates[0]).date : null;
    let dataFim = calendarInutri.selectedDates[1] ? formatDate(calendarInutri.selectedDates[1]).date : null;
    if (dataInicio == null || dataFim == null) return;

    $.ajax({
      url: "dashboard",
      type: "POST",
      data: "dataInicio=" + dataInicio + "&dataFim=" + dataFim,
      beforeSend: () => {
        $('.loader-div').show();
        $('#container-inutri-dashboard').children().remove();
      }
    }).done((responseText) => {
      $('#container-inutri-dashboard').html(responseText);
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
      $('.loader-div').hide();
    });
  }
});