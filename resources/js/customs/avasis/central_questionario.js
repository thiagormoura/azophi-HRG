jQuery.extend(jQuery.validator.messages, {
	required: "Este campo Ã© obrigatÃ³rio",
});

$.fn.dataTableExt.ofnSearch["html-input"] = function (value) {
	return $(value).find("select").find(":selected").text();
};

$.fn.dataTable.ext.order["dom-text"] = function (settings, col) {
	return this.api()
		.column(col, {
			order: "index",
		})
		.nodes()
		.map(function (td, i) {
			return $("input[type=text]", td).val();
		});
};

$.fn.dataTable.ext.order["dom-option"] = function (settings, col) {
	return this.api()
		.column(col, {
			order: "index",
		})
		.nodes()
		.map(function (td, i) {
			return $("select", td).find(":selected").text();
		});
};

$.fn.dataTable.ext.order["dom-check"] = function (settings, col) {
	return this.api()
		.column(col, {
			order: "index",
		})
		.nodes()
		.map(function (td, i) {
			return +$("input[type=checkbox]", td).prop("checked");
		});
};

var table = $("#table-questionarios").DataTable({
	responsive: true,
	order: [
		[0, "desc"],
		[2, "asc"],
	],
	language: {
		paginate: {
			first: "Primeiro",
			last: "Ultimo",
			next: "Próximo",
			previous: "Anterior",
		},
		decimal: "",
		emptyTable: "A tabela está vazia.",
		info: "Exibindo _END_ de um total de _TOTAL_ elementos",
		infoEmpty: "Exibindo um total de 0 elementos",
		infoFiltered: "(Filtrando um total de _MAX_ elementos)",
		infoPostFix: "",
		thousands: ",",
		lengthMenu: "Exibir _MENU_ elementos",
		loadingRecords: "Carregando...",
		processing: "Processando...",
		search: "Pesquisar:",
		zeroRecords: "Sem resultado...",
	},
	columnDefs: [
		{
			orderable: true,
			orderDataType: "dom-check",
			targets: [0],
			orderData: [0, 0],
		},
		{
			searchable: true,
			orderable: true,
			orderDataType: "dom-text",
			type: "string",
			targets: [1],
			orderData: [1, 0],
		},
		{
			searchable: true,
			orderable: true,
			orderDataType: "dom-text",
			type: "html-input",
			targets: [2],
			orderData: [2, 0],
		},
		{
			searchable: true,
			orderable: false,
			orderDataType: "dom-option",
			type: "html-input",
			targets: [3],
			orderData: [3, 0],
		},
	],
});

// $(".toolbar-questionario").html(
//   "<button type='button' name='submit' class='btn d-flex btn-primary btn-add-questionario'><span>Adicionar</span><div class='icon ml-2'><i class='fal fa-plus'></i></div></button>"
// );

$(document).ready(function () {
	$("#table-questionarios").on("change", "input, select", function () {
		var ctnComida = $(this).closest("tr");
		ctnComida.find("input[type=text]").addClass("changed");
		ctnComida.find("input[type=hidden]").addClass("changed");
		ctnComida.find("select").addClass("changed");
	});
	var tablex = null;
	var table = null;
});

