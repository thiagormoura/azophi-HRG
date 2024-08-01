$(document).ready(function() {

    // selectize setores envolvidos
    $("#slSetoresEnvolvidosDashboard").selectize({
        plugins: ['remove_button'],
        maxItems: null,
    }); 

    // Abrir painel de filtro clicando em qualquer lugar dele
    $("#filtrosDashboard").click(function () {
        $(this).closest(".card").CardWidget("toggle");
    });


    $(function() {

        var start = moment().subtract(30, 'days');
        var end = moment();
    
        function cb(start, end) {
            $('#reportrange span').html(start.format('DD/MM/YYYY HH:mm') + '  até  ' + end.format('DD/MM/YYYY HH:mm'));
        }
    
        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            timePicker: true,
            timePicker24Hour: true,
            format: 'DD/MM/YYYY HH:mm',
            showDropdowns: true,
            linkedCalendars: false,
            autoUpdateInput: true,
            ranges: {
                'Hoje': [moment().startOf('day'), moment()],
                'Ontem': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                'Ultimos 7 dias': [moment().subtract(6, 'days'), moment()],
                'Ultimos 30 dias': [moment().subtract(29, 'days'), moment()],
                'Esse mês': [moment().startOf('month'), moment()],
                'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
    
        cb(start, end);
    
    });

    // hide column of charts
    function toggleDataSeries(e) {
        if(typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            e.dataSeries.visible = false;
        }
        else {
            e.dataSeries.visible = true;
        }
        // render chart clicked
        e.chart.render();
    }

    function showDefaultText(chart, text){
 		
        var isEmpty = !(chart.options.data[0].dataPoints && chart.options.data[0].dataPoints.length > 0);
       
        if(!chart.options.subtitles)
            (chart.options.subtitles = []);
        
        if(isEmpty)
            chart.options.subtitles.push({
          text : text,
          verticalAlign : 'center',
        });

        else
            (chart.options.subtitles = []);
    }

    function UpdateCharts(){
        
        // Valores do filtro de tempo
        var start = moment($('#reportrange').data('daterangepicker').startDate);
        var end = moment($('#reportrange').data('daterangepicker').endDate);

        // formatação
        var start_date = start.format('YYYY-MM-DD HH:mm');
        var end_date = end.format('YYYY-MM-DD HH:mm');

        var setores_envolvidos = $("#slSetoresEnvolvidosDashboard")[0].selectize.getValue();

        // get data to charts
        $.ajax({
            url: _URL + "/ouvimed/getDashboardsData",
            type: "POST",
            data:{
                start_date: start_date,
                end_date: end_date,
                setores_envolvidos: setores_envolvidos,
            },
            success: function(data) {

                console.log(data);

                // Criando charts

                // Manifestações totais por status
                var chart1 = new CanvasJS.Chart("divChart1", {

                    theme: "light2",
                    exportEnabled: true,
                    animationEnabled: true,
                    title: {
                        text: "Manifestações totais por status",
                        fontSize: 18,
                        fontFamily: "Arial",
                        fontWeight: "bold"
                    },
                    data: [{
                        type: "pie",
                        startAngle: 25,
                        toolTipContent: "<b>{label}</b>: {y}",
                        showInLegend: "true",
                        legendText: "{label}",
                        indexLabelFontSize: 13,
                        indexLabel: "{label} - {percent}%",
                        dataPoints: data.chart1
                    }]
                });

                // Manifestações por setor e status
                var chart2 = new CanvasJS.Chart("divChart2", {
                    animationEnabled: true,
                    dataPointMaxWidth: 20,
                    title:{
                        text: "Manifestações setor e status",
                        fontSize: 18,
                        fontFamily: "Arial",
                        fontWeight: "bold",
                    },
                    axisX2: {
                        reversed: true,
                        labelFontSize: 11,
                    },
                    axisY: {
                        reversed: true,
                        gridDashType: "dot",
                    },
                    toolTip: {
                        shared: true
                    },
                    legend:{
                        cursor: "pointer",
                        itemclick: toggleDataSeries
                    },
                    data: [{
                        type: "stackedBar",
                        color: "#0366fc",
                        name: "Aberto",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart2.aberto,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#fcba03",
                        name: "Processamento",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart2.processamento,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#069e1d",
                        name: "Finalizado",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart2.finalizado,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#524e4e",
                        name: "Cancelado",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart2.cancelado,
                        axisXType: "secondary"
                        
                    }]
                });

                // Manifestações totais por veiculo
                var chart3 = new CanvasJS.Chart("divChart3", {
                    animationEnabled: true,
                    title:{
                        text: "Manifestações totais por veículo",
                        fontSize: 18,
                        fontFamily: "Arial",
                        fontWeight: "bold",
                    },
                    data: [{
                        type: "doughnut",
                        startAngle: 90,
                        indexLabelFontSize: 12,
                        indexLabel: "{label} - #percent%",
                        toolTipContent: "<b>{label}:</b> {y}",
                        dataPoints: data.chart3
                    }]
                });

                // Manifestações por setor e veículo
                var chart4 = new CanvasJS.Chart("divChart4", {
                    animationEnabled: true,
                    dataPointMaxWidth: 20,
                    title:{
                        text: "Manifestações por setor e veículo",
                        fontSize: 18,
                        fontFamily: "Arial",
                        fontWeight: "bold",
                    },
                    axisX2: {
                        reversed: true,
                        labelFontSize: 11,
                    },
                    axisY: {
                        reversed: true,
                        gridDashType: "dot",
                    },
                    toolTip: {
                        shared: true
                    },
                    legend:{
                        cursor: "pointer",
                        itemclick: toggleDataSeries
                    },
                    data: [{
                        type: "stackedBar",
                        color: "#0366fc",
                        name: "Presencial",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart4.presencial,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#fcba03",
                        name: "Telefone",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart4.telefone,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#069e1d",
                        name: "Caixa Sugestões",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart4.caixa_sugestoes,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#524e4e",
                        name: "Email",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart4.email,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#8403fc",
                        name: "Rede Social",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart4.rede_social,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#fc037b",
                        name: "Outro",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart4.outro,
                        axisXType: "secondary"
                        
                    }]
                });

                // Manifestações totais por identificação
                var chart5 = new CanvasJS.Chart("divChart5", {
                    animationEnabled: true,
                    theme: "light2",
                    title:{
                        text: "Manifestações totais por identificação",
                        fontSize: 18,
                        fontFamily: "Arial",
                        fontWeight: "bold",
                    },
                    axisY: {
                    },
                    data: [{        
                        type: "column",
                        dataPoints: data.chart5
                    }]
                });

                // Manifestações por identificação e setor
                var chart6 = new CanvasJS.Chart("divChart6", {
                    animationEnabled: true,
                    dataPointMaxWidth: 20,
                    title:{
                        text: "Manifestações por setor e identificação",
                        fontSize: 18,
                        fontFamily: "Arial",
                        fontWeight: "bold",
                    },
                    axisX2: {
                        reversed: true,
                        labelFontSize: 11,
                    },
                    axisY: {
                        reversed: true,
                        gridDashType: "dot",
                    },
                    toolTip: {
                        shared: true
                    },
                    legend:{
                        cursor: "pointer",
                        itemclick: toggleDataSeries
                    },
                    data: [{
                        type: "stackedBar",
                        color: "#069e1d",
                        name: "Elogio",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart6.elogio,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#bd1c11",
                        name: "Reclamação",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart6.reclamacao,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#0366fc",
                        name: "Solicitação",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart6.solicitacao,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#8403fc",
                        name: "Informação",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart6.informacao,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#fc037b",
                        name: "Sugestão",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart6.sugestao,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#fcba03",
                        name: "Crítica/Comentário",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart6.critica,
                        axisXType: "secondary"
                        
                    },
                    {
                        type: "stackedBar",
                        color: "#524e4e",
                        name: "Denúncia",
                        showInLegend: "true",
                        yValueFormatString: "#,##0",
                        dataPoints: data.chart6.denuncia,
                        axisXType: "secondary"
                    }]
                });

                // Renderizando charts
                $("#divChart1").css("height", "400px", "width", "100%");
                $("#divChart2").css("height", "400px", "width", "100%");
                $("#divChart3").css("height", "400px", "width", "100%");
                $("#divChart4").css("height", "400px", "width", "100%");
                $("#divChart5").css("height", "400px", "width", "100%");
                $("#divChart6").css("height", "400px", "width", "100%");

                showDefaultText(chart1, "Nenhum dado encontrado");
                showDefaultText(chart2, "Nenhum dado encontrado");
                showDefaultText(chart3, "Nenhum dado encontrado");
                showDefaultText(chart4, "Nenhum dado encontrado");
                showDefaultText(chart5, "Nenhum dado encontrado");
                showDefaultText(chart6, "Nenhum dado encontrado");
                chart1.render();
                chart2.render();
                chart3.render();
                chart4.render();
                chart5.render();
                chart6.render();
                    
            },
            error: function(msg) {
                console.log(msg);
            }
        });

    }

    // trigger update on date range picker change
    $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        UpdateCharts();
    });

    // trigger update on selectize change
    $("#slSetoresEnvolvidosDashboard").on("change", function(){
        UpdateCharts();
    });

    // trigger update on page load
    $(function(){
        UpdateCharts(); 
    });

    

});
