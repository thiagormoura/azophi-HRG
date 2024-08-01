jQuery.extend(jQuery.validator.messages, {
  required: "Este campo é obrigatório",
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

$(document).ready(function () {
  var table = $("#table-perguntas").DataTable({
    responsive: true,
    order: [
      [0, "desc"],
      [1, "asc"],
    ],
    language: {
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
      paginate: {
        first: "Primeiro",
        last: "Ultimo",
        next: "Próximo",
        previous: "Anterior",
      },
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
        orderDataType: "dom-option",
        type: "html-input",
        targets: [2],
        orderData: [2, 0],
      },
    ],
  });

  $("#table-perguntas").on("change", "input, select", function () {
    var ctnComida = $(this).closest("tr");
    ctnComida.find("input[type=text]").addClass("changed");
    ctnComida.find("input[type=hidden]").addClass("changed");
    ctnComida.find("select").addClass("changed");
  });
});

$(".form-perguntas").on("click", ".btn-add-pergunta", function (e) {
  e.preventDefault();
  $.post(_URL + "/avasis/modalAddPergunta", function (data) {
    $("#divPergunta").append(data);
    $("#createPergunta").modal("show");
  });
});

$("#divPergunta").on("click", ".btn-create-pergunta", function (e) {
  var mainContainer = $(this).closest("#createPergunta");
  var form = $(mainContainer).find("form");
  var inputs = $(form).serialize();

  var validator = $(form).validate({
    debug: true,
    errorClass: "error text-danger",
    validClass: "success",
    errorElement: "div",
    highlight: function (element, errorClass, validClass) {
      $(element)
        .parents("div.control-group")
        .addClass("error")
        .removeClass("success");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).parents(".error").removeClass("error").addClass("success");
    },
  });
  if ($(form).valid()) {
    $.ajax({
      url: _URL + "/avasis/addPergunta",
      type: "POST",
      data: inputs,
      dataType: "html",
    })
      .done(function (resposta) {
        if (resposta == "true") {
          alert("Pergunta foi adicionada!");
          window.location.reload();
        } else console.log(resposta);
      })
      .fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
      })
      .always(function () {});
  }
});

$("#table-perguntas").on("change", ".change-status-pergunta", function () {
  var checked = $(this).prop("checked");
  var ctnQuestionario = $(this).closest("tr");
  var idQuestionario = ctnQuestionario.find("input[type=hidden]").val();
  let button = $(this);

  if (checked) {
    var status = 1;
    var text = "Pergunta ativada";
    var bool = true;
  } else if (!checked) {
    var status = 0;
    var text = "Pergunta desativada";
    var bool = false;
  }

  $.ajax({
    url: _URL + "/avasis/changeStatus",
    type: "POST",
    data: {
      table: "perguntas",
      id_pergunta: idQuestionario,
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
    .always(function () {});
});

$("#table-perguntas").on("click", ".btn-cancel-pergunta", function (e) {
  var line = $(this).closest("tr");
  $(line)
    .find(".form-control")
    .each(function () {
      $(this).prop("disabled", false);
    });

  $(this).addClass("bg-success btn-active-pergunta");
  $(this).removeClass("btn-cancel-pergunta bg-danger");
  $(this).children("i").removeClass("fas fa-lock");
  $(this).children("i").addClass("fas fa-unlock");
});

$("#table-perguntas").on("click", ".btn-active-pergunta", function (e) {
  $(
    "#table-perguntas.input[type=text]:not(.changed), #table-perguntas.input[type=hidden]:not(.changed), #table-perguntas.select:not(.changed)"
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
    url: _URL + "/avasis/editPergunta",
    type: "POST",
    data: {
      tipo: "perguntas",
      id: inputValues()[0],
      nome: inputValues()[1],
      categoria: inputValues()[2],
    },
    dataType: "html",
  })
    .done(function (resposta) {
      // console.log(resposta);
      if (resposta) {
        Swal.fire({
          title: "Sucesso!",
          text: "Pergunta atualizada com sucesso!",
          icon: "success",
          confirmButtonText: "Ok",
        });
      } else console.log(resposta);
    })
    .fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    })
    .always(function () {});
  // console.log(inputLine);

  $(line)
    .find(".form-control")
    .each(function () {
      $(this).prop("disabled", true);
    });

  $(this).addClass("btn-cancel-pergunta bg-danger");
  $(this).removeClass("bg-success btn-active-pergunta");

  $(this).children().remove();
  $(this).append('<i class="fas fa-lock"></i>');
  var activeButton = $(this).clone();
  var divParent = $(this).parent();
  divParent.append(activeButton);
  $(this).remove();
});
