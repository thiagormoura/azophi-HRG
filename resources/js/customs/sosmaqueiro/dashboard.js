reloadInfos();
setInterval(reloadInfos, 60000);
function reloadInfos(){
    $.ajax({
        url: _URL + `/sosmaqueiro/dashboard`,
        type: "POST",
        dataType: "json",
        async: false
    }).done(function (response) {

        $("#total").html(response.total);
        $("#tempo").html(response.tempo);
        $("#naoAtendidos").html(response.naoAtendidos);
        $("#emAtendimento").html(response.emAtendimento);
        $("#finalizados").html(response.finalizados);
        $("#cancelados").html(response.cancelados);
        $("#ultimaSemana").html(response.ultimaSemana);
        $("#mesAtual").html(response.mesAtual);

        var chart = new CanvasJS.Chart("chartHora", {
            theme: "light2", // "light1", "light2", "dark1", "dark2"
            animationEnabled: true,
            zoomEnabled: true,
            title: {
                text: ""
            },
            axisX: {
                title: "Hora",
                interval: 1
            },
            axisY: {
                title: "Solicitações",
                interval: 20
            },
            toolTip: {
                content:"<strong>{x} horas:</strong> {y} Solicitações"
            },
            data: [{
                type: "area",
                dataPoints: response.chamadosPorHora
            }]
        });
        chart.render();

        var chartDia = new CanvasJS.Chart("chartDiaSemana", {
            theme: "light2", // "light1", "light2", "dark1", "dark2"
            animationEnabled: true,
            zoomEnabled: true,
            title: {
                text: ""
            },
            axisX: {
                title: "Dia da Semana",
                interval: 1
            },
            axisY: {
                title: "Solicitações",
                interval: 100
            },
            toolTip: {
                content:"<strong>{label}:</strong> {y} Solicitações"
            },
            data: [{
                type: "column",
                dataPoints: response.chamadosPorDiaSemana
            }]
        });
        chartDia.render();

    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}