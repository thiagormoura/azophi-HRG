const idSolicitacao = location.href.split('/').pop() == "" ? location.href.split('/')[location.href.split('/').length-2] : location.href.split('/').pop();
$(document).ready(() => {
    $("#solicitacao-form").on("submit", function(e){
        e.preventDefault();
    });

    $('#gleitos-prioridade').on('input pagecontainerload', (e) => {
        const priority = {
            '1': 'Muito Baixa',
            '2': 'Baixa',
            '3': 'Média',
            '4': 'Alta',
            '5': 'Muito Alta'
        }

        const priorityInput = $(e.target);
        const priorityVal = priorityInput.val();

        priorityInput.next().text(priority[priorityVal]);
    })

    $('#gleitos-covid-sim, #gleitos-covid-nao').on('change', (e) => {
        const input = $(e.target);
        const value = input.attr('id').replace('gleitos-covid-', '');

        if (value === 'sim') {
            $('#gleitos-covid-container').removeClass('d-none').find('input, textarea').attr('disabled', false);
        } else {
            $('#gleitos-covid-container').addClass('d-none').find('input, textarea').attr('disabled', true);
        }
    })

    $('#gleitos-caract-leitos').change(ignoreIncompatibleBeds);

    function ignoreIncompatibleBeds(e){
        
        $('#gleitos-unidade-internacao option').not('option:first').remove();
        $('#gleitos-unidade-internacao option').prop('selected', true);
        $('#gleitos-leitos option').not('option:first').remove();
        $('#gleitos-leitos option').prop('selected', true);

        const select = $('#gleitos-unidade-internacao');
        const sector = select.val();

        const accommodation = $('input[name="acomodacao-solicitada"]:checked').val();
        const covid = $(`input[name="covid"]:checked`).val();
        const pediatric = $(`input[name="pediatrico"]:checked`).val();
        const gender = $('input[name="genero"]').val();
        const ignoreIncompatible = $('#gleitos-caract-leitos').is(':checked');

        var route = "getAdequateSectors";
        if(ignoreIncompatible) route = "getAllSectorsWithoutDifference";

        $.ajax({
            url: _URL+"/gestaoleitos/"+route,
            type: 'POST',
            data: {
                ignoreIncompatible: +ignoreIncompatible,
                filters: {
                    sector,
                    gender,
                    accommodation,
                    pediatric,
                    covid
                }
            },
            dataType: 'json',
            success: (data) => {
                console.log(data);
                if(data != null){
                    data.map((hospitalBed) => {
                        const option = `<option value="${hospitalBed.STR_COD}">${hospitalBed.STR_NOME}</option>`;
                        $('#gleitos-unidade-internacao').append(option);
                    });
                }
            },
            error: (err) => {
                console.log(err.responseText);
            }
        })
    }

    $("#gleitos-unidade-internacao").change(getBedsWithThisSector);

    function getBedsWithThisSector(e){

        $('#gleitos-leitos option').not('option:first').remove();
        $('#gleitos-leitos option').prop('selected', true);
        const select = $('#gleitos-unidade-internacao');
        const sector = select.val();

        const accommodation = $('input[name="acomodacao-solicitada"]:checked').val();
        const covid = $(`input[name="covid"]:checked`).val();
        const pediatric = $(`input[name="pediatrico"]:checked`).val();
        const gender = $('input[name="genero"]').val();
        const ignoreIncompatible = $('#gleitos-caract-leitos').is(':checked');

        var route = "getAdequateBedsBySector";
        if(ignoreIncompatible) route = "getAllBedsWithoutDifference";

        $.ajax({
            url: _URL+"/gestaoleitos/"+route,
            type: 'POST',
            data: {
                ignoreIncompatible: +ignoreIncompatible,
                filters: {
                    sector,
                    gender,
                    accommodation,
                    pediatric,
                    covid
                }
            },
            dataType: 'json',
            success: (data) => {
                console.log(data);
                data.map((hospitalBed) => {
                    const option = `<option value="${hospitalBed.leito_codigo}">${hospitalBed.leito_nome}</option>`;
                    $('#gleitos-leitos').append(option);
                });
            },
            error: (err) => {
                console.log(err.responseText);
            }
        })
    }

    $('#gleitos-paciente').on('change', (e) => {
        const select = $(e.target);
        const patient = select.val();

        $.ajax({
            url: `solicitacao/paciente/${patient}`,
            type: 'GET',
            dataType: 'html',
            success: (data) => {
                const formGroups = select.closest('#gleitos-fieldset-patient').children();
                formGroups.slice(1).remove();
                select.closest('#gleitos-fieldset-patient').append(data);
            },
            error: (err) => {
                console.log(err);
            }
        });

    })

    $("#gleitos-criar-leito").click(function(e){
        e.preventDefault();
        $(this).empty();
        $(this).prop("disabled", true);
        $(this).append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...');

        // Checar de todos os dados do formulario foram preenchidos
        if(($('#solicitacao-form')[0]).reportValidity()){

            /* Aquisição de dados */ 
                var formCriar = new FormData();

                var patientName = "";
                if($("#gleitos-paciente option:selected").val()) patientName = $("#gleitos-paciente option:selected").text();

                formCriar.append('patientName', patientName);
                formCriar.append('patientGender', $("#gleitos-genero-paciente").val() ? $("#gleitos-genero-paciente").val() : "");
                formCriar.append('patientHealthInsurance', $("#gleitos-convenio-paciente").val() ? $("#gleitos-convenio-paciente").val() : "");
                formCriar.append('patientBirth', $("#gleitos-dtnascimento-paciente").val() ? $("#gleitos-dtnascimento-paciente").val() : "");
                formCriar.append('patientAccommodation', $("input[name='acomodacao-contrato']:checked").val() ? $("input[name='acomodacao-contrato']:checked").val() : "");
                
                // Adquire as precauções do paciente
                var preocu = [];
                $("input[name='gleitos-precaucoes']").each(function(){
                    if($(this).prop("checked"))
                        preocu.push($(this).val());
                });
                formCriar.append('precaucoes[]', preocu);

                // Adquire os riscos do paciente
                var risc = [];
                $("input[name='gleitos-riscos']").each(function(){
                    if($(this).prop("checked"))
                        risc.push($(this).val());
                });
                formCriar.append('riscos[]', risc);

                formCriar.append('patientDiagnosis', $("#gleitos-diagnostico-internacao").val());
                formCriar.append('solicitationProfile', $("input[name='perfil']:checked").val() ? $("input[name='perfil']:checked").val() : "");
                formCriar.append('solicitationAccommodation', $("input[name='acomodacao-solicitada']:checked").val() ? $("input[name='acomodacao-solicitada']:checked").val() : "");
                formCriar.append('solicitationIsolation', $("input[name='isolamento']:checked").val() ? $("input[name='isolamento']:checked").val() : "");
                formCriar.append('solicitationReason', $("#gleitos-motivo-solicitacao").val() ? $("#gleitos-motivo-solicitacao").val() : "");
                formCriar.append('solicitationDoctor', $("#gleitos-medico-assistente").val());
                formCriar.append('solicitationUnit', $("#gleitos-unidades-solicitante").val() ? $("#gleitos-unidades-solicitante").val() : "");
                formCriar.append('solicitationPriority', $("#gleitos-prioridade").val());
                formCriar.append('isCovid', $("input[name='covid']:checked").val() ? $("input[name='covid']:checked").val() : "");

                if($("input[name='covid']:checked").val()){
                    formCriar.append('covidSuspect', $("input[name='covid-suspeito']:checked").val() ? $("input[name='covid-suspeito']:checked").val() : 0);
                    formCriar.append('covidObservation', $("#gleitos-covid-observacao").val());
                }
                else{
                    formCriar.append('covidSuspect', 0);
                    formCriar.append('covidObservation', "");
                }

                formCriar.append('otherInformation', $("#gleitos-informacoes-relevantes").val());
                formCriar.append('patientRegistration', $("#gleitos-paciente").val() ? $("#gleitos-paciente").val() : "");
                formCriar.append('solicitationPediatric', $("input[name='pediatrico']:checked").val() ? $("input[name='pediatrico']:checked").val() : "");

                // Adquire data/hora de admissao
                var adm = null;
                if($("#date-admissao").val() != "" && $("#time-admissao").val() != ""){

                    formCriar.append('solicitationAdmissionDate', ($("#date-admissao").val()+" "+$("#time-admissao").val()).trim());
                    
                    postDataCriarSolic(formCriar);
                    return false;
                }
                else{
                    Swal.fire({
                        title: "A data/hora de admissão não foi informada. Devido a isso será atribuido a data/hora atual. Confirma?",
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonText: "Não",
                        confirmButtonText: "Sim"
                    }).then((value) => {
                        if(!value.isConfirmed){
                            $(this).empty();
                            $(this).prop("disabled", false);
                            $(this).append('Criar solicitação');
                            return;
                        }
                        else{

                            var d = new Date();
                            formCriar.append('solicitationAdmissionDate', d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2) + " " +
                            ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2));

                            postDataCriarSolic(formCriar);
                            return false;
                        }
                    });
                }
        }
    });

    $("#gleitos-cancelar-solicitacao").click(function(e){
        $(this).empty();
        $(".btn").prop("disabled", true);
        $(this).append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...');

        Swal.fire({
            title: "Tem certeza que quer cancelar a solicitação?",
            icon: "warning",
            showCancelButton: true,
            cancelButtonText: "Não",
            confirmButtonText: "Sim",
            confirmButtonColor: "#e01d00"
        }).then((value) => {
            if(!value.isConfirmed){
                $(this).empty();
                $(".btn").prop("disabled", false);
                $(this).append('Cancelar solicitação');
                return;
            }

            $.ajax({ 
                url: _URL+"/gestaoleitos/cancelSolicitation",
                type: "POST",
                data: {
                    id: idSolicitacao
                },
                dataType: "json"
            }).done(function(resposta){

                if(resposta.succeeded){
                    Swal.fire({
                        title: resposta.message,
                        icon: "success",
                        confirmButtonText: "Ok"
                    }).then(() => {
                        location.reload();
                    });
                }
                else{
                    Swal.fire({
                        title: resposta.message,
                        icon: "error",
                        confirmButtonText: "Ok"
                    });
                }

            }).fail(function(jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
        });
    });

    $("#gleitos-preparar-leito").click(function(e){
        $(this).empty();
        $(".btn").prop("disabled", true);
        $(this).append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...');

        e.preventDefault();

        if(($('#solicitacao-form')[0]).reportValidity()){
            const unit = $("#gleitos-unidade-internacao option:selected").val();
            const bed = $("#gleitos-leitos option:selected").val();

            Swal.fire({
                title: "Confirma a preparação do leito?",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: "Não",
                confirmButtonText: "Sim"
            }).then((value) => {
                if(!value.isConfirmed){
                    $(this).empty();
                    $(".btn").prop("disabled", false);
                    $(this).append('Preparar leito');
                    return;
                }
                else gravarLeito("P");
            });
        }
    });

    $("#gleitos-cancelar-preparacao").click(cancelPreparation);

    function cancelPreparation(e){
        $("#gleitos-cancelar-preparacao").empty();
        $(".btn").prop("disabled", true);
        $("#gleitos-cancelar-preparacao").append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...');

        Swal.fire({
            title: "Tem certeza que quer cancelar a preparação do leito?",
            icon: "warning",
            showCancelButton: true,
            cancelButtonText: "Não",
            confirmButtonText: "Sim",
            confirmButtonColor: "#e01d00",
            focusCancel: true
        }).then((value) => {
            if(value.isConfirmed){
                $.ajax({
                    url: _URL+"/gestaoleitos/cancelPreparation",
                    type: "POST",
                    data: {
                        "id": idSolicitacao,
                        "bedCode": $("#gleitos-leitos option:selected").val() 
                    },
                    dataType: "json"                                             
                }).done(function(resposta) {

                    console.log(resposta);
                    if(resposta.succeeded){
                        Swal.fire({
                            title: resposta.title,
                            icon: "success",
                            confirmButtonText: "Ok",
                        }).then(() => {
                            location.reload();
                        });
                    }
                    else{
                        Swal.fire({
                            title: resposta.title,
                            icon: "error",
                            confirmButtonText: "Ok"
                        });
                        $("#gleitos-cancelar-preparacao").empty();
                        $(".btn").prop("disabled", false);
                        $("#gleitos-cancelar-preparacao").append('Cancelar preparação');
                    }
                }).fail(function(jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
            }
            else{
                $("#gleitos-cancelar-preparacao").empty();
                $(".btn").prop("disabled", false);
                $("#gleitos-cancelar-preparacao").append('Cancelar preparação');
            }
        });
    }

    $("#gleitos-reservar-leito").click(confirmReserve);

    function confirmReserve(e){
        $("#gleitos-reservar-leito").empty();
        $(".btn").prop("disabled", true);
        $("#gleitos-reservar-leito").append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...');

        if(($('#solicitacao-form')[0]).reportValidity()){
            const unit = $("#gleitos-unidade-internacao option:selected").val();
            const bed = $("#gleitos-leitos option:selected").val();

            Swal.fire({
                title: "Confirma a liberação do leito?",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: "Não",
                confirmButtonText: "Sim"
            }).then((value) => {
                if(!value.isConfirmed){
                    $("#gleitos-reservar-leito").empty();
                    $(".btn").prop("disabled", false);
                    $("#gleitos-reservar-leito").append('Liberar leito');
                    return;
                }
                else gravarLeito("L");
            });
        }
    }

    $("#gleitos-editar-solicitacao").click(function(e){
        location.href = _URL+"/gestaoleitos/editarSolicitacao/"+idSolicitacao;
    });

    $("#gleitos-cancelar-edicao").click(function(e){
        
        Swal.fire({
            title: "Tem certeza que quer cancelar a edição?",
            text: "Você irá perder sua alterações.",
            icon: "warning",
            showCancelButton: true,
            cancelButtonText: "Não",
            confirmButtonText: "Sim",
            confirmButtonColor: "#e01d00",
            focusCancel: true
        }).then((value) => {
            if(value.isConfirmed)
                location.href = _URL+"/gestaoleitos/solicitacao/"+idSolicitacao;
        });
    });

    $("#gleitos-confirmar-edicao").click(function(e){

        Swal.fire({
            title: "Tem certeza que quer alterar a edição do leito?",
            icon: "warning",
            showCancelButton: true,
            cancelButtonText: "Não",
            confirmButtonText: "Sim",
            confirmButtonColor: "#e01d00",
            focusCancel: true
        }).then((value) => {
            if(value.isConfirmed){

                var formDataEdit = new FormData();
                formDataEdit.append('id', idSolicitacao);
                formDataEdit.append('solicitationProfile', $("input[name='perfil']:checked").val());
                formDataEdit.append('solicitationAccommodation', $("input[name='acomodacao-solicitada']:checked").val());
                formDataEdit.append('solicitationIsolation', $("input[name='isolamento']:checked").val());
                formDataEdit.append('solicitationReason', $("#gleitos-motivo-solicitacao").val());
                formDataEdit.append('solicitationDoctor', $("#gleitos-medico-assistente").val());
                formDataEdit.append('solicitationUnit', $("#gleitos-unidades-solicitante").val());
                formDataEdit.append('solicitationPriority', $("#gleitos-prioridade").val());
                formDataEdit.append('isCovid', parseInt($("input[name='covid']:checked").val()));

                if(parseInt($("input[name='covid']:checked").val())){
                    formDataEdit.append('covidSuspect', $("input[name='covid-suspeito']:checked").val() == undefined ? 0 : $("input[name='covid-suspeito']:checked").val());
                    formDataEdit.append('covidObservation', $("#gleitos-covid-observacao").val());
                }
                else{
                    formDataEdit.append('covidSuspect', 0);
                    formDataEdit.append('covidObservation', "");
                }
                formDataEdit.append('otherInformation', $("#gleitos-informacoes-relevantes").val());
                formDataEdit.append('solicitationPediatric', $("input[name='pediatrico']:checked").val());

                // Adquire data/hora de admissao
                if($("#date-admissao").val() != "" && $("#time-admissao").val() != ""){

                    formDataEdit.append('solicitationAdmissionDate', ($("#date-admissao").val()+" "+$("#time-admissao").val()).trim());
                    
                    postDataEditSolic(formDataEdit);
                    return;
                }
                else{
                    Swal.fire({
                        title: "A data/hora de admissão não foi informada. Devido a isso será atribuido a data/hora atual. Confirma?",
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonText: "Não",
                        confirmButtonText: "Sim",
                        focusConfirm: true
                    }).then((value) => {
                        if(!value.isConfirmed)
                            return;
                        else{

                            var d = new Date();
                            formDataEdit.append('solicitationAdmissionDate', d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2) + " " +
                            ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2));

                            postDataEditSolic(formDataEdit);
                            return;
                        }
                    });
                }
            }
        });
    });

    // Quando o botão de alterar reserva for pressionado
    $("#gleitos-alterar-reserva").click(ChangeReserveCard);
    // $("#gleitos-encerrar-solicitacao").click(ChangeReserveCard);
    

    // $("#gleitos-encerrar-reserva").click(finishReserve);

    function confirmChangeReserve(e){

        $("#gleitos-confirmar-alteracao-reserva").empty();
        $(".btn").prop("disabled", true);
        $("#gleitos-confirmar-alteracao-reserva").append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...');

        const oldBed = e.data.oldBed;
        const oldSector = e.data.oldSector;

        Swal.fire({
            title: "Confirma a alteração da reserva?",
            icon: "warning",
            showCancelButton: true,
            cancelButtonText: "Não",
            confirmButtonText: "Sim",
            confirmButtonColor: "#e01d00"
        }).then((value) => {
            if (value.isConfirmed) {

                $.ajax({
                    url: _URL+"/gestaoleitos/confirmChangeReserve",
                    type: "POST",
                    data: {
                        "id": idSolicitacao,
                        "registro": $("#gleitos-paciente option:selected").val(),
                        "oldSector": oldSector,
                        "oldBed": oldBed,
                        "newSector": $("#gleitos-unidade-internacao option:selected").val(),
                        "newBed": $("#gleitos-leitos option:selected").val() 
                    },
                    dataType: "json"
                }).done(function(resposta) {

                    console.log(resposta);
                    if(resposta.succeeded){
                        Swal.fire({
                            title: resposta.title,
                            icon: "success",
                            confirmButtonText: "Ok",
                        }).then(() => {
                            location.reload();
                        });
                    }
                    else{
                        Swal.fire({
                            title: resposta.title,
                            icon: "error",
                            confirmButtonText: "Ok"
                        });
                        $("#gleitos-confirmar-alteracao-reserva").empty();
                        $(".btn").prop("disabled", false);
                        $("#gleitos-confirmar-alteracao-reserva").append('Confirmar Alteração');
                    }

                }).fail(function(jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
            }
            else{
                $("#gleitos-confirmar-alteracao-reserva").empty();
                $(".btn").prop("disabled", false);
                $("#gleitos-confirmar-alteracao-reserva").append('Confirmar Alteração');
            }
        });
    }

    function ChangeReserveCard(e){

        $("#buttons").append("<div class='overlay'><i class='fas fa-2x fa-sync-alt fa-spin'></i></div>");

        const oldSector = $("#gleitos-unidade-internacao option:selected").val();
        const oldBed = $("#gleitos-leitos option:selected").val();
        
        // Função para verificar se a reserva da solicitação ainda é valida
        $.ajax({
            url: _URL+"/gestaoleitos/vericarReserva",
            type: "POST",
            data: {
                "id": idSolicitacao,
                "registro": $("#gleitos-paciente option:selected").val()
            },
            dataType: "json"
        }).done(function(resposta) {

            if(resposta.succeeded){

                $("#buttons").html(resposta.html);
                $("#gleitos-unidade-internacao").change(getBedsWithThisSector);
                $("#gleitos-cancelar-alteracao-reserva").click(cancelChangeReserve);
                $("#gleitos-confirmar-alteracao-reserva").click({oldSector: oldSector, oldBed: oldBed}, confirmChangeReserve);
                $("#gleitos-encerrar-reserva").click(finishReserve);
                $("#gleitos-caract-leitos").change(ignoreIncompatibleBeds);

            }
            else{
                Swal.fire({
                    title: resposta.title,
                    text: resposta.message,
                    icon: "error",
                    confirmButtonText: "Ok"
                });
            }
        
        }).fail(function(jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
    }

    function cancelChangeReserve(){
        $("#buttons").append("<div class='overlay'><i class='fas fa-2x fa-sync-alt fa-spin'></i></div>");
        
        $.ajax({
            url: _URL+"/gestaoleitos/getSectorAndBedLiberate",
            type: "POST",
            data: {
                "id": idSolicitacao
            },
            dataType: "json"
        }).done(function(resposta) {

            $("#buttons").html(resposta.html);
            if(resposta.status == "L")
                $("#gleitos-alterar-reserva").click(ChangeReserveCard);

            else{
                $("#gleitos-alterar-reserva").click(ChangeReserveCard);
                $("#gleitos-reservar-leito").click(confirmReserve);
                $("#gleitos-cancelar-preparacao").click(cancelPreparation);
            }
        
        }).fail(function(jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
    }

    function finishReserve(e) {
        Swal.fire({
            title: "Tem certeza que quer finalizar a solicitação?",
            icon: "warning",
            showCancelButton: true,
            cancelButtonText: "Não",
            confirmButtonText: "Sim",
            confirmButtonColor: "#e01d00",
            focusCancel: true
        }).then((value) => {
            if(value.isConfirmed){

                $.ajax({
                    url: _URL+"/gestaoleitos/finishReserve",                              
                    type: "POST",                                              
                    data: {
                        "id": idSolicitacao,
                        "registro": $("#gleitos-paciente option:selected").val(),
                        "bedCode": $("#gleitos-leitos option:selected").val() 
                    },
                    dataType: "json"                                             
                }).done(function(resposta) {

                    console.log(resposta);
                    if(resposta.succeeded){
                        Swal.fire({
                            title: resposta.title,
                            icon: "success",
                            confirmButtonText: "Ok",
                        }).then(() => {
                            location.reload();
                        });
                    }
                    else{
                        Swal.fire({
                            title: resposta.title,
                            text: resposta.message,
                            icon: "error",
                            confirmButtonText: "Ok"
                        });
                    }
                }).fail(function(jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
            }
        });
    }

    function gravarLeito(modo_atendimento) {

        var gender = $("#gleitos-genero-paciente").val() == "Feminino" ? "F" : ($("#gleitos-genero-paciente").val() == "Masculino" ? "M" : "O");
        // console.log(modo_atendimento);

        $.ajax({ 
            url: _URL+"/gestaoleitos/preparateBed",
            type: "POST",
            data: {
                id: idSolicitacao,
                mode: modo_atendimento,
                uni: $("#gleitos-unidade-internacao option:selected").val() != '' ? $("#gleitos-unidade-internacao option:selected").val() : "",
                lei: $("#gleitos-leitos option:selected").val() != '' ? $("#gleitos-leitos option:selected").val() : "",
                registro: $("#gleitos-paciente option:selected").val() != '' ? $("#gleitos-paciente option:selected").val() : "",
                gender: gender
            },
            dataType: "json"
        }).done(function(resposta){
            console.log(resposta);
            if(resposta.succeeded){
                Swal.fire({
                    title: resposta.title,
                    text: resposta.message,
                    icon: "success",
                    confirmButtonText: "Ok"
                }).then(() => {
                    location.reload();
                });
            }
            else{
                Swal.fire({
                    title: resposta.title,
                    html: resposta.message,
                    icon: "error",
                    confirmButtonText: "Ok"
                }).then(() => {
                    if(modo_atendimento == "L"){
                        $("#gleitos-reservar-leito").empty();
                        $(".btn").prop("disabled", false);
                        $("#gleitos-reservar-leito").append('Reservar leito');
                    }
                    else{
                        $("#gleitos-preparar-leito").empty();
                        $(".btn").prop("disabled", false);
                        $("#gleitos-preparar-leito").append('Preparar leito');
                    }
                });
            }

        }).fail(function(jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
    }

    async function postDataCriarSolic(formattedFormData){
        const response = await fetch(
            _URL+"/gestaoleitos/criarSolicitacao", 
            {
                method: "POST",
                body: formattedFormData
            }
        );

        try {
            const resposta = await response.json();

            var caminho = _URL+"/gestaoleitos/solicitacao/"+resposta.idSOLICITACAO;
    
            // Se a solicitação foi criada
            if(resposta.succeeded){ // Direciona para a pagina principal
                Swal.fire({
                    title: "Solicitação criada com sucesso!",
                    icon: "success",
                    confirmButtonText: "Ok"
                }).then(() => {
                    location.href = caminho;
                });
            }
            // Se a solicitação não foi criada
            else{
                Swal.fire({
                    title: resposta.title,
                    html: resposta.message,
                    icon: "error",
                    confirmButtonText: "Ok"
                });
                $("#gleitos-criar-leito").empty();
                $("#gleitos-criar-leito").prop("disabled", false);
                $("#gleitos-criar-leito").append('Criar solicitação');
            }
        } catch (error) {
            Swal.fire({
                title: "Ocorreu um erro!",
                text: "Ocorreu um erro ao tentar criar a solicitação. Por favor contatar equipe da TI.",
                icon: "error",
                confirmButtonText: "Ok"
            });
            $("#gleitos-criar-leito").empty();
            $("#gleitos-criar-leito").prop("disabled", false);
            $("#gleitos-criar-leito").append('Criar solicitação');
        }
    }

    async function postDataEditSolic(formattedFormData){
        const response = await fetch(
            _URL+"/gestaoleitos/edition",
            {
                method: "POST",
                body: formattedFormData
            }
        );

        try {
            const resposta = await response.json();
    
            // Se a solicitação foi criada
            if(resposta.succeeded){ // Direciona para a pagina principal
                Swal.fire({
                    title: resposta.title,
                    icon: "success",
                    confirmButtonText: "Ok"
                }).then(() => {
                    location.href = _URL+"/gestaoleitos/solicitacao/"+idSolicitacao;
                });
            }
            // Se a solicitação não foi criada
            else{
                Swal.fire({
                    title: resposta.title,
                    text: resposta.message,
                    icon: "error",
                    confirmButtonText: "Ok"
                });
            }

        } catch (error) {
            Swal.fire({
                title: "Ocorreu um erro!",
                text: "Ocorreu um erro ao tentar criar a solicitação. Por favor contatar equipe da TI.",
                icon: "error",
                confirmButtonText: "Ok"
            });
        }
    }

    $("#gleitos-ver-historico").click(function(e){
        $.ajax({
            url: _URL+"/gestaoleitos/getHistoricoModal",                              
            type: "POST",
            data:{
                id: idSolicitacao
            },
            dataType: "html"                                             
        }).done(function(resposta) {
            
            $("#divToPutModal").append(resposta);
            var myModal = new bootstrap.Modal(document.getElementById('exampleModal'), {
                keyboard: false
            });

            myModal.show();

        }).fail(function(jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
        
    });
});