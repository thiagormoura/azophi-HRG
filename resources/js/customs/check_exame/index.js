var calendarMonExm = flatpickr('#startDateMonexm', {
    enableTime: true,
    time_24hr: true,
    allowInput: true,
    dateFormat: "d/m/Y H:i",
    defaultDate: [new Date().fp_incr(-7), new Date()],
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
        // if (value != '') {
        //     calendarMonExm.set('maxDate', selectedDates[0].fp_incr(60));
        //     calendarMonExm.set('minDate', selectedDates[0].fp_incr(-60))
        // }
    }
});

var registros = $("#pacientesRegistros").selectize({
    plugins: ["remove_button"],
    create: false,
    sortField: "text",
    persist: false,
    maxItems: null
});

var exames = $("#exames").selectize({
    plugins: ["remove_button"],
    create: false,
    sortField: "text",
    persist: false,
    maxItems: null
});

var tablePrincipal = null;

$(document).ready(function () {
    tablePrincipal = $("#pacientes").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: _URL + "/check_exame/getPacientes",
            type: "POST",
            data: function (obj) {
                // $("#cardOS").append("<div class='overlay'><i class='fas fa-2x fa-sync-alt fa-spin'></i></div>");
                obj.dataInicio = calendarMonExm.selectedDates[0] ? formatDate(calendarMonExm.selectedDates[0]) : null;
                obj.dataFim = calendarMonExm.selectedDates[1] ? formatDate(calendarMonExm.selectedDates[1]) : null;
                obj.exames = ($('#exames').selectize())[0].selectize.items;
                obj.pacientes = ($('#pacientesRegistros').selectize())[0].selectize.items;
                // obj.isOpen = $("#openOS").is(':checked');
                // obj.isExec = $("#execOS").is(':checked');
                // obj.isLib = $("#libOS").is(':checked');
                
                // obj.isCiente = $("#cienteOS").is(':checked');
            }
            , dataSrc: function (e) {
                if(e.draw == 1){
                    $("#pacientesRegistros")[0].selectize.addOption(e.selectRegistros);
                    $("#exames")[0].selectize.addOption(e.selectExames);
                }
                else{
                    if($("#pacientesRegistros")[0].selectize.items.length == 0){
                        $("#pacientesRegistros")[0].selectize.clearOptions();
                        $("#pacientesRegistros")[0].selectize.addOption(e.selectRegistros);
                    }
    
                    if(e.selectExames.length > 0 && $("#exames")[0].selectize.items.length == 0){
                        $("#exames")[0].selectize.clearOptions();
                        $("#exames")[0].selectize.addOption(e.selectExames);
                    }
                }

                return e.data;
            }
        },
        order: [
            [0, "asc"]
        ],
        sDom: "lrtip",
        responsive: true,
        columnDefs: [{
            name: "NOME",
            data: "NOME",
            orderable: true,
            searchable: true,
            orderDataType: "dom-text",
            targets: [0],
            className: "ps-2"
        }, {
            name: "REGISTRO",
            data: "REGISTRO",
            orderable: true,
            searchable: true,
            orderDataType: "dom-text",
            targets: [1]
        }, {
            "className": "dt-center px-1 px-md-0",
            "targets": "_all"
        }],
        language: ptBrDataTable,
        drawCallback: function (settings) {
            $('#pacientes tbody tr').each(function () {
                if ($._data($(this)[0], "events") == undefined)
                    $(this).click(getPacienteExamesModal);
            });        
        }
    });

    function getPacienteExamesModal(e) {
        const registro = $(e.target).parent().children().last().text();

        $.post(_URL + "/check_exame/getPacienteExamesModal", {
            registro: registro,
            dataInicio: calendarMonExm.selectedDates[0] ? formatDate(calendarMonExm.selectedDates[0]) : null,
            dataFim: calendarMonExm.selectedDates[1] ? formatDate(calendarMonExm.selectedDates[1]) : null,
            exames: ($('#exames').selectize())[0].selectize.items
        }, function(data) {

            $("#divModal").append(data);
            $("#pacienteExames").modal("show");

            $("#pacienteExames").on('hidden.bs.modal', function (event) {
                $("#divModal").children().remove();
            });

            $("#examesDoPaciente tbody tr").each(function(){
                const idExame = $(this).children().first().attr('data-id').trim();

                $(this).click(function(){
                    window.open(_URL+"/check_exame/getFile/"+idExame+"/"+registro, '_blank');
                });
            });
        });
    }

    $("#filtros").click(function () {
        $(this).parent().first().CardWidget("toggle");
    });

    // $('#pacientesRegistros, #exames').change(function(){
    //     tablePrincipal.ajax.reload(null,false);
    // });

    $("#searchFilters").click(function(){
        tablePrincipal.ajax.reload(null,false);
    });
});