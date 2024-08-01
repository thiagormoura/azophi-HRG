
var intervalChart = 0;
var angle_solic_dia = 0;
var solicHora;
var setores_exames;
var solicSemana;

function getCharts() {
  var dataInicio = $('#startDateMonexm').val();
  var dataFim = $('#endDateMonexm').val();

  var dataInicioSplit = dataInicio.split('/');
  var dataFimSplit = dataFim.split('/');

  dataInicio = `${dataInicioSplit[1]}-${dataInicioSplit[0]}-${dataInicioSplit[2]}`;
  dataFim = `${dataFimSplit[1]}-${dataFimSplit[0]}-${dataFimSplit[2]}`;

  $.ajax({
    url: "monexm/getCharts",
    type: "POST",
    data: "dataInicio=" + dataInicio + "&dataFim=" + dataFim,
    dataType: "json",
    beforeSend: () => {
      $('.loader-div2').show();
    }
  }).done((responseText) => {
    renderCharts(JSON.parse(responseText));
  }).fail((jqXHR, textStatus) => {
    console.log("Request failed: " + textStatus);
  }).always(() => {
    $('.loader-div2').hide();
  });
}

function responsiveDayChart(dias){
  if(dias >= 60) angle_solic_dia = 90;
  else if (dias >= 22) angle_solic_dia = 90;
  else if (dias <= 10) angle_solic_dia = 0;
}

function renderCharts(dataPoints) {
  setores_exames = new CanvasJS.Chart("setores-exame", {
    animationEnabled: true,
    theme: 'light2',
    title: {
      fontFamily: 'Roboto, sans-serif',
      text: "SOLICITAÇÕES POR SETORES",
      fontSize: 20,
    },
    axisY: {
      titleFontFamily: 'Roboto, sans-serif',
      title: "Solicitações",
    },
    axisX: {
      titleFontFamily: 'Roboto, sans-serif',
      title: "Setor",
      interval: 1,
      labelAngle: 90,
      labelPlacement: "outsite",
      tickPlacement: "outsite",
      valueFormatString: "####.",
      labelFormatter: function (e) {
        return "";
      }
    },
    toolTip: {
      shared: true
    },
    data: [{
      indexLabelFontColor: "darkSlateGray",
      // color: "rgba(0,75,141,0.7)",
      markerSize: 8,
      type: "column",
      indexLabel: "{y}",
      indexLabelPlacement: "outside",
      indexLabelOrientation: "horizontal",
      name: "Solicitações",
      dataPoints: dataPoints.solicitacao_setor
    }]
  });
  setores_exames.render();

  solicSemana = new CanvasJS.Chart("solic-semana", {
    animationEnabled: true,
    theme: 'light2',
    title: {
      fontFamily: 'Roboto, sans-serif',
      text: "SOLICITAÇÕES POR SEMANA",
      fontSize: 20,
    },
    axisX: {
      titleFontFamily: 'Roboto, sans-serif',
      interval: 1
    },
    axisY: {
      titleFontFamily: 'Roboto, sans-serif',
      title: "Solicitações e Resultados",
    },
    legend: {
      fontSize: 16,
    },
    toolTip: {
      shared: true
    },
    data: [{
      name: "Solicitações",
      type: "splineArea",
      showInLegend: true,
      dataPoints: dataPoints.solicitacao_semana
    },
    {
      name: "Resultado",
      type: "splineArea",
      showInLegend: true,
      dataPoints: dataPoints.resultado_semana
    },
    ]
  });
  solicSemana.render();

  solicHora = new CanvasJS.Chart("solic-hora", {
    animationEnabled: true,
    theme: 'light2',
    title: {
      fontFamily: 'Roboto, sans-serif',
      text: "SOLICITAÇÕES POR DIA",
      fontSize: 20,
    },
    axisX: {
      interval: intervalChart,
      titleFontFamily: 'Roboto, sans-serif',
    },
    axisY: {
      titleFontFamily: 'Roboto, sans-serif',
      title: "Solicitações",
    },
    legend: {
      fontSize: 16,
    },
    toolTip: {
      shared: true
    },
    data: [{
      name: "Solicitações",
      type: "spline",
      showInLegend: true,
      dataPoints: dataPoints.solicitacao_hora
    }]
  });
  solicHora.render();
  
  responsiveDayChart(dataPoints.solicitacao_dia.length);
  solicDia = new CanvasJS.Chart("solic-dia", {
    zoomEnabled: true,
    animationEnabled: true,
    theme: 'light2',
    title: {
      fontFamily: 'Roboto, sans-serif',
      text: "SOLICITAÇÕES POR DIAS",
      fontSize: 20,
    },
    axisX: {
      titleFontFamily: 'Roboto, sans-serif',
      interval: 1,
      labelAngle: angle_solic_dia,
    },
    axisY: {
      titleFontFamily: 'Roboto, sans-serif',
      title: "Solicitações e Resultados",
    },
    legend: {
      fontSize: 16,
    },
    toolTip: {
      shared: true
    },
    // 30 - 5
    // 20 - 
    data: [{
      name: "Solicitações",
      type: "splineArea",
      showInLegend: true,
      dataPoints: dataPoints.solicitacao_dia
    },
    {
      name: "Resultado",
      type: "splineArea",
      showInLegend: true,
      dataPoints: dataPoints.resultado_dia
    },
    ]
  });
  solicDia.render();
  changeInterval();
}
getCharts();

// > 60 > 6
// 30 - 5
// 20 - 2
// < 20 = 1
function changeInterval() {
  let widthScreen = $(window).width();
  if (widthScreen <= 290) {
    intervalChart = 3;
  } else if (widthScreen <= 650) {
    intervalChart = 2;
  } else {
    intervalChart = 1;
  }
  for (var i = 0; i < solicDia.options.data.length; i++) {
    solicDia.options.data[i].indexLabelFontSize = Math.min(36, Math.max(12, $("#solic-dia").width() / 10));
  }
  solicHora.axisX[0].set("interval", intervalChart);
}

$(window).resize(function () {
  changeInterval();
});
