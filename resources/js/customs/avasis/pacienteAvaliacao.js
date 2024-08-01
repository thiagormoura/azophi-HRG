const array_respostas = [];

$("[name='pacienteQuestionario']").change(function () {
    if ($("[name='pacienteQuestionario']:checked").val() == 'yes') {
        $("#modalInicio").fadeOut(200);
        window.setTimeout(function () {
            $("#third").fadeIn(200);
        }, 200);
    }
});

$(".box-questionario").click(function () {
    const id_questionario = $(this).attr('data-id');

    $.get(_URL + '/avasis/puxarQuestionario/' + id_questionario,
        function (data) {
            $("#third").fadeOut(200);
            $("#questions").html(data.perguntas);
            $("#questionario_name").text(data.titulo);
            window.setTimeout(function () {
                $("#questions").parent().fadeIn(200);
            }, 200);

            function enviar(data) {
                console.log(data);

                $.ajax({
                    url: `${_URL}/avasis/enviarQuestionario`,
                    type: 'POST',
                    data: data[0]
                }).done(function (response) {
                    if(!response.result)
                        return;

                    Swal.fire({
                        icon: 'success',
                        title: 'Avaliação enviada com sucesso!',
                        text: 'Agradecemos o seu contato.',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        location.href = `${_URL}/avasis/avaliar`;
                    })
                }).fail((jqXHR, textStatus) => {
                    console.log("Request failed: " + textStatus);
                });
            }

            $.post(_URL + '/avasis/getModalEnviar',
                function (data) {
                    $("#divModalObs").html(data);
                    $('#sub').on('click', function () {
                        let name = $('#nome').val();
                        let contato1 = $('#cont_1').val();
                        let contato2 = $('#cont_2').val();
                        let obs = $('#observacao').val();
                        const array_respostas_with_obs = [];
                        array_respostas_with_obs.push({data: array_respostas, observacoes: {nome: name, contatoPrimeiro: contato1, contatoSegundo: contato2, observacao: obs}});
                        enviar(array_respostas_with_obs);
                    });
                }
            );

            //Funções responsaveis por salvar as respostas
            $('.full').on('click', function () {

                var collapseFather = $(this).parents()[6];
                var cardFather = $(this).parents()[4];
                var idCollapse = parseInt(($(collapseFather).attr("id")).substring(8));

                $(cardFather).addClass('answeredCard');

                if($(cardFather).parent().children(':not(.answeredCard)').length == 0){
                    $("#collapse" + idCollapse).prev().children().first().addClass('answered');
                    $("#collapse" + idCollapse).collapse('hide');
                    $("#collapse" + (++idCollapse)).collapse('show');
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $("#collapse" + (idCollapse-1)).offset().top
                    }, 100);
                }
                else{
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $(cardFather).offset().top
                    }, 100);
                }


                const inp = $(this).prev();
                const quest_id = $(this).parent().parent().prev().attr('id');
                const avaluation = $(this).prev().attr('value');


                if (array_respostas.some((el) => {
                    return el.question_id == quest_id;
                })) {
                    array_respostas.forEach(element => {
                        if (element.question_id == quest_id)
                            element.answer = avaluation;
                    });
                }
                else array_respostas.push({ question_id: quest_id, answer: avaluation, questionario_id: id_questionario });

                $(inp).prop("checked", true);
            });

            $('.submit-avaluation').on('click', function () {

                if ($(".accordion-item").length == array_respostas.length) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Deseja fazer observações?',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        denyButtonColor: '#d33',
                        confirmButtonText: 'Sim',
                        denyButtonText: 'Não'
                    }).then((result) => {
                        //Se sim dispara o modal e pega as observações
                        if (result.value) {
                            $('#modalObs').modal('show');
                        } else {
                            const array_respostas_with_obs = [];
                            array_respostas_with_obs.push({data: array_respostas, observacoes: []});
                            enviar(array_respostas_with_obs);
                        }

                    });

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perguntas faltando!',
                        text: 'Preencha todas as perguntas para continuar',
                    });
                }
            });
        }
    );
});
