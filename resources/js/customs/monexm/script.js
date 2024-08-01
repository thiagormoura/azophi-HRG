$(document).ready(() => {

  var calendarMonExm = flatpickr('#startDateMonexm', {
    enableTime: true,
    time_24hr: true,
    allowInput: true,
    dateFormat: "d/m/Y H:i",
    defaultDate: [new Date().fp_incr(-3), new Date()],
    position: "below center",
    disableMobile: "true",
    locale: ptBr,
    "plugins": [new rangePlugin({ input: "#endDateMonexm" })],
    onReady(selectedDates, value, datepicker) {
    },
    onOpen(selectedDates, value, datepicker) {
      calendarMonExm.set('maxDate', false)
      calendarMonExm.set('minDate', false)
    },
    onChange(selectedDates, value, datepicker) {
      if (value != '') {
        calendarMonExm.set('maxDate', selectedDates[0].fp_incr(60));
        calendarMonExm.set('minDate', selectedDates[0].fp_incr(-60))
      }
    }
  });
  getResultMonExm();

  function formatDate(date) {
    var d = new Date(date),
      month = '' + (d.getMonth() + 1),
      day = '' + d.getDate(),
      year = d.getFullYear(),
      hour = '' + d.getHours(),
      minute = '' + d.getMinutes(),
      seconds = '' + d.getSeconds();

    month = month.length < 2 ? month = '0' + month : month;
    day = day.length < 2 ? day = '0' + day : day;
    hour = hour.length < 2 ? hour = '0' + hour : hour;
    minute = minute.length < 2 ? minute = '0' + minute : minute;
    seconds = seconds.length < 2 ? seconds = '0' + seconds : seconds;

    let time = [hour, minute, seconds].join(':')
    let definedDate = [year, month, day].join('-')
    return [definedDate, time].join(' ');
  }

  $('#search-calendar-monxem').click(() => {
    getResultMonExm();
  })

  $('.container-monexm').on("click", ".setor", (e) => {
    $('#osModal').remove();
    let setor = $(e.target).data('setor');
    if ($('#startDateMonexm').length === 0) return;
    let dataInicio = calendarMonExm.selectedDates[0] ? formatDate(calendarMonExm.selectedDates[0]) : null;
    let dataFim = calendarMonExm.selectedDates[1] ? formatDate(calendarMonExm.selectedDates[1]) : null;

    if (dataInicio == null || dataFim == null) return;

    $.ajax({
      url: `monexm/getOs/${setor}`,
      type: "POST",
      data: "dataInicio=" + dataInicio + "&dataFim=" + dataFim,
      dataType: "html",
      async: false
    }).done((responseText) => {
      console.log(responseText);
      $('.os-modal-container').html(responseText);
      $('#osModal').modal('show');

      $('#table-perfil').dataTable({
        order: [
          [0, "desc"], [2, "asc"]
        ],
        language: {
          "decimal": "",
          "emptyTable": "A tabela está vazia.",
          "info": "Exibindo _END_ de um total de _TOTAL_ elementos",
          "infoEmpty": "Exibindo um total de 0 elementos",
          "infoFiltered": "(Filtrando um total de _MAX_ elementos)",
          "infoPostFix": "",
          "thousands": ",",
          "lengthMenu": "Exibir _MENU_ elementos",
          "loadingRecords": "Carregando...",
          "processing": "Processando...",
          "search": "Pesquisar:",
          "zeroRecords": "Sem resultado...",
          "paginate": {
            "first": "Primeiro",
            "last": "Último",
            "next": "Próximo",
            "previous": "Anterior"
          },
        },
        columnDefs: [{
          orderable: true,
          orderDataType: "dom-check",
          targets: [0],
        }, {
          orderable: true,
          orderDataType: "dom-check",
          targets: [1],
        }, {
          searchable: true,
          orderable: true,
          orderDataType: "dom-text",
          type: 'string',
          targets: [2],
        }]
      });

    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
    });

  });

  function getResultMonExm() {
    if ($('#startDateMonexm').length === 0) return;
    let dataInicio = calendarMonExm.selectedDates[0] ? formatDate(calendarMonExm.selectedDates[0]) : null;
    let dataFim = calendarMonExm.selectedDates[1] ? formatDate(calendarMonExm.selectedDates[1]) : null;
    if (dataInicio == null || dataFim == null) return;

    $.ajax({
      url: "monexm/getExames",
      type: "POST",
      data: "dataInicio=" + dataInicio + "&dataFim=" + dataFim,
      dataType: "html",
      beforeSend: () => {
        $('.loader-div').show();
        $('.container-monexm').html();
      }
    }).done((responseText) => {
      $('.container-monexm').html(responseText);
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
      $('.loader-div').hide();
    });
  }
});