var tablePrincipal = null;
ptBrDataTable.processing = "";

$(document).ready(function () {

    $("#filtros").click(function () {
        $(this).parent().first().CardWidget("toggle");
    });

    var calendarMonExm = flatpickr('#startDateMonexm', {
        enableTime: true,
        time_24hr: true,
        allowInput: true,
        dateFormat: "d/m/Y H:i",
        defaultDate: [new Date().fp_incr(-1), new Date()],
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
                calendarMonExm.set('maxDate', new Date());
                calendarMonExm.set('minDate', selectedDates[0].fp_incr(-60))
            }
        }
    });

    $("#sectors").selectize({
        create: false,
        sortField: "text",
        labelField: "text",
        valueField: "id",
        persist: false,
        maxItems: 1,
    });

    $("#search-calendar-monxem").click({ typeAlteracao: "data" }, triggerGetOS);
    // $("#sectors").change({ typeAlteracao: "data" }, triggerGetOS);
    $("#sectors").change(function(){
        var systemId = $("#sectors").val();
        $.ajax({
            url: "allog/getUsersBySystem",
            type: "POST",
            data: {
                system: systemId
            }
        }).done(response => {
        
            allogUsuariosBySystem.data[0].dataPoints = response.data;

            if(allogUsuariosBySystem.title.text.split(':')[1] == '') allogUsuariosBySystem.title.text += " "+response.nome;
            else{
                allogUsuariosBySystem.title.text = allogUsuariosBySystem.title.text.split(':')[0]+": "+response.nome;
            }
    
            generatedChartUsersBySystem = new CanvasJS.Chart(`allog-users-por-sistema`, allogUsuariosBySystem);
            generatedChartUsersBySystem.render();
            
        }).fail(function(jqXHR, textStatus){
            console.log("Request failed: " + textStatus)
        });
    });

    function triggerGetOS(e) {
        $("#search-calendar-monxem").prop("disabled", true);
        $("#filtros").next().find("input").prop("disabled", true);
        $('#sectors').selectize()[0].selectize.disable();
    }

    const allogAcessoPorSemana = {
        animationEnabled: true,
        title:{
            text: "Intervalo de 7 dias de acesso dos sistemas"
        },
        // axisX: {
        //     valueFormatString: "DDD MM,YY"
        // },
        // axisY: {
        //     title: "Temperature (in °C)",
        //     suffix: " °C"
        // },
        legend:{
            cursor: "pointer",
            fontSize: 16,
            itemclick: toggleDataSeries
        },
        toolTip:{
            shared: true
        },
        data: []
    };

    const allogAcessoTotal = {
        animationEnabled: true,
        title:{
            text: "Acesso Total"
        },
        axisY2:{
            interlacedColor: "rgba(1,77,101,.2)",
            gridColor: "rgba(1,77,101,.1)",
            title: "Numero de acessos",
            interval: 100
        },
        toolTip: {
            shared: true
        },
        data: [{
            type: "bar",
            name: "Acessos",
            axisYType: "secondary",
            color: "#014D65",
            dataPoints: []
        }]
    };

    const allogUsuariosBySystem = {
        theme: "light2",
        animationEnabled: true,
        title: {
            text: "Usuários:",
            horizontalAlign: "center",
            fontSize: 22
        },
        data: [{
            type: "pie",
            showInLegend: false,
            startAngle: 25,
            toolTipContent: "<b>{label}</b>: {y}",
            indexLabelFontSize: 16,
            indexLabel: "{label} - {y}",
            dataPoints: []
        }]
    };

    var generatedChartAcessoPorSemana = "";
    var generatedChartAcessoTotal = "";
    var generatedChartUsersBySystem = "";

    $.ajax({
        url: "allog/getGraphics",
        type: "POST",
        data: {
            system: $("#sectors").val()
        }
    }).done(response => {

        // console.log(response);

        $(response.weekAccess).each(function(){
            $($(this)[0]['dataPoints']).each(function(){
                $(this)[0]["y"] = parseInt($(this)[0]["y"]);

                var day = $(this)[0]["x"].split('-')[2];
                var month = $(this)[0]["x"].split('-')[1];
                var year = $(this)[0]["x"].split('-')[0];

                $(this)[0]["x"] = new Date(year, month-1, day);
            });
        });

        allogAcessoPorSemana.data = response.weekAccess;

        generatedChartAcessoPorSemana = new CanvasJS.Chart(`allog-acesso-por-semana`, allogAcessoPorSemana);
        generatedChartAcessoPorSemana.render();


        $(response.totalAccess).each(function(){
            $(this)[0]["y"] = parseInt($(this)[0]["y"]);
        });
        
        allogAcessoTotal.data[0].dataPoints = response.totalAccess;

        generatedChartAcessoTotal = new CanvasJS.Chart(`allog-acesso-total`, allogAcessoTotal);
        generatedChartAcessoTotal.render();



        allogUsuariosBySystem.data[0].dataPoints = response.usersAccess.data;

        if(allogUsuariosBySystem.title.text.split(':')[1] == '') allogUsuariosBySystem.title.text += " "+response.usersAccess.nome;
        else{
            allogUsuariosBySystem.title.text = allogUsuariosBySystem.title.text.split(':')[0]+": "+response.usersAccess.nome;
        }

        generatedChartUsersBySystem = new CanvasJS.Chart(`allog-users-por-sistema`, allogUsuariosBySystem);
        generatedChartUsersBySystem.render();
        
    }).fail(function(jqXHR, textStatus){
        console.log("Request failed: " + textStatus)
    });
    
    function toggleDataSeries(e){
        if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            e.dataSeries.visible = false;
        }
        else{
            e.dataSeries.visible = true;
        }
        generatedChartAcessoPorSemana.render();
    }

});



function random(numero) {
    return Math.floor(Math.random() * (numero)) + 1;
}