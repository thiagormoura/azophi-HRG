$(document).ready(function() {

    var actual_create_id_identificacao = 1;

    // get all patients and prepare selectize 

    // prepare selectize
    // $("#slSetoresEnvolvidos").selectize({
    //     plugins: ['remove_button'],
    //     maxItems: null,
    // }); 

    // Clean and disable/enable autor name input when cb is checked
    $("#cbAutorAnonimo").on("change", function() {
        
        if($(this).is(":checked")) {
            $("#inAutor").val("Anônimo");
            $("#inAutor").prop("disabled", true);
        } else {
            $("#inAutor").val("");
            $("#inAutor").prop("disabled", false);
        }
    
    });

    // Show/hide patient inputs when rb is selected
    $("input[name='rbPaciente']").on("change", function(){
        
        $("#inPaciente").val("");
        $("#inRegistroPac").val("");
        $("#inTelefonePac").val("");
        $("#slPaciente").val("");

        $("#inPaciente").parent().removeClass("d-none");
        $("#inRegistroPac").parent().removeClass("d-none");
        $("#inTelefonePac").parent().parent().removeClass("d-none");

        if($("#rbInserirPac").is(":checked")) {
            $("#inPaciente").removeClass("d-none");
            //$("#inPaciente").prop("required", true);
            $("#inRegistroPac").prop("disabled", false);
            $("#inRegistroPac").attr("placeholder", "Digite o registro");
            $("#slPaciente").addClass("d-none");
            //$("#slPaciente").prop("required", false);
        } 
        
        else {

            $("#inPaciente").addClass("d-none");
            //$("#inPaciente").prop("required", false);
            $("#inRegistroPac").prop("disabled", true);
            $("#inRegistroPac").attr("placeholder", "");
            $("#slPaciente").removeClass("d-none");
            //$("#slPaciente").prop("required", true);
        }


    });

    // phone mask
    // $("#inTelefonePac").mask("(00) 00000-0000");

    // Show/hide "rede social" input when radio button is checked or unchecked
    // Show hide "outro" input when radio button is checked or unchecked
    $("input[name='rbVeiculoManifestacao']").on("change", function(){

        // Clean inputs
        $("#inRedeSocial").val("");
        $("#inOutro").val("");

        // If "Rede Social" is checked, show input and add required to form
        if($("#rbRedeSocial").is(":checked")) {
            $("#inRedeSocial").parent().parent().removeClass("d-none");
            $("#inRedeSocial").prop("required", true);
        }
        else{
            $("#inRedeSocial").parent().parent().addClass("d-none");
            $("#inRedeSocial").prop("required", false);
        }

        // If "Outro" is checked, show input and add required to form
        if($("#rbOutro").is(":checked")) {
            $("#inOutro").parent().parent().removeClass("d-none");
            $("#inOutro").prop("required", true);
        }
        else{
            $("#inOutro").parent().parent().addClass("d-none");
            $("#inOutro").prop("required", false);
        }

    });


    // Add identificacao radios buttons, ta and select when button is clicked
    $("#btnAddIdentificacao").click(function() {
    
        $.ajax({
            url: _URL + "/ouvimed/getNewIdentificacaoElement",
            type: "POST",
            data:{
                id: actual_create_id_identificacao
            },
            success: function(data) {

                actual_create_id_identificacao++;

                $("#divAppendIdentificacao").append(data);

                $("select[name='slSetoresEnvolvidos']").selectize({
                    plugins: ['remove_button'],
                    maxItems: null,
                }); 

            },
            error: function(msg) {
                console.log(msg);
            }
        });

    });

    // remove identificacao
    $(document).on("click", "button[name='btnRemoverIdentificacao']", function() {
        $(this).parent().parent().remove();
    })

    // form submit
    $("#form").on("submit", function(e) {

        console.log("sim");

        e.preventDefault();
    
        // Get autor name
        var autor = $("#inAutor").val();
  
        // Get data e hora da manifestação
        var dthr_manifestacao = $("#inDtManifestacao").val() + "T" + $("#inHrManifestacao").val();

        // Get patient data
        var nome_paciente = 'Não Informado';
        var registro_paciente = '---';
        var telefone_paciente = '';

        // Manualy inserted
        if($("#rbInserirPac").is(":checked")) {
            nome_paciente = $("#inPaciente").val() == "" ? 'Não Informado' : $("#inPaciente").val();
            registro_paciente = $("#inRegistroPac").val() == "" ? '---' : $("#inRegistroPac").val();
            telefone_paciente = $("#inTelefonePac").val();
        } 
        
        // Auto search
        else {
            // do later
            console.log("do later");
            return;
        }

        var veiculo_manifestacao = $("input[name='rbVeiculoManifestacao']:checked").val();
        if(veiculo_manifestacao=='REDE_SOCIAL')
            var veiculo_descricao = $("#inRedeSocial").val();
        else if(veiculo_manifestacao=='OUTRO')
            var veiculo_descricao = $("#inOutro").val();
        else
            var veiculo_descricao = null;


        // for each all divs with name IdentificacaoRowx
       
        var identificacoes_manifestacao = [];
        var setores_envolvidos = [];
        $("div[name^='IdentificacaoRow']").each(function() {

            // find checked radio button
            var identificacao = $(this).find("input[name^='identificacao']:checked").val();

            // find text area
            var descricao = $(this).find("textarea[name^='taIdentificacao']").val();

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

        console.log("engraçado não?");
        
        $.ajax({
            url: _URL + "/ouvimed/criar",
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
                setores_envolvidos: setores_envolvidos
            },
            success: function(data) {
                if(data[0] == true){
                    Swal.fire({
                        icon:'success',
                        title:'Sucesso!',
                        text:'Manifestação registrada com sucesso!',
                        confirmButtonText:'Ok'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location.href = _URL + "/ouvimed";
                        }
                    });
                }
            },
            error: function(msg) {
                console.log(msg);
            }
        });

    });

    // force checked to Inserir paciente manualmente
    $("#rbInserirPac").prop("checked", true);
    $("#rbInserirPac").trigger("change");

});
