$(document).ready(function () {
    $.fn.dataTable.ext.order['dom-text'] = function (settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function (td, i) {
            return $('input[type=text]', td).val();
        });
    }

    $.fn.dataTable.ext.order['dom-check'] = function (settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function (td, i) {
            return +$('input[type=checkbox]', td).prop('checked');
        });
    }

    jQuery.extend(jQuery.validator.messages, {
        required: "Este campo é obrigatório",
    });

    jQuery.validator.addMethod("checkDates", function (value, element, params) {
        if ($(params[0]).val() == $(params[1]).val()) return false;
        return true;
    }, 'As horas não podem ser iguais.');

    $("#divCardapio").on("hidden.bs.modal", "#createCardapio", function () {
        $(this).remove();
    });

    //Verificar distância de tempo
    $('#divCardapio').on('change', '#horaInicio, #horaLimite', function () {
        var hora1 = $('#horaInicio').val().split(':');
        var hora2 = $('#horaLimite').val().split(':');
        var hourDiff = (parseInt(hora2[0]) > parseInt(hora1[0])) ? Math.abs(parseInt(hora1[0]) - parseInt(hora2[0])) : 24 - Math.abs(parseInt(hora1[0]) - parseInt(hora2[0]));
        if (hora1.length > 1 && hora2.length > 1 && hourDiff <= 1) {
            var alertDistTime = $('<div></div>').addClass('alert alertTime alert-danger py-2').attr('role', 'alert');
            alertDistTime.text('Aviso: Distância de tempo inserida é curta!');
            $("#alertTime").append(alertDistTime);
            if ($('.alertTime').length > 1) {
                $('.alertTime')[0].remove();
            }
            return;
        }
        $('.alertTime').remove();
    });

    //Tabela de cardápios - Datatables
    $('#table-cardapio').dataTable({
        order: [
            [0, "desc"], [1, "asc"]
        ],
        language: {
            "decimal": "",
            "emptyTable": "A tabela está vazia.",
            "info": "Exibindo _END_ de um total de _TOTAL_ elementos",
            "infoEmpty": "Exibindo um total de 0 elementos",
            "infoFiltered": "(Filtrando um total de _MAX_ elementos)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Exibir _MENU_ elementos",
            "loadingRecords": "Carregando...",
            "processing": "Processando...",
            "search": "Pesquisar:",
            "zeroRecords": "Sem resultado...",
            "paginate": {
                "first": "Primeiro",
                "last": "Último",
                "next": "Próximo",
                "previous": "Anterior"
            },
        },
        columnDefs: [{
            orderable: true,
            orderDataType: "dom-check",
            targets: [0],
            orderData: [0, 0]
        }, {
            searchable: true,
            orderable: true,
            orderDataType: "dom-text",
            type: 'string',
            targets: [1],
            orderData: [1, 0]
        }],
    });

    $('#divCardapio').on('change', 'input', function () {
        if ($(this).attr("type") == "checkbox" && !$(this).prop("checked")) return;
        $(this).addClass('changed');
    });

    $('.form-cardapio').on("click", '.btn-add', function (e) {
        e.preventDefault();

        $.ajax({
            url: "createCardapio",
            type: "GET",
            dataType: "html"
        }).done(function (responseText) {
            $('#divCardapio').html(responseText);
            $('#inutri-select-dieta').selectize({
                create: false,
                sortField: "text",
                persist: false
            });
            let modalCardapio = new bootstrap.Modal(document.getElementById('createCardapio'));
            modalCardapio.show();
            createHourFlatpickr('horaLimite');
            createHourFlatpickr('horaInicio');
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    })

    $('#table-cardapio').on("click", '.btn-edit', function (e) {
        var selectedRow = $(this).closest('tr');
        var idCardapio = $(selectedRow).find('input[name="id-cardapio[]"]').val();

        $.ajax({
            url: `updateCardapio/${idCardapio}`,
            type: "GET",
            dataType: "html"
        }).done(function (responseText) {
            $('#divCardapio').html(responseText);
            let selectDieta = $('#inutri-select-dieta').selectize({
                create: false,
                sortField: "text",
                persist: false
            });
            $('#createCardapio').modal('show');
            let limiteTime = $('#createCardapio').find('#horaLimite').val();
            let inicioTime = $('#createCardapio').find('#horaInicio').val();

            createHourFlatpickr('horaLimite', limiteTime);
            createHourFlatpickr('horaInicio', inicioTime);
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    })

    function createHourFlatpickr(id, defaultDate = '') {
        return $(`#${id}`).flatpickr({
            enableTime: true,
            noCalendar: true,
            defaultDate: defaultDate,
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
    }

    $('#table-cardapio').on("change", '.change-status', function () {
        let idCardapio = $(this).closest('tr').find('input[type=hidden]').val();
        $.ajax({
            url: `updateStatusCardapio/${idCardapio}`,
            type: "POST",
        }).done(function (responseText) {
            if (responseText.success) {
                showError(responseText.message, 'success');
            } else showError(responseText.message);
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    });

    function validadorForm() {
        var modal = $('#createCardapio');
        var form = $(modal).find('form');
        var date1 = $(modal).find('#horaLimite')
        var date2 = $(modal).find('#horaInicio')

        $(form).validate({
            debug: true,
            errorClass: 'error text-danger',
            validClass: 'success',
            errorElement: 'div',
            ignore: "#inutri-select-dieta-selectized",
            rules: {
                'perfis[]': {
                    required: true
                },
                'horaInicio': {
                    required: true,
                    checkDates: [date1, date2]
                },
                'horaLimite': {
                    required: true,
                    checkDates: [date1, date2]
                }
            },
            messages: {
                'perfis[]': {
                    required: 'É necessário selecionar pelo menos um perfil.'
                }
            },
            highlight: function (element, errorClass, validClass) {
                if ($(element).attr('type') == 'text') $(element).parents("div.form-group").addClass('error').removeClass('success');
                else if ($(element).attr('type') == 'checkbox') $(element).parents("ul.list-group").addClass('error').removeClass('success');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents(".error").removeClass('error').addClass('success');
            },
            errorPlacement: function (error, element) {
                var placement = $(element).parents(".error");
                if ($(placement).prop('tagName') == 'UL') $(placement).before(error);
                else if ($(placement).prop('tagName') == 'DIV') $(placement).append(error);
            }
        });
        return $(form).valid();
    }

    $('#divCardapio').on("click", ".btn-create", function (e) {
        var buttonCreate = $(this);
        var form = $('#createCardapio').find('form');
        var inputsSerialized = $(form).serialize();
        //Verify all form fields
        if (validadorForm()) {
            $.ajax({
                url: "createCardapio",
                type: "POST",
                data: inputsSerialized,
            }).done(function (responseText) {
                if (responseText.success) {
                    showError(responseText.message, 'success');
                    $(buttonCreate).prop('disabled', true);
                    $('.modal').modal('hide');
                    window.location.reload();
                } else showError(responseText.message);

            }).fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            }).always(function () { });
        }
    });

    $("#divCardapio").on("click", ".btn-save", function () {
        let form = $('#createCardapio').find('form');
        $(".modal input[type='checkbox']:not(:checked)").prop('disabled', true);
        let inputs = $(form).serialize().replace('%5B', '[').replace('%5D', ']');
        let id = $(form).find('#idCardapio').val();

        if (validadorForm()) {
            $.ajax({
                url: `updateCardapio/${id}`,
                type: "POST",
                data: inputs,
            }).done(function (responseText) {
                if (responseText.success) {
                    $(`.cardapio-content[data-id='${id}']`).find('.cardapio-nome').text(responseText.nome_cardapio);
                    showError(responseText.message, 'success');
                    $('.modal').modal('hide');
                } else showError(responseText.message);
            }).fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            }).always(function () { });
        }

        $('.modal input:not(.changed)').prop('disabled', false);
    });

});