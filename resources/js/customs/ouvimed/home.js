$(document).ready(function() {

    // selectize setores envolvidos
    $("#slSetoresEnvolvidosHome").selectize({
        plugins: ['remove_button'],
        maxItems: null,
        onChange: function(value) {
            tablePrincipal.ajax.reload();
        }
    }); 

    // Abrir painel de filtro clicando em qualquer lugar dele
    $("#filtros").click(function () {
        $(this).closest(".card").CardWidget("toggle");
    });

    // plugin do calendario com datas padrões de 7 dias atras e hoje
    var calendarOuvimed = flatpickr('#startDateOuvimed', {
        enableTime: true,
        time_24hr: true,
        allowInput: true,
        dateFormat: "d/m/Y H:i",
        defaultDate: [new Date().fp_incr(-30), new Date()],
        position: "below center",
        disableMobile: "true",
        locale: ptBr,
        "plugins": [new rangePlugin({ input: "#endDateOuvimed" })],
        onReady(selectedDates, value, datepicker) {
        },
        onOpen(selectedDates, value, datepicker) {
            calendarOuvimed.set('maxDate', false)
            calendarOuvimed.set('minDate', false)
        },
        onChange(selectedDates, value, datepicker) {
            // if (value != '') {
            //     calendarOuvimed.set('maxDate', selectedDates[0].fp_incr(60));
            //     calendarOuvimed.set('minDate', selectedDates[0].fp_incr(-60))
            // }
        }
    });

    // Datatables
    ptBrDataTable.emptyTable = "Nenhuma manifestação encontrada";
    const tablePrincipal = $("#ouvimed-tabela-manifestacoes").DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        language: ptBrDataTable,
        columns: [
            {
                width: "5%",
                class: "text-center",
                data : "PROTOCOLO",
                render(data, type, row) {
                    console.log(type);

                    return "<a href='" + _URL + "/ouvimed/manifestacao/" + row['ID'] + "'>" + data + "</a>";
                }
            },
            {
              width: "20%",
              data: "NOME_AUTOR",
              class: "text-center",
            },
            {
              width: "20%",
              data: "NOME_PACIENTE",
              class: "text-center",
              
            },
            {
              width: "10%",
              data: "REGISTRO_PACIENTE",
              class: "text-center",
              
            },
            {
                width: "20%",
                data: "DTHR_MANIFESTACAO",
                class: "text-center",
                orderable: true,
                render: function (data, type, row) {
                    if (data) {
                        let date = new Date(data);
                        return date.toLocaleString('pt-BR', { timeZone: 'UTC' }).substring(0, 16);
                        
                    }
                    return "";
                }
            },
            {
              width: "15%",
              data: "VEICULO",
              class: "text-center",
            },
            {
                width: "10%",
                data: "STATUS",
                class: "text-center",
                render: function (data, type, row) {
                    if(data == "A") {
                        return "<span class='badge badge-primary'>Aberto</span>";
                    }
                    else if(data == "EA") {
                        return "<span class='badge badge-warning'>Processamento</span>";
                    }
                    else if(data == "F") {
                        return "<span class='badge badge-success'>Finalizado</span>";
                    }
                    else if(data == "C") {
                        return "<span class='badge badge-secondary'>Cancelado</span>";
                    }
                    else {
                        return "";
                    }

                }
            },
        ],
        order: [
            [4, "asc"]
        ],
        serverMethod: "post",
        ajax: {
            url: _URL + "/ouvimed/getManifestacoes",
            type: "POST",
            data: function (obj) {

                // Datas selecionadas
                obj.startDate = $("#startDateOuvimed").val();
                obj.endDate = $("#endDateOuvimed").val();

                // Setores envolvidos selecionados
                obj.setoresEnvolvidos = $("#slSetoresEnvolvidosHome")[0].selectize.getValue();

                // Identicações selecionadas
                obj.identificacoes = [];
                $("input[name='identificacao']:checked").each(function () {
                    obj.identificacoes.push($(this).val());
                });

                // Veiculos selecionados
                obj.veiculos = [];
                $("input[name='veiculo']:checked").each(function () {
                    obj.veiculos.push($(this).val());
                });

                // Status das manifestações selecionados
                obj.status = [];
                $("input[name='status']:checked").each(function () {
                    obj.status.push($(this).val());
                });

            },
        },
        "drawCallback": function (settings) { 
            var response = settings.json;
            console.log(response);
        },
    });

    // update do filtro de tempo
    $("#search-calendar-ouvimed").click(function () {
        tablePrincipal.ajax.reload(null, false);
    });
    

    // make row clickable
    $('#ouvimed-tabela-manifestacoes tbody').on('click', 'tr', function () {
        var data = tablePrincipal.row(this).data();
        window.location.href = _URL + "/ouvimed/manifestacao/" + data['ID'];
    });

    // filtro de identificação da manifestação
    $("input[name='identificacao']").change(function () {
        tablePrincipal.ajax.reload(null, false);
    });

    // filtro de veiculo da manifestação
    $("input[name='veiculo']").change(function () {
        tablePrincipal.ajax.reload(null, false);
    });

    // filtro de status da manifestação
    $("input[name='status']").change(function () {
        tablePrincipal.ajax.reload(null, false);
    });

});