$("button.perguntas").click(function(){
	$('.perguntas').prop('disabled', true);
	$.post(_URL + "/avasis/showPerguntas",
		{ id_questionario: $(this).attr("data-id")}, function (data) {
			$("#divQuestionario").empty();
			$("#divQuestionario").append(data);
			$(".modal").modal("show");
			$('.perguntas, #addPergunta, #removePergunta').prop('disabled', false);

			$("#showPerguntas #addPergunta").click(function () {
				$('.modal #addPergunta').prop('disabled', true);
				if ($(".modal").attr("data-modify") == "0") {
					$(".modal").attr("data-modify", "1");

					var ray_perguntas = [];

					if (($("#perguntas")[0]).tBodies[0].rows.length > 1 || (($("#perguntas")[0]).tBodies[0].rows.length == 1 && !$($("#perguntas").find("tr")[1]).hasClass('no-one'))) {
						$(($("#perguntas")[0]).tBodies[0].rows).each(function () {
							ray_perguntas.push($(this).attr("data-id"));
						});
					}

					if (ray_perguntas.length == 0) ray_perguntas = "";

					$.post(_URL + '/avasis/getPerguntasToAddInQuestionario', {
						'perguntasSelected': ray_perguntas
					}, function (data) {
						$("#divQuestionario .modal").modal("hide");


						window.setTimeout(function () {
							$(this).text("Adicionar as perguntas selecionadas");
							$("#removePergunta").addClass("d-none");

							$("#divQuestionario .modal-body").empty();
							$('#divQuestionario .modal-body').append(data);

							tablex = $('#addPerguntasQuestionario').DataTable({
								'columnDefs': [
									{
										'targets': 0,
										'checkboxes': {
											'selectRow': true
										}
									}],
								'select': {
									'style': 'multi'
								},
								language: ptBrDataTable,
								'order': [[1, 'asc']]
							});

							$("#divQuestionario .modal").modal("show");
							$('.modal #addPergunta').prop('disabled', false);
						}, 500);
					});
				}
				else if ($(".modal").attr("data-modify") == "1") {

					var rows_selected = tablex.column(0).checkboxes.selected();
					var ray_id = [];

					// Iterate over all selected checkboxes
					$.each(rows_selected, function (index, rowId) {
						ray_id.push(rowId);
					});
					ray_id.sort();

					var questionarioId = $(".modal").attr("data-id");

					$.ajax({
						url: _URL + "/avasis/putPerguntasToAddInQuestionario",
						type: "POST",
						data: {
							perguntas: ray_id,
							id_questionario: questionarioId
						},
						dataType: "json"
					}).done(function (resposta) {
						Swal.fire({
							title: "Perguntas adicionadas ao questionario com sucesso!",
							icon: "success",
							confirmButtonText: "OK",
						}).then(() => {
							$("#divQuestionario .modal").modal('hide');
							window.setTimeout(function () {
								$("#divQuestionario").empty();
								callPerguntas(questionarioId);
							}, 300);
						});

					}).fail(function (jqXHR, textStatus) {
						console.log("Request failed: " + textStatus);
						Swal.fire({
							title: "Algo deu errado!",
							icon: "error",
							text: textStatus,
							confirmButtonText: "OK",
						});
					});
				}
			});

			$("#showPerguntas #removePergunta").click(function () {
				$('.modal btn').prop('disabled', true);
				if ($(".modal").attr("data-modify") != "2") {
					if (($("#perguntas")[0]).tBodies[0].rows.length == 1 && $($("#perguntas").find("tr")[1]).hasClass('no-one')) {
						Swal.fire({
							title: "Não há perguntas nesse questionário!",
							icon: "error",
							text: textStatus,
							confirmButtonText: "OK",
						});
					}
					else {
						$(".modal").attr("data-modify", "2");
						$(this).text("Remover as perguntas selecionadas");
						$("#addPergunta").addClass("d-none");

						$.get(_URL + '/avasis/getPerguntasFromQuestionario/' + $(".modal").attr("data-id"), function (data) {
							// $(".modal-body").empty();
							// $('.modal-body').append(data);
							$("#divQuestionario .modal-body").empty();
							$('#divQuestionario .modal-body').append(data);

							tablex = $('#addPerguntasQuestionario').DataTable({
								'columnDefs': [
									{
										'targets': 0,
										'checkboxes': {
											'selectRow': true
										}
									}],
								'select': {
									'style': 'multi'
								},
								'order': [[1, 'asc']],
								'language': ptBrDataTable
							});

							$('.modal btn').prop('disabled', false);
						});
					}
				}
				else if ($(".modal").attr("data-modify") == "2") {
					var rows_selected = tablex.column(0).checkboxes.selected();
					var ray_id = [];

					// Iterate over all selected checkboxes
					$.each(rows_selected, function (index, rowId) {
						ray_id.push(rowId);
					});
					ray_id.sort();


					var questionarioId = $(".modal").attr("data-id");

					$.ajax({
						url: _URL + '/avasis/removePerguntasFromQuestionario',
						type: "POST",
						data: {
							perguntas: ray_id,
							id_questionario: questionarioId
						},
						dataType: "json"
					}).done(function (resposta) {

						Swal.fire({
							title: "Perguntas removidas do questionario com sucesso!",
							icon: "success",
							confirmButtonText: "OK",
						}).then(() => {
							$("#divQuestionario .modal").modal('hide');
							window.setTimeout(function () {
								callPerguntas(questionarioId);
							}, 300);
						});

					}).fail(function (jqXHR, textStatus) {
						console.log("Request failed: " + textStatus);
						Swal.fire({
							title: "Algo deu errado!",
							icon: "error",
							text: textStatus,
							confirmButtonText: "OK",
						});
					});
				}
			});
		}
	);
});

