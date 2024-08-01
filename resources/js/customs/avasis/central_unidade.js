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

$(document).ready(function () {
  $("#table-unidades").on("change", "input, select", function () {
    var ctnComida = $(this).closest("tr");
    ctnComida.find("input[type=text]").addClass("changed");
    ctnComida.find("input[type=hidden]").addClass("changed");
    ctnComida.find("select").addClass("changed");
  });
  var tablex = null;
  table = null;

  var table = $("#table-unidades").DataTable({
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

  $(".btn-add-unidade").click(createUnidade);
});
$(".btn-add-unidade").click(createUnidade);

function createUnidade(e) {
  e.preventDefault();
  console.log(15);
  $.post(_URL + "/avasis/modalAddUnidade", function (data) {
    $("#divUnidades").append(data);
    $("#createUnidade").modal("show");
  });
}

$("#divUnidades").on("click", ".btn-create-unidade", function (e) {
  var nome = $("#unidade-nome").val();

  $.ajax({
    url: _URL + "/avasis/createUnidade",
    type: "POST",
    data: {
      nome: nome,
    },
    dataType: "json",
  })
    .done(function (resposta) {
      if (resposta.succeded) {
        alert("Questionario foi adicionado!");
        window.location.reload();
      }
    })
    .fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    })
    .always(function () {});
});

$("#table-unidades").on("change", ".change-status-unidade", function () {
  var checked = +$(this).prop("checked");
  var ctnUnidades = $(this).closest("tr");
  var idUnidades = ctnUnidades.find("input[type=hidden]").val();
  // console.log(checked);
  $.ajax({
    url: _URL + "/avasis/changeStatus",
    type: "POST",
    data: {
      type: 3,
      table: "unidades",
      id_unidade: idUnidades,
      status: checked,
    },
    dataType: "html",
  })
    .done(function (resposta) {
      if (resposta) {
        Swal.fire({
          title: "Sucesso!",
          text:
            "Unidade foi " + (checked == 1 ? "ativada" : "desativada") + "!",
          icon: "success",
          confirmButtonText: "Ok",
        });
      } else console.log(resposta);
    })
    .fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    })
    .always(function () {});
});

$("#table-unidades").on("click", ".btn-cancel-unidade", function (e) {
  var line = $(this).closest("tr");
  $(line)
    .find(".form-control")
    .each(function () {
      $(this).prop("disabled", false);
    });
  // $(line).find('.selectpicker').attr('disabled', false).selectpicker('refresh');
  $(this).addClass("bg-success btn-active-unidade");
  $(this).removeClass("btn-cancel-unidade bg-danger");
  $(this).children().remove();
  $(this).append('<i class="fas fa-unlock"></i>');
  var activeButton = $(this).clone();
  var divParent = $(this).parent();
  divParent.append(activeButton);
  $(this).remove();
});

$("#table-unidades").on("click", ".btn-active-unidade", function (e) {
  $(
    "#table-unidades.input[type=text]:not(.changed), #table-unidades.input[type=hidden]:not(.changed), #table-unidades.select:not(.changed)"
  ).prop("disabled", true);
  var line = $(this).closest("tr");
  var unidade_id = $(line).find("input[name='unidade-id[]']").val();
  var unidade_nome = $(line).find("input[name='unidade-nome[]']").val();

  $.ajax({
    url: _URL + "/avasis/editUnidade",
    type: "POST",
    data: {
      tipo: "unidades",
      id: unidade_id || "",
      nome: unidade_nome || "",
    },
    dataType: "html",
  })
    .done(function (resposta) {
      if (resposta) alert("Unidade foi modificada!");
      else console.log(resposta);
    })
    .fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    })
    .always(function () {});

  $(line)
    .find(".form-control")
    .each(function () {
      $(this).prop("disabled", true);
    });

  $(this).addClass("btn-cancel-unidade bg-danger");
  $(this).removeClass("bg-success btn-active-unidade");

  $(this).children().remove();
  $(this).append('<i class="fas fa-lock"></i>');
  var activeButton = $(this).clone();
  var divParent = $(this).parent();
  divParent.append(activeButton);
  $(this).remove();
});
