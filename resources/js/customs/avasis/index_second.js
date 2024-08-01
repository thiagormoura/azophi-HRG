// $(document).ready(()=>
// {
//     const id = window.location.href.split('/').pop();
//     const array_respostas = [];
//     //Funções responsaveis por carregar a página das perguntas

//         $.ajax(
//         {
//             url: `${_URL}/avasis/questionario/${id}`,
//             type: 'GET',
//         }
//         ).done(function(response)
//         {
//             // console.log(response)
//         }).fail((jqXHR, textStatus)=> {
//             console.log("Request failed: " + textStatus);
//         }).always();    

//         //Funções responsaveis por salvar as respostas
//         $('.full').on('click', function(){

//             var idCollapse = ($($(this).parents()[5]).attr("id")).substring(8);
//             idCollapse = parseInt(idCollapse);

//             $("#collapse"+idCollapse).collapse('hide');
//             $("#collapse"+(++idCollapse)).collapse('show');
//             location.href = location.href.indexOf("#") ? 
//                 (location.href.split("#")[0])+"#collapse"+(idCollapse-1) :
//                 (location.href+"#collapse"+(idCollapse-1));


//             const inp = $(this).prev();
//             const quest_id = $(this).parent().parent().prev().attr('id');
//             const avaluation = $(this).prev().attr('value');

//             if(array_respostas.length == 0) array_respostas.push({question_id: quest_id, answer: avaluation, id: id});
//             else if(array_respostas.some((el) => {
//                 return el.question_id == quest_id;
//             })){
//                 array_respostas.forEach(element => {
//                     if(element.question_id == quest_id)
//                         element.answer = avaluation;
//                 });
//             }
//             else array_respostas.push({question_id: quest_id, answer: avaluation, id: id});

//             $(inp).prop("checked", true);

//         });

//         //ativa o modal e envia as respostas
//         $('.submit-avaluation').on('click', function(){
//             function enviar(){
//                 Swal.fire({
//                     icon: 'success',
//                     title: 'Avaliação enviada com sucesso!',
//                     text: 'Agradecemos o seu contato.',
//                     confirmButtonText: 'Ok',
//                     timer: 1500
//                 }).then((result) => {
//                     $.ajax({
//                         url: `${_URL}/avasis/enviarQuestionario`,
//                         type: 'POST',
//                         data: {array_respostas}
//                     }).done(function(response) {
//                         location.href = `${_URL}/avasis`;
//                     }).fail((jqXHR, textStatus)=>{
//                         console.log("Request failed: " + textStatus);
//                     }).always();
//                 })
//             }

//             if($(".accordion-item").length == array_respostas.length)
//             {
//                 Swal.fire({
//                     icon: 'info',
//                     title: 'Deseja fazer observações ?',
//                     showCancelButton: true,
//                     confirmButtonColor: '#3085d6',
//                     denyButtonColor: '#d33',
//                     confirmButtonText: 'Sim',
//                     denyButtonText: 'Não'
//                 }).then((result) => {
//                     //Se sim dispara o modal e pega as observações
//                     if (result.value) {
//                         $('#modalObs').modal('show');
//                         $('#sub').on('click', function(){
//                             let name = $('#nome').val();
//                             let contato1 = $('#cont_1').val();
//                             let contato2 = $('#cont_2').val();
//                             let obs = $('#observacao').val();
//                             array_respostas.push({name, contato1, contato2, obs});
//                             enviar();
//                         });
//                     } else 
//                     {
//                         enviar();
//                     }

//                 });

//             } else 
//             {
//                 Swal.fire({
//                     icon: 'error',
//                     title: 'Perguntas faltando!',
//                     text: 'Preencha todas as perguntas para continuar',
//                   });
//             }
//         });
//     }
// )

const array_respostas_questionario = [];

//Funções responsaveis por salvar as respostas
$('#logged-questionario .full').on('click', function () {

    var collapseFather = $(this).parents()[6];
    var cardFather = $(this).parents()[4];
    var idCollapse = parseInt(($(collapseFather).attr("id")).substring(8));

    $(cardFather).addClass('answeredCard');

    if ($(cardFather).parent().children(':not(.answeredCard)').length == 0) {
        $("#collapse" + idCollapse).prev().children().first().addClass('answered');
        $("#collapse" + idCollapse).collapse('hide');
        $("#collapse" + (++idCollapse)).collapse('show');
        $([document.documentElement, document.body]).animate({
            scrollTop: $("#collapse" + (idCollapse - 1)).offset().top
        }, 100);
    }
    else {
        $([document.documentElement, document.body]).animate({
            scrollTop: $(cardFather).offset().top
        }, 100);
    }

    const inp = $(this).prev();
    const quest_id = $(this).parent().parent().prev().attr('id');
    const avaluation = $(this).prev().attr('value');


    if (array_respostas_questionario.some((el) => {
        return el.question_id == quest_id;
    })) {
        array_respostas_questionario.forEach(element => {
            if (element.question_id == quest_id)
                element.answer = avaluation;
        });
    }
    else {
        array_respostas_questionario.push({ question_id: quest_id, answer: avaluation, questionario_id: Number.isInteger(id_questionario) ? id_questionario : location.href.split('/').pop() });
    }


    $(inp).prop("checked", true);
});

$('.submit-avaluation-logged').on('click', function () {

    if ($(".group-cards .card").length == array_respostas_questionario.length) {
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
                if ($('#sub').length)
                    $("#sub").attr('id', 'submit-relatorio');

                $("#modalObs form").submit(function () {
                    let name = $('#nome').val();
                    let contato1 = $('#cont_1').val();
                    let contato2 = $('#cont_2').val();
                    let obs = $('#observacao').val();
                    const array_respostas_with_obs = [];
                    array_respostas_with_obs.push({ data: array_respostas_questionario, observacoes: { nome: name, contatoPrimeiro: contato1, contatoSegundo: contato2, observacao: obs } });
                    enviarRespostas(array_respostas_with_obs);
                });

            } else {
                const array_respostas_with_obs = [];
                array_respostas_with_obs.push({ data: array_respostas_questionario, observacoes: [] });
                enviarRespostas(array_respostas_with_obs);
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

function enviarRespostas(data) {

    $.ajax({
        url: `${_URL}/avasis/enviarQuestionario`,
        type: 'POST',
        data: data
    }).done(function (response) {
        if (!response.result)
            return;

        Swal.fire({
            icon: 'success',
            title: 'Avaliação enviada com sucesso!',
            text: 'Agradecemos o seu contato.',
            confirmButtonText: 'Ok'
        }).then((result) => {
            location.href = `${_URL}/avasis`;
        });

    }).fail((jqXHR, textStatus) => {
        Swal.fire({
            icon: 'error',
            title: 'Algo deu errado!',
            text: textStatus,
            confirmButtonText: 'Ok'
        });
    });
}