function callPerguntas(id_questionario) {
	$.post(_URL + "/avasis/showPerguntas",
		{ id_questionario: id_questionario}, function (data) {
			$("#divQuestionario").empty();
			$("#divQuestionario").append(data);
			$(".modal").modal("show");

			$("#showPerguntas #addPergunta").click(function () {
				if ($(".modal").attr("data-modify") == "0") {
					$(".modal").attr("data-modify", "1");

					var ray_perguntas = [];

					if (($("#perguntas")[0]).tBodies[0].rows.length > 1 || (($("#perguntas")[0]).tBodies[0].rows.length == 1 && !$($("#perguntas").find("tr")[1]).hasClass('no-one'))) {
						$(($("#perguntas")[0]).tBodies[0].rows).each(function () {
							ray_perguntas.push($(this).attr("data-id"));
						});
					}

					if (ray_perguntas.length == 0) ray_perguntas = "";

					$.post(_URL + '/avasis/getPerguntasToAddInQuestionario', {
						'perguntasSelected': ray_perguntas
					}, function (data) {
						$("#divQuestionario .modal").modal("hide");

						window.setTimeout(function () {
							$(this).text("Adicionar as perguntas selecionadas");
							$("#removePergunta").addClass("d-none");

							$("#divQuestionario .modal-body").empty();
							$('#divQuestionario .modal-body').append(data);

							tablex = $('#addPerguntasQuestionario').DataTable({
								'columnDefs': [
									{
										'targets': 0,
										'checkboxes': {
											'selectRow': true
										}
									}],
								'select': {
									'style': 'multi'
								},
								language: ptBrDataTable,
								'order': [[1, 'asc']]
							});

							$("#divQuestionario .modal").modal("show");
						}, 500);
					});
				}
				else if ($(".modal").attr("data-modify") == "1") {

					var rows_selected = tablex.column(0).checkboxes.selected();
					var ray_id = [];

					// Iterate over all selected checkboxes
					$.each(rows_selected, function (index, rowId) {
						ray_id.push(rowId);
					});
					ray_id.sort();

					$.ajax({
						url: _URL + "/avasis/putPerguntasToAddInQuestionario",
						type: "POST",
						data: {
							perguntas: ray_id,
							id_questionario: $(".modal").attr("data-id")
						},
						dataType: "json"
					}).done(function (resposta) {
						Swal.fire({
							title: "Perguntas adicionadas ao questionario com sucesso!",
							icon: "success",
							confirmButtonText: "OK",
						}).then(() => {
							$("#divQuestionario .modal").modal('hide');
							window.setTimeout(function () {
								$("#divQuestionario").empty();
								callPerguntas(id_questionario);
							}, 300);
						});

					}).fail(function (jqXHR, textStatus) {
						console.log("Request failed: " + textStatus);
						Swal.fire({
							title: "Algo deu errado!",
							icon: "error",
							text: textStatus,
							confirmButtonText: "OK",
						});
					});
				}
			});

			$("#showPerguntas #removePergunta").click(function () {
				if ($(".modal").attr("data-modify") != "2") {
					if (($("#perguntas")[0]).tBodies[0].rows.length == 1 && $($("#perguntas").find("tr")[1]).hasClass('no-one')) {
						Swal.fire({
							title: "Não há perguntas nesse questionário!",
							icon: "error",
							text: textStatus,
							confirmButtonText: "OK",
						});
					}
					else {
						$(".modal").attr("data-modify", "2");
						$(this).text("Remover as perguntas selecionadas");
						$("#addPergunta").addClass("d-none");

						$.get(_URL + '/avasis/getPerguntasFromQuestionario/' + $(".modal").attr("data-id"), function (data) {
							// $(".modal-body").empty();
							// $('.modal-body').append(data);
							$("#divQuestionario .modal-body").empty();
							$('#divQuestionario .modal-body').append(data);

							tablex = $('#addPerguntasQuestionario').DataTable({
								'columnDefs': [
									{
										'targets': 0,
										'checkboxes': {
											'selectRow': true
										}
									}],
								'select': {
									'style': 'multi'
								},
								'order': [[1, 'asc']],
								'language': ptBrDataTable
							});
						});
					}
				}
				else if ($(".modal").attr("data-modify") == "2") {
					var rows_selected = tablex.column(0).checkboxes.selected();
					var ray_id = [];

					// Iterate over all selected checkboxes
					$.each(rows_selected, function (index, rowId) {
						ray_id.push(rowId);
					});
					ray_id.sort();

					$.ajax({
						url: _URL + '/avasis/removePerguntasFromQuestionario',
						type: "POST",
						data: {
							perguntas: ray_id,
							id_questionario: $(".modal").attr("data-id")
						},
						dataType: "json"
					}).done(function (resposta) {

						Swal.fire({
							title: "Perguntas removidas do questionario com sucesso!",
							icon: "success",
							confirmButtonText: "OK",
						}).then(() => {
							$("#divQuestionario .modal").modal('hide');
							window.setTimeout(function () {
								callPerguntas(id_questionario);
							}, 300);
						});

					}).fail(function (jqXHR, textStatus) {
						console.log("Request failed: " + textStatus);
						Swal.fire({
							title: "Algo deu errado!",
							icon: "error",
							text: textStatus,
							confirmButtonText: "OK",
						});
					});
				}
			});
		}
	);
}

