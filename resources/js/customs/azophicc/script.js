$(document).ready(() => {

  var calendarAzophiCC = flatpickr('#startDateAzophicc', {
    allowInput: true,
    dateFormat: "d/m/Y",
    position: "below center",
    disableMobile: "true",
    locale: ptBr,
    "plugins": [new rangePlugin({ input: "#endDateAzophicc" })],
    onReady(selectedDates, value, datepicker) {
    },
    onOpen(selectedDates, value, datepicker) {
    },
    onChange(selectedDates, value, datepicker) {
      getConvenios();
    }
  });
  function getDateInput(calendar) {
    let dataInicio = calendar.selectedDates[0] ? removeTime(formatDate(calendar.selectedDates[0]).date) : null;
    let dataFim = calendar.selectedDates[1] ? removeTime(formatDate(calendar.selectedDates[1]).date) : null;
    return [dataInicio, dataFim];
  }

  function getConvenios() {
    let dates = getDateInput(calendarAzophiCC);
    let dataInicio = dates[0];
    let dataFim = dates[1];
    if (dataInicio == null || dataFim == null) return;

    $.ajax({
      url: "azophicc/getConvenios",
      type: "POST",
      data: `firstDate=${dataInicio}&lastDate=${dataFim}`,
      dataType: "html",
    }).done((responseText) => {
      $('#div-select-convenio').html(responseText);
      let selectConvenios = $('#select-convenios').selectize({
        maxItems: null,
        plugins: ["remove_button"],
        delimiter: ",",
        persist: false,
      });
      let selectize = selectConvenios[0].selectize;
      selectize.setValue('', false);
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
    });
  }

  $('#div-select-convenio').on('click', '#search-azophicc', function () {
    let selectValues = $('#select-convenios')[0].selectize.items;
    if (!selectValues.length)
      $('#checkAllConvenios').prop('checked', true).trigger("change");
    getCharts(selectValues);
  })

  $('#div-select-convenio').on('change', '#checkAllConvenios', function () {
    let $selectize = $('#select-convenios').selectize();
    let selectize = $selectize[0].selectize;
    let optKeys = Object.keys(selectize.options);
    if ($(this).is(':checked')) {
      optKeys.forEach(function (key, index) {
        selectize.addItem(key);
      });
    } else {
      optKeys.forEach(function (key, index) {
        selectize.removeItem(key);
      });
    }
  })

  function getCharts(convenios) {
    let dates = getDateInput(calendarAzophiCC);
    let dataInicio = dates[0];
    let dataFim = dates[1];
    if (dataInicio == null || dataFim == null) return;

    $.ajax({
      url: "azophicc/getCamposAzophiCC",
      type: "POST",
      data: `convenios=${convenios}&firstDate=${dataInicio}&lastDate=${dataFim}`,
      dataType: "html",
      async: false
    }).done((responseText) => {
      $('.container-azophicc').html(responseText);
      tabela_cirurgias_agendadas = new TableDataTable('#azophicc-tabela-agendadas');
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
    });

    $.ajax({
      url: "azophicc/getDataPoints",
      type: "POST",
      data: `convenios=${convenios}&firstDate=${dataInicio}&lastDate=${dataFim}`,
    }).done((responseText) => {
      renderCharts(responseText);
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
    });
  }

  function render_procedimento_mes(dataPoints, media) {
    let procedByMonth = new CanvasJS.Chart("procedimentos-mes", {
      animationEnabled: true,
      theme: "light2",
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: "PROCEDIMENTOS POR MÊS",
        fontSize: 18
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        interval: 1,
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      axisY: {
        titleFontFamily: 'Roboto, sans-serif',
        title: "Nº Procedimentos",
        gridDashType: "shortDot",
        gridThickness: 1,
        stripLines: [
          {
            value: media,
            label: `Média - ${media}`,
            labelFontColor: "#9E8355",
            color: "#9E8355"
          }
        ],
      },
      legend: {
        cursor: "pointer",
        fontSize: 16,
      },
      toolTip: {
        shared: true
      },
      data: [{
        name: "Procedimentos",
        type: "spline",
        color: "#525f9b",
        showInLegend: true,
        dataPoints: dataPoints
      }]
    });

    procedByMonth.render();
  }

  function render_melhores_meses(dataPoints) {
    let bestMonths = new CanvasJS.Chart("best-months", {
      animationEnabled: true,
      theme: "light2",
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: "MELHORES MESES DE CIRÚRGIAS",
        fontSize: 18
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        interval: 1,
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      axisY: {
        titleFontFamily: 'Roboto, sans-serif',
        title: "Total de cirúrgias",
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      legend: {
        cursor: "pointer",
        fontSize: 16,
      },
      data: [{
        type: "column",
        showInLegend: true,
        legendMarkerColor: "grey",
        legendText: "Meses",
        dataPoints: dataPoints
      }]
    });
    bestMonths.render();
  }

  function render_salas_utilizadas(dataPoints) {

    let mostRooms = new CanvasJS.Chart("most-rooms", {
      animationEnabled: true,
      theme: "light2",
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: "UTILIZAÇÃO DAS SALAS",
        fontSize: 18
      },
      legend: {
        cursor: "pointer",
        itemclick: explodePie
      },
      data: [{
        type: "doughnut",
        showInLegend: true,
        toolTipContent: "<strong>{label}</strong>: Utilização de <strong>{y}%</strong>",
        indexLabel: "{label} - {y}%",
        startAngle: 60,
        indexLabelFontSize: 16,
        dataPoints: dataPoints
      }]
    });

    mostRooms.render();
  }

  function render_cirurgias_horarios(dataPoints) {
    let interval = 3;

    if ($(window).width() <= 850) {
      interval = 6;
    } else if ($(window).width() <= 1100) {
      interval = 4;
    }

    let cirurgiasHorarios = new CanvasJS.Chart("cirurgias-horarios", {
      animationEnabled: true,
      zoomEnabled: true,
      theme: "light2",
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: "CIRÚRGIAS POR HORÁRIOS",
        fontSize: 18
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        title: "Horários",
        interval: interval,
        gridDashType: "shortDot",
        gridThickness: 1
      },
      axisY: {
        titleFontFamily: 'Roboto, sans-serif',
        title: "Cirúrgias",
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      data: [{
        indexLabelFontColor: "darkSlateGray",
        name: "views",
        type: "area",
        dataPoints: dataPoints
      }]
    });
    cirurgiasHorarios.render();
  }

  function render_procedimento_porte(dataPoints) {
    var procedByPorte = new CanvasJS.Chart("procedimentos-porte", {
      animationEnabled: true,
      theme: "light2",
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: "PROCEDIMENTOS POR PORTES",
        fontSize: 18
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        title: "Porte",
        interval: 1,
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      axisY: {
        titleFontFamily: 'Roboto, sans-serif',
        title: "Procedimentos",
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      data: [{
        type: "column",
        legendText: "Porte",
        dataPoints: dataPoints
      }]
    });
    procedByPorte.render();
  }

  function render_procedimento_convenio(dataPoints) {
    let procedByConvenio = new CanvasJS.Chart("procedimentos-convenio", {
      animationEnabled: true,
      zoomEnabled: true,
      theme: "light2",
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: "PROCEDIMENTOS POR CONVÊNIOS",
        fontSize: 18
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        title: "Convênio",
        interval: 6,
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      axisY: {
        titleFontFamily: 'Roboto, sans-serif',
        title: "Procedimentos",
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      data: [{
        type: "column",
        dataPoints: dataPoints
      }]
    });
    procedByConvenio.render();
  }

  function renderCharts(dataPoints) {
    if (dataPoints.cirurgias_mes)
      render_procedimento_mes(dataPoints.cirurgias_mes, dataPoints.cirurgias_mes_media);
    if (dataPoints.melhores_meses_cirurgia)
      render_melhores_meses(dataPoints.melhores_meses_cirurgia);
    if (dataPoints.salas_mais_utilizadas)
      render_salas_utilizadas(dataPoints.salas_mais_utilizadas);
    if (dataPoints.cirurgias_horarios)
      render_cirurgias_horarios(dataPoints.cirurgias_horarios);
    if (dataPoints.procedimentos_porte)
      render_procedimento_porte(dataPoints.procedimentos_porte);
    if (dataPoints.procedimento_convenio)
      render_procedimento_convenio(dataPoints.procedimento_convenio);
  }
});