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

var table = $("#table-categorias").DataTable({
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
  ],
});

$(document).ready(function () {
  $("#table-categorias").on("change", "input, select", function () {
    var ctnComida = $(this).closest("tr");
    ctnComida.find("input[type=text]").addClass("changed");
    ctnComida.find("input[type=hidden]").addClass("changed");
    ctnComida.find("select").addClass("changed");
  });
});

$("#table-categorias").on("click", ".btn-cancel-categoria", function (e) {
  var line = $(this).closest("tr");
  $(line)
    .find(".form-control")
    .each(function () {
      $(this).prop("disabled", false);
    });

  $(this).addClass("bg-success btn-active-categoria");
  $(this).removeClass("btn-cancel-categoria bg-danger");
  $(this).children("i").removeClass("fas fa-lock");
  $(this).children("i").addClass("fas fa-unlock");
});

$("#table-categorias").on("click", ".btn-active-categoria", function (e) {
  $(
    "#categorias.input[type=text]:not(.changed), #table-categorias.input[type=hidden]:not(.changed), #table-categorias.select:not(.changed)"
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
    url: _URL + "/avasis/editCategoria",
    type: "POST",
    data: {
      tipo: "categorias",
      id: inputValues()[0],
      nome: inputValues()[1],
    },
    dataType: "html",
  })
    .done(function (resposta) {
      if (resposta) {
        Swal.fire({
          title: "Sucesso!",
          text: "Categoria atualizada com sucesso!",
          icon: "success",
          confirmButtonText: "Ok",
        });
      }
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

  $(this).addClass("btn-cancel-categoria bg-danger");
  $(this).removeClass("bg-success btn-active-categoria");

  $(this).children().remove();
  $(this).append('<i class="fas fa-lock"></i>');
  var activeButton = $(this).clone();
  var divParent = $(this).parent();
  divParent.append(activeButton);
  $(this).remove();
});

$(".change-status-categoria").click(function () {
  var checked = $(this).prop("checked");
  var ctnQuestionario = $(this).closest("tr");
  var idQuestionario = ctnQuestionario.find("input[type=hidden]").val();
  let button = $(this);

  if (checked) {
    var status = 1;
    var text = "Categoria ativada";
    var bool = true;
  } else if (!checked) {
    var status = 0;
    var text = "Categoria desativada";
    var bool = false;
  }

  $.ajax({
    url: _URL + "/avasis/changeStatus",
    type: "POST",
    data: {
      table: "categorias",
      id_categoria: idQuestionario,
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

$(".form-categorias").on("click", ".btn-add-categoria", function (e) {
  e.preventDefault();
  $.post(_URL + "/avasis/modalAddCategoria", function (data) {
    $("#divCategoria").append(data);
    $("#createCategoria").modal("show");
  });
});

$("#divCategoria").on("click", ".btn-create-categoria", function (e) {
  var mainContainer = $(this).closest("#createCategoria");
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
      url: _URL + "/avasis/addCategoria",
      type: "POST",
      data: inputs,
      dataType: "html",
    })
      .done(function (resposta) {
        if (resposta == "true") {
          alert("Categoria foi adicionada!");
          window.location.reload();
        }
      })
      .fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
      })
      .always(function () {});
  }
});
