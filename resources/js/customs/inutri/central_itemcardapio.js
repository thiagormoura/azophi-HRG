$(document).ready(() => {

    jQuery.extend(jQuery.validator.messages, {
        required: "Este campo é obrigatório",
    });

    $.fn.dataTableExt.ofnSearch['html-input'] = function (value) {
        return $(value).find('select').find(':selected').text()
    };

    $.fn.dataTable.ext.order['dom-text'] = function (settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function (td, i) {
            return $('input[type=text]', td).val();
        });
    }

    $.fn.dataTable.ext.order['dom-option'] = function (settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function (td, i) {
            return $('select', td).find(":selected").text();
        });
    }

    $.fn.dataTable.ext.order['dom-check'] = function (settings, col) {
        return this.api().column(col, {
            order: 'index'
        }).nodes().map(function (td, i) {
            return +$('input[type=checkbox]', td).prop('checked');
        });
    }

    $('#table-recursos').dataTable({
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
        }, {
            searchable: true,
            orderable: true,
            orderDataType: "dom-option",
            type: 'html-input',
            targets: [2],
            orderData: [2, 0],
        }],

    });

    // Alterações nos itens pedidos
    $('#table-recursos').on('change', 'input, select', function () {
        var ctnComida = $(this).closest('tr');
        ctnComida.find('input[type=text]').addClass('changed');
        ctnComida.find('input[type=hidden]').addClass('changed');
        ctnComida.find('select').addClass('changed');
    });

    $('.form-recursos').on("click", ".btn-submit", function (e) {
        e.preventDefault();
        $('input:not(.changed), select:not(.changed)').prop('disabled', true);
        var inputs = $('.form-recursos').serialize().replace('%5B', '[').replace('%5D', ']');
        updateItemCardapio(inputs);
        $(".form-control").prop('disabled', true);
        $('.btn-active').each((key, element) => {
            changeButton(element)
        });
    })

    $('.form-recursos').on("click", '.btn-add', function (e) {
        e.preventDefault();
        $.ajax({
            url: "createItemCardapio",
            type: "GET",
            dataType: "html"
        }).done(function (responseTxt) {
            $('#divRecurso').append(responseTxt);
            $('#createRecurso').modal('show');
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    })

    $('#divRecurso').on("click", ".btn-create", function (e) {
        var mainContainer = $(this).closest('#createRecurso');
        var form = $(mainContainer).find('form');
        var inputs = $(form).serialize();

        $(form).validate({
            debug: true,
            errorClass: 'error text-danger',
            validClass: 'success',
            errorElement: 'div',
            highlight: function (element, errorClass, validClass) {
                $(element).parents("div.control-group").addClass('error').removeClass('success');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents(".error").removeClass('error').addClass('success');
            }
        });

        if ($(form).valid()) {
            $.ajax({
                url: "createItemCardapio",
                type: "POST",
                data: inputs,
            }).done(function (responseTxt) {
                location.reload();
            }).fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            }).always(function () { });
        }
    });

    $('#table-recursos').on("click", '.btn-cancel', function (e) {
        changeButton(this);
    })

    $('#table-recursos').on('click', '.btn-active', function (e) {
        $('#table-recursos.input[type=text]:not(.changed), #table-recursos.input[type=hidden]:not(.changed), #table-recursos.select:not(.changed)').prop('disabled', true);
        var line = $(this).closest('tr');
        var inputLine = line.find('input, select').serialize();
        updateItemCardapio(inputLine, this);
    });

    function updateItemCardapio(inputsSerialized, button = null) {
        $.ajax({
            url: "updateItemCardapio",
            type: "POST",
            data: inputsSerialized,
        }).done(function (responseText) {
            if (responseText.success) {
                showError(responseText.message, 'success');
                if (button !== null) changeButton(button);
            } else showError(responseText.message);
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    }

    $('#table-recursos').on("change", '.change-status', function () {
        let idComida = $(this).closest('tr').find('input[type=hidden]').val();

        $.ajax({
            url: `updateStatusItem/${idComida}`,
            type: "POST",
        }).done(function (responseText) {
            if (responseText.success) {
                showError(responseText.message, 'success');
            } else showError(responseText.message);
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    });

});
