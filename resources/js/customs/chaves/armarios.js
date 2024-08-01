$("#one").on("click", "button", buttonLocker);

function buttonLocker(e){
    var locker = e.target;
    var lockerNumber = $(locker).text();
    console.log(lockerNumber);

    $(".btn").prop("disabled", true);
    $(locker).empty();
    $(locker).append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

    $.post(_URL+'/chaves/modalLocker', {
        idLocker: $(locker).attr("data-id")
    }, function (data) {

        // ajax to route getFuncionariosData
        $.ajax({
            url: _URL+'/chaves/getFuncionariosData',
            type: "POST",
            dataType: "json"
        }).done(function(resposta){
            $("#matricula-select-div").append(resposta);
            $("#matricula").selectize({
                create: false,
                sortField: "text",
                persist: false,
                maxOptions: 100000000000000
            });
        });

        $('#divModal').append(data);
        $('#lockerInfo').modal('show');

        $('#btn-alugar').click(verifyFuncionario);
        $('#btn-devolver').click(devolver);
        
        ($('#lockerInfo')[0]).addEventListener('hide.bs.modal', function(event){
            $(locker).empty();
            $(".btn").prop("disabled", false);
            $(locker).append(lockerNumber);
        });

        ($('#lockerInfo')[0]).addEventListener('hidden.bs.modal', function(event){
            $('#divModal').empty();
        });
    });
}

function verifyFuncionario(e){
    if($("#matricula").val().length == 0) return showError('Insira uma matricula', 'error');
    $.ajax({ 
        url: _URL+"/chaves/searchRegistration",
        type: "POST",
        data: {
            matricula: $("#matricula").val()
        },
        dataType: "json"
    }).done(function(resposta){
        if(resposta.succeded){
            Swal.fire({
                title: resposta.title,
                html: resposta.message,
                icon: "question",
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonColor: "#6b6",
                cancelButtonColor: "#e55",
                confirmButtonText: "Sim",
                cancelButtonText: "Não",
                focusCancel: true,
                width: "50em"
            }).then(function (res){
                if(res.isConfirmed){
                    alugar();
                } else console.log('Error');
            });
        }
        else{
            if(resposta.code == undefined)
                Swal.fire({
                    title: resposta.title,
                    icon: "error",
                    confirmButtonText: "Ok"
                });
            else{
                Swal.fire({
                    title: resposta.title,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#6b6",
                    cancelButtonColor: "#e55",
                    confirmButtonText: "Sim",
                    cancelButtonText: "Não",
                    focusCancel: true,
                }).then((resposta) => {
                    if(!resposta.isConfirmed)
                        return;
                    let matricula = $("#matricula").val()
                    $('#cadModel').modal('show');
                    $.ajax({
                        url: _URL+'/chaves/getSetores',
                        type: "POST",
                        dataType: "html"
                    }).done(function(res){
                        $('.select-setor-div').append(res);
                    }).fail(function(jqXHR, textStatus){
                        console.log("Request failed: " + textStatus);
                    })
                    $('input[placeholder="Matrícula"]').val(matricula)
                    $('.cad-user').click(function(){
                        let setor = $('.setor-select > option:selected').text();
                        let nome = $(".name-input").val();
                        addFuncionario(matricula, nome, setor);
                    });

                });
            }
        }

    }).fail(function(jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function addFuncionario(matricula, nome, setor){
    $.ajax({ 
        url: _URL+"/chaves/addFuncionario",
        type: "POST",
        data: {
            matricula: matricula,
            nome: nome,
            setor: setor.trim()
        },
        dataType: "json"
    }).done(function(resposta){
        console.log(resposta)
        Swal.fire({
            title: resposta.title,
            icon: "success",
            confirmButtonText: "Ok"
        }).then(function(res){
            if(res.isConfirmed) verifyFuncionario();
        });
        $('#cadModel').modal('hide');
    }).fail(function(jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function alugar() {
    $.ajax({ 
        url: _URL+"/chaves/alugarLocker",
        type: "POST",
        data: {
            matricula: $("#matricula").val(),
            idLocker: $(".spinner-border").parent().attr("data-id")
        },
        dataType: "json"
    }).done(function(resposta){

        if(resposta.succeded){
            Swal.fire({
                title: resposta.title,
                icon: "success",
                confirmButtonText: "Ok",
            }).then(() => {
                
                $("#tab-chaves").append("<div class='overlay'><i class='fas fa-2x fa-sync-alt fa-spin'></i></div>");

                $('#lockerInfo').modal('hide');
                getAllLockers();
            });
        }
        else{
            Swal.fire({
                title: resposta.title,
                icon: "error",
                confirmButtonText: "Ok"
            });
        }

    }).fail(function(jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function getAllLockers(){
    $.ajax({ 
        url: _URL+"/chaves/getAllLockers",
        type: "POST",
        dataType: "html",
        async: false
    }).done(function(resposta){
        $(".armarios").empty();
        $(".armarios").append(resposta);
        $(".armarios").on("click", "button", buttonLocker);
        $(".overlay").remove();

    }).fail(function(jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

function devolver(){
    Swal.fire({
        title: "Você tem certeza que quer devolver?",
        icon: "question",
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Sim",
        cancelButtonText: "Não",
        focusCancel: true
    }).then((res) => {
        if(res.isConfirmed){
            $.ajax({ 
                url: _URL+"/chaves/devolverLocker",
                type: "POST",
                data:{
                    idLocker: $(".spinner-border").parent().attr("data-id")
                },
                dataType: "json"
            }).done(function(resposta){
        
                if(resposta.succeded){
                    Swal.fire({
                        title: resposta.title,
                        icon: "success",
                        confirmButtonText: "Ok",
                    }).then(() => {
                        $("#tab-chaves").append("<div class='overlay'><i class='fas fa-2x fa-sync-alt fa-spin'></i></div>");

                        $('#lockerInfo').modal('hide');
                        getAllLockers();
                    });
                }
                else{
                    Swal.fire({
                        title: resposta.title,
                        icon: "error",
                        confirmButtonText: "Ok"
                    });
                }

            }).fail(function(jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
        }
        else return;
    });
}

$('#filter-select-keys').change(function(){
    var buttonsReverse = $(".armarios").children().toArray().reverse();
    var newButtons = "";
    $(buttonsReverse).each(function(){
        newButtons += ($(this)[0]).outerHTML;
    });
    $(".armarios").html(newButtons);
});

$('#locked-radius').change(function(){
    if($(this).is(":checked"))
        $('.armarios').children('.btn-danger').removeClass('d-none');
    else
        $('.armarios').children('.btn-danger').addClass('d-none');
});

$('#free-radius').change(function(){
    if($(this).is(":checked"))
        $('.armarios').children('.btn-success').removeClass('d-none');
    else
        $('.armarios').children('.btn-success').addClass('d-none');
});