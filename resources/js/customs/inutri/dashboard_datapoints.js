
var intervalChart = 0;
var pedidosHora;
var pedidosSetores;
var pedidosSemana;


if ($(window).width() <= 290) {
  intervalChart = 3;
} else if ($(window).width() <= 650) {
  intervalChart = 2;
} else {
  intervalChart = 1;
}

function getChartsiNutri() {
  if (!$('#startDateInutri').length) return;
  var dataInicio = formartDateFp($('#startDateInutri').val());
  var dataFim = formartDateFp($('#endDateInutri').val());
  $.ajax({
    url: "getDataPoints",
    type: "POST",
    data: `dataInicio=${dataInicio.fullDate}&dataFim=${dataFim.fullDate}`,
  }).done((responseText) => {
    renderChartsIntrui(responseText);
  }).fail((jqXHR, textStatus) => {
    console.log("Request failed: " + textStatus);
  }).always(() => {
  });
}

function renderChartsIntrui(dataPoints) {
  pedidosSetores = new CanvasJS.Chart("pedidos-setores", {
    animationEnabled: true,
    theme: 'light2',
    title: {
      fontFamily: 'Roboto, sans-serif',
      text: "SOLICITAÇÃO POR SETORES",
      fontSize: 20,
    },
    axisY: {
      titleFontFamily: 'Roboto, sans-serif',
      title: "Solicitações",
      gridDashType: "shortDot",
    },
    axisX: {
      titleFontFamily: 'Roboto, sans-serif',
      gridDashType: "shortDot",
      title: "Setor",
      interval: 1,
      labelAngle: -90,
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
      dataPoints: dataPoints['pedidos-setor']
    }]
  });
  pedidosSetores.render();
  pedidosMes = new CanvasJS.Chart("pedidos-mes", {
    animationEnabled: true,
    theme: "light2",
    title: {
      fontFamily: 'Roboto, sans-serif',
      text: "SOLICITAÇÃO POR MÊS",
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
      title: "Nº Solicitações",
      gridDashType: "shortDot",
      gridThickness: 1,
      stripLines: [
        {
          value: dataPoints['pedidos-mes'].media,
          label: `Média - ${dataPoints['pedidos-mes'].media}`,
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
      name: "Solicitações",
      type: "spline",
      color: "#525f9b",
      showInLegend: true,
      dataPoints: dataPoints['pedidos-mes'].pedidos
    }]
  });
  pedidosMes.render();

  pedidosSemana = new CanvasJS.Chart("pedidos-semana", {
    animationEnabled: true,
    theme: 'light2',
    title: {
      fontFamily: 'Roboto, sans-serif',
      text: "SOLICITAÇÃO POR SEMANA",
      fontSize: 20,
    },
    axisX: {
      titleFontFamily: 'Roboto, sans-serif',
      gridDashType: "shortDot",
      interval: 1
    },
    axisY: {
      gridDashType: "shortDot",
      titleFontFamily: 'Roboto, sans-serif',
      title: "Solicitações",
    },
    legend: {
      cursor: "pointer",
      fontSize: 16,
    },
    toolTip: {
      shared: true
    },
    data: [{
      name: "Solicitações",
      type: "splineArea",
      showInLegend: true,
      dataPoints: dataPoints['pedidos-semana']
    }
    ]
  });
  pedidosSemana.render();

  pedidosHora = new CanvasJS.Chart("pedidos-hora", {
    animationEnabled: true,
    theme: 'light2',
    title: {
      fontFamily: 'Roboto, sans-serif',
      text: "SOLICITAÇÃO POR HORA",
      fontSize: 20,
    },
    axisX: {
      interval: intervalChart,
      titleFontFamily: 'Roboto, sans-serif',
      gridDashType: "shortDot",
    },
    axisY: {
      titleFontFamily: 'Roboto, sans-serif',
      gridDashType: "shortDot",
      title: "Solicitações",
    },
    legend: {
      cursor: "pointer",
      fontSize: 16,
    },
    toolTip: {
      shared: true
    },
    data: [{
      name: "Solicitações",
      type: "spline",
      showInLegend: true,
      dataPoints: dataPoints['pedidos-hora']
    }]
  });
  pedidosHora.render();
}
getChartsiNutri();

$(window).resize(function () {
  if ($(window).width() <= 290) {
    intervalChart = 3;
  } else if ($(window).width() <= 650) {
    intervalChart = 2;
  } else {
    intervalChart = 1;
  }
  pedidosHora.axisX[0].set("interval", intervalChart);
});