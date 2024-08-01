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

var table = $('#table-perfil').dataTable({
    order: [
        [0, "desc"], [2, "asc"]
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
    }, {
        orderable: true,
        orderDataType: "dom-check",
        targets: [1],
    }, {
        searchable: true,
        orderable: true,
        orderDataType: "dom-text",
        type: 'string',
        targets: [2],
    }]
});

$(document).ready(function () {
    $('#table-perfil').on('change', 'input, select', function () {
        var ctnComida = $(this).closest('tr');
        ctnComida.find('input[type=text]').addClass('changed');
        ctnComida.find('input[type=hidden]').addClass('changed');
        ctnComida.find('select').addClass('changed');
    });

});

$('.form-perfil').on("click", '.btn-add', function (e) {
    e.preventDefault();
    $.ajax({
        url: "createPerfil",
        type: "GET",
        dataType: "html"
    }).done(function (responseText) {
        $('#divPerfil').html(responseText);
        $('#createPerfil').modal('show');
    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    }).always(function () { });
})

$('#divPerfil').on("click", ".btn-create", function (e) {
    var perfilNome = $('#createPerfil').find('#perfil-nome').val();
    var padronizado = +$('#createPerfil').find('#padronizadoCheck').prop('checked');
    var form = $('#createPerfil').find('form');

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
            url: "createPerfil",
            type: "POST",
            data: `nome=${perfilNome}&padronizado=${padronizado}`,
        }).done(function (responseText) {
            if (responseText.success) {
                showError(responseText.message, 'success');
                setTimeout(function () { window.location.reload() }, 1500);
            } else showError(responseText.message);
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    }

});

$('#table-perfil').on("click", '.btn-cancel', function (e) {
    let isEditavel = $(this).closest('tr').data('editavel');
    if (!isEditavel) {
        showError('Desculpe, você não pode alterar esse perfil.');
        return;
    }
    changeButton(this);
})

function savePerfil(inputs) {
    if (!inputs) return;
    $.ajax({
        url: `updatePerfil`,
        type: "POST",
        data: inputs,
    }).done(function (responseText) {
        if (responseText.success)
            showError(responseText.message, 'success');
        else showError(responseText.message);
    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    }).always(function () { });
}

$('#table-perfil').on('click', '.btn-active', function (e) {
    $('input[type=text]:not(.changed), input[type=hidden]:not(.changed)').prop('disabled', true);

    let line = $(this).closest('tr');
    let inputLine = line.find('input').serialize();
    savePerfil(inputLine);
    $(line).find(".form-control").each(function () {
        $(this).prop('disabled', true).removeClass('changed');
    });
    changeButton(this);
})

$('#table-perfil').on("change", '.change-perfil-status', function () {
    let type = $(this).data('definicao');
    let id = $(this).closest('tr').find('input[type=hidden]').val();
    $.ajax({
        url: `updatePerfilCondition/${id}`,
        type: "POST",
        data: `type=${type}`,
    }).done(function (responseText) {
        if (responseText.success) {
            showError(responseText.message, 'success');
        } else showError(responseText.message);
    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    }).always(function () { });
});