$(".form-questionario").on("click", ".btn-add-questionario", function (e) {
	e.preventDefault();
	$.post(_URL + "/avasis/modalAddQuestionario", function (data) {
		$("#divQuestionario").append(data);
		$("#createQuestionario").modal("show");
	});
});

$("#divQuestionario").on("click", ".btn-create-questionario", function (e) {
	var nome = $("#pergunta-nome").val();

	$.ajax({
		url: _URL + "/avasis/addQuestionario",
		type: "POST",
		data: "nome=" + nome + "&tipo=" + $("#pergunta-tipo").val(),
		dataType: "html",
	})
		.done(function (resposta) {
			if (resposta == "true") {
				Swal.fire({
					title: "Questionario foi adicionado!",
					icon: "success",
					text: textStatus,
					confirmButtonText: "OK",
				}).then(() => {
					window.location.reload();
				});
			}
		})
		.fail(function (jqXHR, textStatus) {
			console.log("Request failed: " + textStatus);
		})
		.always(function () { });
});

$(".form-questionario").on("click", ".btn-edit", function (e) {
	e.preventDefault();
	$.post(
		"editQuestionario.php",
		{
			id: $(this).attr("data-id"),
		},
		function (data) {
			$("#divQuestionario").empty();
			$("#divQuestionario").append(data);
			$("#editQuestionario").modal("show");
		}
	);
});

