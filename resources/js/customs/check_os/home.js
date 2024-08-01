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
                calendarMonExm.set('maxDate', selectedDates[0].fp_incr(60));
                calendarMonExm.set('minDate', selectedDates[0].fp_incr(-60))
            }
        }
    });

    var dataChange = false;

    $("#sectors").selectize({
        plugins: ["remove_button"],
        create: false,
        sortField: "text",
        persist: false,
        maxItems: null
    });

    $("#os_search").selectize({
        plugins: ["remove_button"],
        create: false,
        sortField: "text",
        persist: false,
        maxItems: null
    });

    $("#search-calendar-monxem").click({ typeAlteracao: "data" }, triggerGetOS);
    $("#sectors, #openOS, #cienteOS, #execOS, #libOS").change({ typeAlteracao: "data" }, triggerGetOS);
    $("#os_search").change(triggerGetOS);

    function triggerGetOS(e) {
        $("#search-calendar-monxem").prop("disabled", true);
        $("#filtros").next().find("input").prop("disabled", true);
        $('#sectors').selectize()[0].selectize.disable();
        $('#os_search').selectize()[0].selectize.disable();

        try {
            if (e.data.typeAlteracao == "data")
                dataChange = true;
        }
        catch (e) { }
        finally {
            tablePrincipal.ajax.reload(null, false);
        }
    }

    setInterval(function () {
        if ($(".overlay").length == 0)
            triggerGetOS();
    }, 65000);

    var tablePrincipal = $('#tableOS').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: _URL + "/check_os/ajaxReloadTable",
            type: "POST",
            data: function (obj) {
                $("#cardOS").append("<div class='overlay'><i class='fas fa-2x fa-sync-alt fa-spin'></i></div>");
                obj.dataInicio = calendarMonExm.selectedDates[0] ? formatDate(calendarMonExm.selectedDates[0]) : null;
                obj.dataFim = calendarMonExm.selectedDates[1] ? formatDate(calendarMonExm.selectedDates[1]) : null;
                obj.sectors = ($('#sectors').selectize())[0].selectize.items;
                obj.pacientes = ($('#os_search').selectize())[0].selectize.items;
                obj.isOpen = $("#openOS").is(':checked');
                obj.isExec = $("#execOS").is(':checked');
                obj.isLib = $("#libOS").is(':checked');
                
                obj.isCiente = $("#cienteOS").is(':checked');
            }
            , dataSrc: function (e) {
                // console.log(e.sectors);x

                if (jQuery.isEmptyObject(($('#sectors').selectize())[0].selectize.options) || jQuery.isEmptyObject(($('#sectors').selectize())[0].selectize.items)) {
                    $('#sectors').selectize()[0].selectize.clearOptions();
                    $('#sectors').selectize()[0].selectize.addOption(e.sectors);
                }

                if (
                    (
                        jQuery.isEmptyObject(($('#os_search').selectize())[0].selectize.options) &&
                        jQuery.isEmptyObject(($('#os_search').selectize())[0].selectize.items)
                    )
                    ||
                    (
                        !jQuery.isEmptyObject(($('#os_search').selectize())[0].selectize.options) &&
                        jQuery.isEmptyObject(($('#os_search').selectize())[0].selectize.items)
                    )

                ) {
                    $('#os_search').selectize()[0].selectize.clearOptions();
                    $('#os_search').selectize()[0].selectize.addOption(e.pacientes);
                }

                $(".overlay").remove();

                $("#search-calendar-monxem").prop("disabled", false);
                $("#filtros").next().find("input").prop("disabled", false);
                $('#sectors').selectize()[0].selectize.enable();
                $('#os_search').selectize()[0].selectize.enable();

                return e.data;
            }
        },
        sDom: "lrtip",
        order: [
            [0, "desc"], [1, "desc"]
        ],
        responsive: true,
        columnDefs: [{
            name: "OS_SERIE_NUMERO",
            data: "OS_SERIE_NUMERO",
            orderable: true,
            searchable: true,
            orderDataType: "dom-text",
            targets: [0],
            width: '10%'
        }, {
            name: "LANCAMENTO_PROGRAMADO",
            data: "LANCAMENTO_PROGRAMADO",
            orderable: true,
            searchable: true,
            orderDataType: "dom-text",
            targets: [1],
            width: '16%'
        }, {
            name: "LANCAMENTO",
            data: "LANCAMENTO",
            orderable: true,
            searchable: true,
            orderDataType: "dom-text",
            targets: [2],
            width: '16%'
        },
        {
            name: "SETOR",
            data: "SETOR",
            orderable: true,
            searchable: true,
            orderDataType: "dom-text",
            targets: [3],
            width: '30%'
        }, {
            name: "QTD",
            data: "QTD",
            orderable: true,
            searchable: false,
            orderDataType: "dom-text-numeric",
            targets: [4],
            width: '8%'
        }, {
            name: "STATUS",
            data: "STATUS",
            orderable: true,
            searchable: false,
            orderDataType: "dom-text",
            targets: [5],
            width: '16%'
        }, {
            "className": "dt-center px-1 px-md-0",
            "targets": "_all"
        }],
        language: ptBrDataTable,
        drawCallback: function (settings) {
            $('#tableOS tbody tr').each(function () {
                if ($._data($(this)[0], "events") == undefined)
                    $(this).click(getOSModal);
            });

            $.ajax({
                url: _URL + '/check_os/getLegend',
                type: "GET",
                dataType: "html",
                async: false
            }).done((response) => {

                $("#caption").remove();

                $(response).insertAfter("#tableOS_processing");

            }).fail((jqXHR, textStatus) => {
                console.log("Request failed: " + textStatus);
            });
        }
    });

    function getOSModal(e) {
        var os = $(this).children().first().text();
        var os_serie = (os.split("-")[0]).trim();
        var os_numero = os.split("-").pop().trim();

        $('#divModal').empty();

        $.ajax({
            url: _URL + '/check_os/getOSModal',
            type: "POST",
            data: {
                os_serie: os_serie,
                os_numero: os_numero
            },
            dataType: "html",
            async: false
        }).done((response) => {

            $("#divModal").html(response);
            $("#osModal").modal('show');

            $("#osModal tbody tr").each(function () {
                $(this).click({ os: os, num: ($(this).children()[0]).textContent }, addObs);
            });

            $("#verify").click({ os: os }, verifyOS);

        }).fail((jqXHR, textStatus) => {
            console.log("Request failed: " + textStatus);
        });
    }


    function verifyOS(e) {
        Swal.fire({
            title: "Você tem certeza que quer verificar a OS?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sim",
            cancelButtonText: "Não",
            confirmButtonColor: "#5cb85c"
        }).then((result) => {
            if (result.isConfirmed) {

                let os = e.data.os;
                var os_serie = (os.split("-")[0]).trim();
                var os_numero = os.split("-").pop().trim();

                $("#divModal .modal").modal('hide');
                $('#divModal').empty();

                Swal.fire({
                    title: 'Observação',
                    input: 'textarea',
                    showCancelButton: true,
                    cancelButtonText: "Cancelar",
                    confirmButtonText: "Enviar",
                    confirmButtonColor: "#5cb85c",
                    allowOutsideClick: false
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: _URL + '/check_os/verifyOS',
                            type: "POST",
                            data: {
                                os_serie: os_serie,
                                os_numero: os_numero,
                                obs: result.value
                            },
                            dataType: "json",
                            async: false
                        }).done((response) => {

                            if (response.success) {
                                Swal.fire({
                                    title: response.title,
                                    icon: "success",
                                    confirmButtonText: "Ok"
                                }).then(() => {
                                    tablePrincipal.ajax.reload(null, false);
                                    $("#divModal .modal").modal('hide');
                                    $('#divModal').empty();
                                });
                            }
                            else {
                                Swal.fire({
                                    title: response.title,
                                    icon: "error",
                                    confirmButtonText: "Ok"
                                });
                            }

                        }).fail((jqXHR, textStatus) => {
                            Swal.fire({
                                title: "Algo deu errado...",
                                text: textStatus,
                                icon: "error",
                                confirmButtonText: "Ok"
                            });
                        });
                    }
                    return;
                });
            }
            return;
        });
    }

    function addObs(e) {
        let os = e.data.os;
        var num = e.data.num;
        var os_serie = (os.split("-")[0]).trim();
        var os_numero = os.split("-").pop().trim();

        $("#osModal").modal('hide');

        $.ajax({
            url: _URL + '/check_os/getOSModalExame',
            type: "GET",
            data: {
                os_serie: os_serie,
                os_numero: os_numero,
                os_num_exame: num
            },
            dataType: "html",
            async: false
        }).done((response) => {

            $("#divModalExame").html(response);
            $("#osModalExame").modal('show');

            $("#submitObsExame").click({ os: os, num: num }, submitObsExame);

            $("#osModalExame").on("hidden.bs.modal", function () {
                $("#divModalExame").empty();
                $("#divModal .modal").modal('show');
            });

        }).fail((jqXHR, textStatus) => {
            console.log("Request failed: " + textStatus);
        });
    }

    function submitObsExame(e) {

        let os = e.data.os;
        var num = e.data.num;
        var os_serie = (os.split("-")[0]).trim();
        var os_numero = os.split("-").pop().trim();

        if($("#justificativa").val() == undefined){
            Swal.fire({
                title: "Selecione a justificativa!",
                icon: "error",
                confirmButtonColor: "#bd0000"
            });

            return;
        }

        // Alerta de carregamento
        Swal.fire({
            title: "Enviando observações...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: _URL + '/check_os/submitObsExame',
            type: "POST",
            data: {
                os_serie: os_serie,
                os_numero: os_numero,
                os_num_exame: num,
                observacao: $("#obs").val(),
                justificativa: $("#justificativa").val()
            },
            dataType: "html",
            async: false
        }).done((response) => {

            Swal.close();
            setTimeout(() => {

                atualizacaoModalOS(os_serie, os_numero);

                Swal.fire({
                    title: "Adicionado observação com sucesso.",
                    icon: "success",
                    confirmButtonColor: "#bd0000",
                    didOpen: () => {}
                }).then((result) => {
                    $("#osModalExame").modal('hide');
                });
            }, 200);

        }).fail((jqXHR, textStatus) => {
            Swal.fire({
                title: "Algo deu errado",
                icon: "error",
                confirmButtonColor: "#bd0000"
            });
        });

    }

    function atualizacaoModalOS(os_serie, os_numero) {

        var os = os_serie+" - "+os_numero;

        $('#divModal').empty();

        $.ajax({
            url: _URL + '/check_os/getOSModal',
            type: "POST",
            data: {
                os_serie: os_serie,
                os_numero: os_numero
            },
            dataType: "html",
            async: false
        }).done((response) => {

            $("#divModal").html(response);

            $("#osModal tbody tr").each(function () {
                $(this).click({ os: os, num: ($(this).children()[0]).textContent }, addObs);
            });

            $("#verify").click({ os: os }, verifyOS);

        }).fail((jqXHR, textStatus) => {
            console.log("Request failed: " + textStatus);
        });
    }
});


function random(numero) {
    return Math.floor(Math.random() * (numero)) + 1;
}