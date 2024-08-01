$(document).ready(function() {

    // get id from url
    var url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1);

    // prepare selectize    
    $("select[name='slSetoresEnvolvidosEdicao']").each(function () {
   
        var options_values = [];
        $(this).find("option").each(function () {
            options_values.push($(this).val());
        });

        $(this).selectize({
            maxItems: null,
            plugins: ['remove_button']
        });
    });

    // select selected options
    $("select[name='slSetoresEnvolvidosEdicao']").each(function () {
        var selectize = $(this)[0].selectize;
        var options_values = [];
        $(this).find("option").each(function () {
            if ($(this).attr("selected") == "selected") {
                selectize.addItem($(this).val());
            }
        });
    });

    $("#btCancelarEdicao").click(function() {
        
        Swal.fire({
            title: 'Cancelar edição?',
            text: "Você tem certeza que deseja cancelar a edição? Todas as alterações serão perdidas.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, cancelar!',
            cancelButtonText: 'Não, continuar editando!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = _URL + "/ouvimed/manifestacao/" + id;
            }
        });

    });

    $("#btConfirmarEdicao").click(function() {
        
        // Get autor name
        var autor = $("#inAutor").val();
  
        // Get data e hora da manifestação
        var dthr_manifestacao = $("#inDtManifestacao").val() + "T" + $("#inHrManifestacao").val();

        // Get patient data

        // Manualy inserted
        var nome_paciente = $("#inPaciente").val();
        var registro_paciente = $("#inRegistroPac").val();
        var telefone_paciente = $("#inTelefonePac").val();
    
        var veiculo_manifestacao = $("input[name='rbVeiculoManifestacao']:checked").val();
        if(veiculo_manifestacao=='REDE_SOCIAL')
            var veiculo_descricao = $("#inRedeSocial").val();
        else if(veiculo_manifestacao=='OUTRO')
            var veiculo_descricao = $("#inOutro").val();
        else
            var veiculo_descricao = null;

        var identificacoes_manifestacao = [];
        var setores_envolvidos = [];
        $("div[name^='IdentificacaoRow']").each(function() {

            // find checked radio button
            var identificacao = $(this).find("input[name^='identificacao']:checked").val();

            // find text area
            var descricao = $(this).find("textarea[id^='taIdentificacao']").val();

            // add to array
            identificacoes_manifestacao.push({
                identificacao: identificacao,
                descricao: descricao
            });

  
            // find selectize
            var setores = $(this).find("select")[0].selectize.getValue();

            // add to array
            setores_envolvidos.push(setores);
            
    
        });

        // verificação de requisitos

        if(autor == ""){
            Swal.fire({
                icon:'error',
                title:'Erro!',
                text:'Por favor, preencha o campo "Autor".',
                confirmButtonText:'Ok'
            });
            return;
        }

        if(dthr_manifestacao == ""){
            Swal.fire({
                icon:'error',
                title:'Erro!',
                text:'Por favor, preencha o campo "Data e hora da manifestação".',
                confirmButtonText:'Ok'
            });
            return;
        }

        if(registro_paciente == ""){
            Swal.fire({
                icon:'error',
                title:'Erro!',
                text:'Por favor, preencha o campo "Registro do paciente".',
                confirmButtonText:'Ok'
            });
            return;
        }

        if(veiculo_manifestacao == null){
            Swal.fire({
                icon:'error',
                title:'Erro!',
                text:'Por favor, selecione o campo "Veículo da manifestação".',
                confirmButtonText:'Ok'
            });
            return;
        }

        if(veiculo_manifestacao == 'REDE_SOCIAL' && veiculo_descricao == ""){
            Swal.fire({
                icon:'error',
                title:'Erro!',
                text:'Por favor, preencha o campo "Rede social".',
                confirmButtonText:'Ok'
            });
            return;
        }

        if(veiculo_manifestacao == 'OUTRO' && veiculo_descricao == ""){
            Swal.fire({
                icon:'error',
                title:'Erro!',
                text:'Por favor, preencha o campo "Outro".',
                confirmButtonText:'Ok'
            });
            return;
        }

        if(setores_envolvidos.length == 0){
            Swal.fire({
                icon:'error',
                title:'Erro!',
                text:'Por favor, selecione ao menos um "Setor envolvido".',
                confirmButtonText:'Ok'
            });
            return;
        }


        Swal.fire({
            title: 'Confirmação',
            text: "Deseja realmente editar esta manifestação?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: _URL + "/ouvimed/editar/" + id,
                    type: "POST",
                    data:{
                        nome_autor: autor,
                        data_hora_manifestacao: dthr_manifestacao,
                        nome_paciente: nome_paciente,
                        registro_paciente: registro_paciente,
                        telefone_paciente: telefone_paciente,
                        veiculo_manifestacao: veiculo_manifestacao,
                        veiculo_descricao: veiculo_descricao,
                        identificacoes_manifestacao: identificacoes_manifestacao,
                        setores_envolvidos: setores_envolvidos,
                    },
                    success: function(data) {

                        if(data[0] == true){
                            Swal.fire({
                                icon:'success',
                                title:'Sucesso!',
                                text:'Manifestação editada com sucesso!',
                                confirmButtonText:'Ok'
                            }).then((result) => {
                                if(result.isConfirmed){
                                    window.location.href = _URL + "/ouvimed/manifestacao/" + id;
                                }
                            });
                        }

                        else{

                            Swal.fire({
                                icon:'error',
                                title:'Erro!',
                                text:'Erro ao editar manifestação! Por favor, entre em contato com a TI.',
                                confirmButtonText:'Ok'
                            });

                        }
                    },
                    error: function(msg) {

                        Swal.fire({
                            icon:'error',
                            title:'Erro!',
                            text:'Erro ao editar manifestação! Por favor, entre em contato com a TI. Erro: ' + msg,
                            confirmButtonText:'Ok'
                        });
                    }
                });

            }
        });



        

    });

    

});
