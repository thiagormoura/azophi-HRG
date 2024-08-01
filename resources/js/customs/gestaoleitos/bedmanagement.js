
// Busca os leitos da unidade selecionada
$('#gleitos-setores-gerenciador').on('change', function () {
    const unity = $(this).val();

    $.ajax({
        url: `leitos/unidade/${unity}`,
        type: 'GET',
        dataType: "html",
        beforeSend: () => {
            $('.loader-div').show();
        }
    }).done((response) => {
        $('#gleitos-leitos-gerenciador').html(response);
    }).fail((jqXHR, textStatus) => {
        showError('Erro ao carregar leitos. Entre em contato com a equipe de TI pelo Chamado no Milvus.', "error");
    }).always(() => {
        $('.loader-div').hide();
    });
});

$('#gleitos-leitos-gerenciador').on('click', '.gleitos-leito', function () {

    const bedId = $(this).attr("id").replace('gleitos-', '');

    $.ajax({
        url: `leitos/${bedId}`,
        type: 'GET',
        beforeSend: () => {
            $('.loader-div').show();
        }
    }).done((response) => {
        const html = $.parseHTML(response);

        $(html).on('click', '#gleitos-block-bed', function () {
            $.ajax({
                url: `leitos/${bedId}`,
                type: "POST",
                dataType: "json"
            }).done(function (response) {
                if (response) {
                    Swal.fire({
                        title: response.message,
                        icon: "success",
                        confirmButtonText: "Ok"
                    }).then(() => {
                        $(html).modal('hide');
                    });
                }
            }).fail(function (jqXHR, textStatus) {
                Swal.fire({
                    title: "Você não conseguiu Bloquear o leito!",
                    icon: "error",
                    confirmButtonText: "Ok"
                }).then(() => {
                    $(html).modal('hide');
                });
            });
        });

        $(html).on('hidden.bs.modal', function () {
            $(this).remove();
        })

        $(html).modal('show');
    }).fail((jqXHR, textStatus) => {
        console.log(jqXHR)
        console.log(textStatus)
        showError('Erro ao carregar leitos. Entre em contato com a equipe de TI pelo Chamado no Milvus.', "error");
    }).always(() => {
        $('.loader-div').hide();
    });

});

$('#ModalLeito').on('hidden.bs.modal', function () {
    $("#ModalLeitoLabel").empty();
    $("#ModalLeitoBody").empty();
});

/* Menu Lateral */
// Ao clicar em central de vagas
$("#CentralDeVagas, #CentralVagasBC", "#CentralServ").on("click", function () {

    $("#body").loadingModal({
        position: 'auto',
        text: 'Carregando',
        color: '#fff',
        opacity: '0.7',
        backgroudColor: 'rgb(0, 0, 0)',
        animation: 'doubleBounce'
    });

});

// Permissão para solicitar vagas
$("#btSolicitacao").on("click", function () {
    var permissao_solicitar = null;

    $.ajax({
        url: "App/controller.php",
        type: "POST",
        data: { 'func': 'getUserPerm', 'permission': 'permissao_solicitar' },
        dataType: "html"
    }).done(function (resposta) {

        permissao_solicitar = resposta;

        if (permissao_solicitar == 'false') {
            swal({
                title: 'Usuario não tem permissão para criar solicitações',
                icon: "error",
                button: {
                    confirm: true
                }
            });
        } else {
            $("#body").loadingModal({
                position: 'auto',
                text: 'Carregando',
                color: '#fff',
                opacity: '0.7',
                backgroudColor: 'rgb(0, 0, 0)',
                animation: 'doubleBounce'
            });
            window.location.href = "criarSolic.php";
        }

    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
});

// Permissão para transferir paciente
$("#btTransferencia").on("click", function () {

    $("#body").loadingModal({
        position: 'auto',
        text: 'Carregando',
        color: '#fff',
        opacity: '0.7',
        backgroudColor: 'rgb(0, 0, 0)',
        animation: 'doubleBounce'
    });

    var permissao_transferir = null;

    $.ajax({
        url: "App/controller.php",
        type: "POST",
        data: { 'func': 'getUserPerm', 'permission': 'permissao_transferir' },
        dataType: "html"
    }).done(function (resposta) {

        permissao_transferir = resposta;

        if (permissao_transferir == 'false') {
            swal({
                title: "Você não tem permissão para transferir paciente!",
                icon: "error",
                button: {
                    confirm: true
                }
            });
        } else {
            window.location.href = "transferencia_regulacao.php";
        }

    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
});

// Permissão para ir ao painel de leitos
$("#btPainel").on("click", function () {
    var permissao_checklist = null;

    $.ajax({
        url: "App/controller.php",
        type: "POST",
        data: { 'func': 'getUserPerm', 'permission': 'permissao_checklist' },
        dataType: "html"
    }).done(function (resposta) {

        permissao_checklist = resposta;

        if (permissao_checklist == 'false') {
            swal({
                title: "Você não tem permissão para visualizar o painel de leitos!",
                icon: "error",
                button: {
                    confirm: true
                }
            });
        } else {
            window.location.href = "check_leitos.php";
        }

    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });

});

// Permissão para ir a pagina de indicadores
$('#btIndicadores').on("click", function () {

    $("#body").loadingModal({
        position: 'auto',
        text: 'Carregando',
        color: '#fff',
        opacity: '0.7',
        backgroudColor: 'rgb(0, 0, 0)',
        animation: 'doubleBounce'
    });

    var permissao_indicadores = null;

    $.ajax({
        url: "App/controller.php",
        type: "POST",
        data: { 'func': 'getUserPerm', 'permission': 'permissao_indicadores' },
        dataType: "html"
    }).done(function (resposta) {

        permissao_indicadores = resposta;

        if (permissao_indicadores == 'false') {
            swal({
                title: "Você não tem permissão para visualizar a pagina de indicadores!",
                icon: "error",
                button: {
                    confirm: true
                }
            });
        } else {
            window.location.href = "indicadores.php";
        }

    }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
});