$("#divQuestionario").on("click", ".btn-edit", function (e) {
	$.ajax({
		url: "App/controller.php",
		type: "POST",
		data:
			"func=editQuestionario&id=" +
			$("#addQuest").attr("data-id") +
			"&nome=" +
			$("#pergunta-nome").val() +
			"&tipo=" +
			$("#pergunta-tipo").val(),
		dataType: "html",
	})
		.done(function (resposta) {
			if (resposta == "true") {
				Swal.fire({
					title: "Questionario foi editado!",
					icon: "success",
					text: textStatus,
					confirmButtonText: "OK",
				}).then(() => {
					window.location.reload();
				});
			} else console.log(2);
		})
		.fail(function (jqXHR, textStatus) {
			console.log("Request failed: " + textStatus);
		})
		.always(function () { });
});

$(".change-status-questionario").change(function () {
	var checked = $(this).prop("checked");
	var ctnQuestionario = $(this).closest("tr");
	var idQuestionario = ctnQuestionario.find("input[type=hidden]").val();
	let button = $(this);

	if (checked) {
		var status = 1;
		var text = "Questionario foi ativado";
		var bool = true;
	} else if (!checked) {
		var status = 0;
		var text = "Questionario foi desativado";
		var bool = false;
	}

	$.ajax({
		url: _URL + "/avasis/changeStatus",
		type: "POST",
		data: {
			table: "questionario",
			id_questionario: idQuestionario,
			status: status,
		},
		dataType: "html",
	})
		.done(function (resposta) {
			$(button).prop("checked", bool);
			Swal.fire({
				title: "Sucesso!",
				icon: "success",
				text: text,
				confirmButtonText: "OK",
			});
		})
		.fail(function (jqXHR, textStatus) {
			console.log("Request failed: " + textStatus);
		})
		.always(function () { });
});

$("#table-questionarios").on("click", ".btn-cancel-questionario", function (e) {
	var line = $(this).closest("tr");
	$(line)
		.find(".form-control")
		.each(function () {
			$(this).prop("disabled", false);
		});

	$(this).addClass("bg-success btn-active-questionario");
	$(this).removeClass("btn-cancel-questionario bg-danger");
	$(this).children("i").removeClass("fas fa-lock");
	$(this).children("i").addClass("fas fa-unlock");
});

$("#table-questionarios").on("click", ".btn-active-questionario", function (e) {
	$(
		"#table-questionarios.input[type=text]:not(.changed), #table-questionarios.input[type=hidden]:not(.changed), #table-questionarios.select:not(.changed)"
	).prop("disabled", true);
	var line = $(this).closest("tr");
	var formControl = $(line).find(".form-control");
	var inputValues = () => {
		var values = [];
		$(formControl).each(function () {
			values.push($(this).val());
		});
		return values;
	};

	$.ajax({
		url: _URL + "/avasis/editQuestionario",
		type: "POST",
		data: {
			tipo: "questionario",
			id: inputValues()[0],
			nome: inputValues()[1],
			date: inputValues()[2],
			tipo_questionario: inputValues()[3],
		},
		dataType: "html",
	})
		.done(function (resposta) {
			console.log(resposta);
			if (resposta) {
				Swal.fire({
					title: "Sucesso!",
					text: "Questionario atualizado com sucesso!",
					icon: "success",
					confirmButtonText: "Ok",
				});
			}
		})
		.fail(function (jqXHR, textStatus) {
			console.log("Request failed: " + textStatus);
		})
		.always(function () { });

	$(line)
		.find(".form-control")
		.each(function () {
			$(this).prop("disabled", true);
		});

	$(this).addClass("btn-cancel-questionario bg-danger");
	$(this).removeClass("bg-success btn-active-questionario");
	$(this).children("i").removeClass("fas fa-unlock");
	$(this).children("i").addClass("fas fa-lock");
});