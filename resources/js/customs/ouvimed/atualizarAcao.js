$(document).ready(function () {

    // get id from url
    var url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1);

    // cancelar atualização de ação
    $("#btCancelarAtualizacaoAcao").click(function () {
    
        Swal.fire({
            title: "Cancelar atualização de ação tomada",
            text: "Tem certeza que deseja cancelar a atualização da manifestação?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sim, cancelar atualização!",
            cancelButtonText: "Não, continuar atualização!",
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = _URL + "/ouvimed/manifestacao/" + id;
            }
        });
    
    });

    // confirmar atualização de ação
    $("#btConfirmarAtualizacaoAcao").click(function () {

        let dthr_inputs = [];
        $("input[name='inDateInput']").each(function () {
            // if disabled
            if (!$(this).prop("disabled"))
                dthr_inputs.push($(this).val() + " " + $(this).parent().find("input[name='inHourInput']").val());
        });
        
        let acao_textareas = [];
        $("textarea[name='taAcao']").each(function () {
            if (!$(this).prop("disabled"))
                acao_textareas.push($(this).val());
        });
        
        Swal.fire({
            title: "Atualizar ação tomada",
            text: "Tem certeza que deseja atualizar a manifestação?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sim, atualizar manifestação!",
            cancelButtonText: "Não, continuar atualização!",
            allowOutsideClick: false
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: _URL + "/ouvimed/atualizarAcao/" + id,
                    type: "POST",
                    data:{
                        id_manifestacao: id,
                        acoesTomadas: acao_textareas,
                        dthrAcoesTomadas: dthr_inputs
                    },
                    success: function (data) {
                        if (data[0] == true) {
                            Swal.fire({
                                title: "Manifestação atualizada com sucesso!",
                                text: "A manifestação foi atualizada com sucesso.",
                                icon: "success",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = _URL + "/ouvimed/manifestacao/" + id;
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Erro ao atualizar manifestação!",
                                text: "Ocorreu um erro ao atualizar a manifestação. Por favor, entre em contato com a TI",
                                icon: "error",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function (msg) {
                        Swal.fire({
                            title: "Erro ao atualizar manifestação!",
                            text: "Ocorreu um erro ao atualizar a manifestação. Por favor, entre em contato com a TI. Erro: " + msg,
                            icon: "error",
                            confirmButtonText: "Ok",
                            allowOutsideClick: false
                        });
                    }
                });
                
            }
        });
    
    });

    // adiciona inputs ao clicar no botão de adicionar
    $("#btAddAcao").click(function () {
    
        $.post(
            _URL + "/resources/view/ouvimed/atualizarAcao/newAcaoInputs.html",
            function (data) {

                $("#appendDiv").append(data);

                $("button[name='btnRemoverAcao']").click(function () {
                    $(this).parent().parent().remove(); 
                });

            }
        );
    
    });

    // remove inputs ao clicar no botão de remover
    $("button[name='btnRemoverAcao']").click(function () {
        $(this).parent().parent().remove(); 
    });
});

