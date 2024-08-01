var categorias = null;
var pergunta_ini = null;
var categoria_ini = null;
var perguntas_tam = 0;
var respostas = [];


$(".box-pergunta").click(function(){
	$("#third").fadeOut(200);

	$.post(_URL + '/avasis/index_second', {
		'id_questionario': $(this).attr('data-id')
	}, function(data) {
		$(data).insertAfter("#third");
		$("#second").fadeOut(100);
		window.setTimeout(function(){
			$("#second").removeClass("d-none");
		}, 500);
	});

});

$("#customRadio2").click(function(){
	var respostas = [];
	for (let i = 0; i < perguntas_tam; i++) {
		if(i == 0){
			var resposte = {
				"resposta_valor": 0,
				"id_pergunta": pergunta_ini.id
			}
			respostas.push(resposte);
		}
		else{
			console.log(categorias[i-1].id);
			$.ajax({
				url: "App/controller.php",
				type: "POST",
				data: "func=puxar&proxima_pergunta="+categorias[i-1].id,
				dataType: "html"
			}).done(function(resposta){
				resposta = JSON.parse(resposta);
				var resposte = {
					"resposta_valor": 0,
					"id_pergunta": resposta.id
				}
				respostas.push(resposte);

			}).fail(function(jqXHR, textStatus) {
				console.log("Request failed: " + textStatus);
			});	

		}
		
	}

	$.ajax({
		url: "App/controller.php",
		type: "POST",
		data: "func=mark&respostas="+JSON.stringify(respostas),
		dataType: "html"
	}).done(function(resposta){
		if(resposta == "true"){
			alert("Obrigado por não responder! 😁");
			window.location.reload();
		}
		else console.log(resposta);

	}).fail(function(jqXHR, textStatus) {
		console.log("Request failed: " + textStatus);
	});
});	
$("#customRadio1").click(function(){
	$("#modalInicio").fadeOut(200);
	$("#third").fadeOut(100);
	window.setTimeout(function(){
		$("#third").removeClass("d-none");
	}, 500);
});

$("#start_quest").click(function() {
	$.post(_URL + "/avasis/startQuestionario", function(data) {
		if(data.success){
			$('#start').append(data.html);
			$('#showQuestionario').modal('show');
			$("#startBodyQuestionario tr").click(function(){
				if($(this).attr('data-id') !== 'undefined' && $(this).attr('data-id') !== false){
					location.href = _URL + "/avasis/questionario/"+$(this).attr('data-id');
				}
			});
			$("#startBodyQuestionario tr").hover(function(){
				$(this).css("background-color", "#d7d7d7");
			}, function(){
				$(this).css("background-color", "white");
			});
		}
		else{
			console.log(data.html);
			Swal.fire({
				title: data.html,
				icon: "error",
				confirmButtonText: "Ok",
			});
		}
	});
});
