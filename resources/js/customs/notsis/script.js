$(document).ready(() => {
  var requested = false;
  var calendarNotificacoesNotSis = flatpickr('#DateNotSis', {
    allowInput: true,
    altInput: true,
    altFormat: "j/m/Y",
    dateFormat: "d/m/Y",
    position: "below center",
    disableMobile: true,
    locale: ptBr,
    mode: "range",
    defaultDate: [new Date(), new Date().fp_incr(-6)],
    onOpen(selectedDates, value, datepicker) {
      datepicker.set('maxDate', false);
      datepicker.set('minDate', false);
    },
    onChange(selectedDates, value, datepicker) {
      if (value != '') {
        datepicker.set('maxDate', selectedDates[0].fp_incr(30));
        datepicker.set('minDate', selectedDates[0].fp_incr(-30));
      }
    },
  });
  var selectSIncidente = new SelectSelectize('#select-notsis-sincidente', {
    create: false,
    sortField: "text",
    persist: false,
  });
  var selectSNotificador = new SelectSelectize('#select-notsis-snotificador', {
    create: false,
    sortField: "text",
    persist: false,
  });

  function notsisGetNotificacaoDates(dataInicio, dataFim) {
    $.ajax({
      type: "POST",
      data: `firstDate=${dataInicio}&secondDate=${dataFim}`,
      dataType: "html"
    }).done(function (responseText) {
      $('.row-notificacoes').html(responseText);
    }).fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    }).always(function () { });
  }

  $('#notsis-buscar-incidente').on('click', () => {
    let dataInicio = calendarNotificacoesNotSis.selectedDates[0] ? removeTime(formatDate(calendarNotificacoesNotSis.selectedDates[0]).date) : null;
    let dataFim = calendarNotificacoesNotSis.selectedDates[1] ? removeTime(formatDate(calendarNotificacoesNotSis.selectedDates[1]).date) : null;
    notsisGetNotificacaoDates(dataInicio, dataFim);
  })

  var selectIncidente = $('#select-incidente').selectize({
    maxItems: null,
    plugins: ["remove_button"],
    delimiter: ",",
    persist: false,
    onChange: function (value) {
      let notificacoes = $('.notificacao');
      notificacoes.each((key, element) => {
        if (!value.length) { $(element).show(); return }
        let incidenteInValue = value.indexOf($(element).data('incidente').toString());
        if (incidenteInValue != -1) {
          $(element).show();
          return false;
        }
        $(element).hide();
      });
    }
  });
  var selectForm = $('.select-info-incidente').selectize({
    maxItems: 1,
    delimiter: ",",
    create: true,
    onChange: function (value) {
    }
  });
  if (selectForm.length) {
    var selectizeForm = selectForm[0].selectize;
  }

  if (selectIncidente.length) {
    var selectize = selectIncidente[0].selectize;
    selectize.setValue('', false);
  }

  var dataNotSis = $("#data-notsis").flatpickr({
    dateFormat: "Y-m-d",
    altInput: true,
    maxDate: new Date().fp_incr(0),
    altFormat: "j \\de F \\de Y",
    position: "below center",
    locale: ptBr
  });

  var horarioNotSis = $("#horario-notsis").flatpickr({
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    position: "above left",
    time_24hr: true,
    onOpen: function (selectedDates, dateStr, instance) {
      $('.modal').removeAttr('tabindex');
    },
    onClose: function (selectedDates, dateStr, instance) {
      $('.modal').attr('tabindex', -1);
    }
  });

  $('.btn-submit-sisnot').on('click', (e) => {
    e.preventDefault();
    $('.adicional-text-radio').prop('disabled', true);
    $('.adicional-text').each((key, element) => {
      if ($(element).val() == '') $(element).prop('disabled', true);
    });
    let form = $(document).find('form');
    let serializedForm = $(form).serialize();
    $.ajax({
      type: "POST",
      data: serializedForm,
      beforeSend: () => {
        $('.loader-div').show();
      }
    }).done(function (responseText) {
      if (responseText.success) {
        showError(responseText.message, 'success');
        setTimeout(function () { window.location.href = responseText.redirect; }, 1500);
      }
      else {
        showError(responseText.message);
      }
    }).fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    }).always(function () {
      $('.loader-div').hide();
    });
  })
  $('.btn-next-sisnot').on('click', (e) => {
    e.preventDefault();
    let infoIncidente = $('.info-incidente');
    let form = $(e.target).closest('form');
    $(form).addClass('d-none');
    $(infoIncidente).animate({ display: 'block', left: '0' }, 425);
    $(infoIncidente).removeClass('info-incidente-hide');
  })

  $('.btn-create-notification').on('click', (e) => {
    e.preventDefault();
    let button = e.target;
    let form = $(button).closest('form');
    if (form.valid()) $(form).submit();
  })

  // --------------------------------------------- D A T A P O I N T S ------------------------------------------------------------

  var selectIncidenteDashboard = $('#select-incidente-dashboard').selectize({
    maxItems: null,
    plugins: ["remove_button"],
    delimiter: ",",
    persist: false,
    onChange: function (value) {
    }
  });
  if (selectIncidenteDashboard.length) {
    var selectizeDashBoard = selectIncidenteDashboard[0].selectize;
    selectizeDashBoard.setValue('', false);
  }

  var dashboardCalendarNS = flatpickr('#startDateNotSis-dash', {
    allowInput: true,
    dateFormat: "d/m/Y H:i",
    defaultDate: [new Date().fp_incr(-15), new Date()],
    position: "below center",
    disableMobile: "true",
    locale: ptBr,
    "plugins": [new rangePlugin({ input: "#endDateNotSis-dash" })],
    onReady(selectedDates, value, datepicker) {
    },
    onOpen(selectedDates, value, datepicker) {
      dashboardCalendarNS.set('maxDate', false)
      dashboardCalendarNS.set('minDate', false)
    },
    onChange(selectedDates, value, datepicker) {
      if (value != '') {
        dashboardCalendarNS.set('maxDate', selectedDates[0].fp_incr(60));
        dashboardCalendarNS.set('minDate', selectedDates[0].fp_incr(-60))
      }
    }
  });
  getDashboardNotSis();
  getChartsNotSis();

  $('#search-calendar-notsis').on('click', () => {
    let incidentes = selectIncidenteDashboard[0].selectize.getValue()
    getDashboardNotSis(incidentes);
    getChartsNotSis(incidentes);
  })

  function getDashboardNotSis(incidentes = []) {
    if($('#startDateNotSis-dash').length === 0) return;
    let dataInicio = dashboardCalendarNS.selectedDates[0] ? formatDate(dashboardCalendarNS.selectedDates[0]) : null;
    let dataFim = dashboardCalendarNS.selectedDates[1] ? formatDate(dashboardCalendarNS.selectedDates[1]) : null;
    if (dataInicio == null || dataFim == null) return;

    $.ajax({
      url: "dashboard",
      type: "POST",
      data: `dataInicio=${dataInicio.date}&dataFim=${dataFim.date}&incidentes=${incidentes}`,
      dataType: "html",
      async: false,
      beforeSend: () => {
        $('.loader-div').show();
        $('#container-notsis-dashboard').html();
      }
    }).done((responseText) => {
      $('#container-notsis-dashboard').html(responseText);
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
      $('.loader-div').hide();
    });
  }

  function getChartsNotSis(incidentes = []) {
    if($('#startDateNotSis-dash').length === 0) return;
    var dataInicio = formartDateFp($('#startDateNotSis-dash').val());
    var dataFim = formartDateFp($('#endDateNotSis-dash').val());
    $.ajax({
      url: "getDataPoints",
      type: "POST",
      data: `dataInicio=${dataInicio.fullDate}&dataFim=${dataFim.fullDate}&incidentes=${incidentes}`,
    }).done((responseText) => {
      console.log(responseText);
      renderChartsNotSis(responseText);
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
    });
  }

  function renderChartsNotSis(dataPoints) {
    const CHART_MONTH = {
      title: 'NOTIFICAÇÕES POR MÊS',
      dataName: 'Notificações',
      axisYTitle: 'Nº Notificações',
      axisXTitle: null,
    }
    let chartMonth = new ChartMonth('notificacoes-mes', dataPoints['notificacoes-mes'].data, dataPoints['notificacoes-mes'].media, CHART_MONTH);
    chartMonth.generateChart();

    const CHART_WEEK = {
      title: 'NOTIFICAÇÕES POR SEMANA',
      dataName: 'Notificações',
      axisYTitle: 'Nº Notificações',
      axisXTitle: null,
    }
    let chartWeek = new ChartWeek('notificacoes-semana', dataPoints['notificacoes-semana'], CHART_WEEK);
    chartWeek.generateChart();

    const CHART_SECTOR = {
      title: 'NOTIFICAÇÕES POR SETOR INCIDENTAL',
      dataName: 'Notificações',
      axisYTitle: 'Nº Notificações',
      axisXTitle: 'Setor',
    }
    let chartSector = new ChartColumn('notificacoes-setor', dataPoints['notificacoes-setor'], CHART_SECTOR);
    chartSector.generateChart();

    const CHART_RANKING = {
      title: 'INCIDENTES MAIS OCORRIDOS',
      dataName: 'Incidentes',
      axisYTitle: 'Nº Incidentes',
      axisXTitle: 'Incidente',
      lengend: 'Incidentes',
    }
    let chartRanking = new ChartPie('ranking-incidentes', dataPoints['ranking-incidentes'], CHART_RANKING, 'light2');
    chartRanking.generateChart();

  }
});
