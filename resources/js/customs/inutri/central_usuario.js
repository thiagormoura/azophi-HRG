$.fn.dataTable.ext.order['dom-text'] = function (settings, col) {
    return this.api().column(col, {
        order: 'index'
    }).nodes().map(function (td, i) {
        return $('input[type=text]', td).val();
    });
}

$('#table-usuario').dataTable({
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
        searchable: true,
        orderable: true,
        orderDataType: "dom-text",
        type: 'string',
        targets: [0],
        orderData: [0, 1]
    }, {
        searchable: true,
        orderable: true,
        orderDataType: "dom-text",
        type: 'string',
        targets: [1],
        orderData: [1, 0],
    }],
});

$('#table-usuario').on('click', '.btn-edit-usuario', (e) => {
    let idUser = $(e.target).closest('tr').find('#usuario-id').val();
    $.ajax({
        url: `modalUsuarioPerfil/${idUser}`,
        type: "GET",
        dataType: "html"
    }).done(function (responseText) {
        $('.container-modal-usuario').html(responseText);
        $('#perfilUsuario').modal('show');

        let selectUsuarioP = $('#perfil-principal').selectize({
            onInitialize: function () {
                let value = $('#perfil-principal')[0].selectize.getValue();
                hideElements(value);
            },
            onChange: function (value) {
                hideElements(value);
            },
            create: false,
            sortField: "text",
            persist: false,
        });

    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    }).always(function () { });
});

function hideElements(value) {
    let inputs = $('.list-perfil-secondary').find('input[type=checkbox]');
    let lists = $('.list-perfil-secondary');
    $(inputs).each((key, element) => {
        if ($(element).val() == value) {
            $(element).prop('checked', false)
            $(element).closest('.list-perfil-secondary').hide()
            return;
        }
        $(element).closest('.list-perfil-secondary').show()
    });

    bordersInTop(lists[0])
    bordersInBottom(lists[lists.length - 1]);
}

function bordersInTop(element) {
    if ($(element).css('display') == 'none') return bordersInTop($(element).next());
    $(element).css({ "border-top-left-radius": "0.25rem", "border-top-right-radius": "0.25rem", "border-top-width": "1px" });
    return $(element).nextAll().css({ "border-top-left-radius": "0", "border-top-right-radius": "0", "border-top-width": "0" });
}

function bordersInBottom(element) {
    if ($(element).css('display') == 'none') return bordersInBottom($(element).prev());
    $(element).css({ "border-bottom-left-radius": "0.25rem", "border-bottom-right-radius": "0.25rem", "border-bottom-width": "1px" });
    return $(element).prevAll().css({ "border-bottom-left-radius": "0", "border-bottom-right-radius": "0" });
}

$('.container-modal-usuario').on('change', '.check-perfil-usuario', (e) => {
    let checks = $('.check-perfil-usuario');
    $(checks).each((key, element) => {
        let value = $(element).val();
        if ($(element).is(':checked')) {
            $('#perfil-principal')[0].selectize.removeOption(value)
            return;
        }
        let label = $(element).next().text();
        $('#perfil-principal')[0].selectize.addOption({ value: value, text: label });
    });
})

$('.container-modal-usuario').on('click', '.btn-save', (e) => {
    const modal = $(e.target).closest('.modal');
    const formSerialized = modal.find('form').serialize();
    const idUser = modal.data('user');
    $.ajax({
        url: `modalUsuarioPerfil/${idUser}`,
        type: "POST",
        data: formSerialized,
    }).done(function (responseText) {
        if (responseText.success) {
            showError(responseText.message, 'success');
            modal.modal('hide');
            $(`.usuario-content[data-id='${idUser}'`)
                .find('#usuario-perfil').text(responseText.perfil_principal ?? 'Sem perfil');
            return;
        }
        showError(responseText.message);
    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    }).always(function () { });
});