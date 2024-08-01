$(document).ready(function () {
  const releasedFilter = $("#gleitos-released-filter").selectize({
    maxItems: null,
    plugins: ["remove_button"],
  })[0].selectize;

  const solicitationFilter = $("#gleitos-solicitation-filter").selectize({
    maxItems: null,
    plugins: ["remove_button"],
  })[0].selectize;

  const accomodationFilter = $("#gleitos-accomodation-filter").selectize({
    maxItems: null,
    plugins: ["remove_button"],
  })[0].selectize;
  accomodationFilter.setValue(Object.keys(accomodationFilter.options));

  ptBrDataTable.processing = "<i class='fas fa-sync'></i>";

  $("#filtros").click(function () {
    $(this).closest(".card").CardWidget("toggle");
  });

  $(
    "input:checkbox, #gleitos-released-filter, #gleitos-accomodation-filter, #gleitos-solicitation-filter"
  ).on("change", function () {
    tablePrincipal.draw();
  });

  const tablePrincipal = $("#gleitos-tabela-solicitacoes").DataTable({
    responsive: true,
    processing: true,
    serverSide: true,
    serverMethod: "post",
    ajax: {
      url: _URL + "/gestaoleitos/searchLeitos",
      type: "POST",
      data: function (obj) {
        obj.min = $("#min").val() + " 00:00:00";
        obj.max = $("#max").val() + " 23:59:59";
        obj.name = $(
          "#gleitos-tabela-solicitacoes_wrapper input[type='search']"
        ).val();

        obj.unitLiberate = releasedFilter.getValue();
        obj.accomodation = accomodationFilter.getValue();
        obj.solicitationUnit = solicitationFilter.getValue();

        obj.status = [];

        $("input[name='status']:checked").each(function () {
          obj.status.push($(this).val());
        });
      },
      dataSrc: function(obj){
        console.log(obj);
        if(
            (
              jQuery.isEmptyObject(accomodationFilter.options) &&
              jQuery.isEmptyObject(accomodationFilter.items)
            ) 
            ||
            (
              !jQuery.isEmptyObject(accomodationFilter.options) &&
              jQuery.isEmptyObject(accomodationFilter.items)
            )
        ){
          accomodationFilter.clearOptions();
          accomodationFilter.addOption(obj.acomodacao);
        }

        if(
          (
            jQuery.isEmptyObject(releasedFilter.options) &&
            jQuery.isEmptyObject(releasedFilter.items)
          ) 
          ||
          (
            !jQuery.isEmptyObject(releasedFilter.options) &&
            jQuery.isEmptyObject(releasedFilter.items)
          )
        ){
          releasedFilter.clearOptions();
          releasedFilter.addOption(obj.unidadeReservada);
        }

        if(
          (
            jQuery.isEmptyObject(solicitationFilter.options) &&
            jQuery.isEmptyObject(solicitationFilter.items)
          ) 
          ||
          (
            !jQuery.isEmptyObject(solicitationFilter.options) &&
            jQuery.isEmptyObject(solicitationFilter.items)
          )
        ){
          solicitationFilter.clearOptions();
          solicitationFilter.addOption(obj.unidadeSolicitante);
        }
        
        return obj.data;
      }
    },
    columns: [
      {
        width: "1%",
        data: "idSOLICITACAO",
        searchable: false,
      },
      {
        width: "3%",
        data: "REGISTRO",
        searchable: true,
      },
      {
        width: "20%",
        data: "PACIENTE_NOME",
        searchable: true,
      },
      {
        width: "12%",
        data: "SOLICITACAO_DTHR_REGISTRO",
        searchable: false,
      },
      {
        width: "8%",
        data: "SOLICITACAO_SETOR",
        searchable: true,
      },
      {
        width: "4%",
        data: "SOLICITACAO_ACOMODACAO",
        searchable: true,
      },
      {
        width: "4%",
        data: "SOLICITACAO_STATUS",
        searchable: true,
      },
      {
        width: "6%",
        data: "UNIDADE_LIBERADA",
        searchable: true,
      },
      {
        width: "7%",
        data: "COMPATIBLE",
        searchable: false,
      },
      {
        width: "3%",
        data: "BUTTON",
        searchable: false,
      },
    ],
    language: ptBrDataTable,
    order: [
      [0, "desc"],
      [3, "asc"],
    ],
  });

  $("#example").DataTable({
    columnDefs: [
      {
        orderable: false,
        className: "select-checkbox",
        targets: 0,
      },
    ],
    select: {
      style: "os",
      selector: "td:first-child",
    },
    order: [[1, "asc"]],
  });

  $("#triggerSearch").click(function () {
    $(this).empty();
    $(this).prop("disabled", true);
    $(this).append(
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...'
    );

    var data_inferior = $("#min").val() + " 00:00:00";
    var data_superior = $("#max").val() + " 23:59:59";

    $.ajax({
      url: _URL + "/gestaoleitos/searchLeitos",
      type: "POST",
      data: {
        min: data_inferior,
        max: data_superior,
      },
      dataType: "json",
    })
      .done(function (resposta) {
        console.log(resposta);

        tablePrincipal.clear();
        tablePrincipal.rows.add(resposta);
        tablePrincipal.draw();

        $("#triggerSearch").empty();
        $("#triggerSearch").prop("disabled", false);
        $("#triggerSearch").append("Pesquisar");
      })
      .fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
      });
  });

  $("#min").on("change", function () {
    $("#max").attr("min", $(this).val());
  });
  $("#max").on("change", function () {
    $("#min").attr("max", $(this).val());
  });
});

/* Jquery Methods */

$.fn.dataTable.ext.search.push(function (
  settings,
  searchData,
  index,
  rowData,
  counter
) {
  var positions = $("#gleitos-released-filter")
    .map(function () {
      return this.value;
    })
    .get();
  console.log(positions);

  if (positions.length === 0) {
    return true;
  }

  if (positions.indexOf(searchData[7]) !== -1) {
    return true;
  }

  return false;
});

$.fn.dataTable.ext.search.push(function (
  settings,
  searchData,
  index,
  rowData,
  counter
) {
  var positions = $("#gleitos-solicitation-filter")
    .map(function () {
      return this.value;
    })
    .get();

  if (positions.length === 0) {
    return true;
  }

  if (positions.indexOf(searchData[4]) !== -1) {
    return true;
  }

  return false;
});

$.fn.dataTable.ext.search.push(function (
  settings,
  searchData,
  index,
  rowData,
  counter
) {
  var leitos = $('input:checkbox[name="status"]:checked')
    .map(function () {
      return $(this).val();
    })
    .get();

  if (leitos.length === 0) {
    return true;
  }

  if (
    leitos.indexOf($(rowData["SOLICITACAO_STATUS"]).attr("data-status")) !== -1
  ) {
    return true;
  }

  return false;
});

$.fn.dataTable.ext.search.push(function (
  settings,
  searchData,
  index,
  rowData,
  counter
) {
  var leitos = $("#gleitos-accomodation-filter")
    .map(function () {
      return this.value;
    })
    .get();

  console.log(leitos);
  if (leitos.length === 0) {
    return true;
  }

  if (
    leitos.indexOf($(rowData["SOLICITACAO_ACOMODACAO"]).attr("data-value")) !==
    -1
  ) {
    return true;
  }

  return false;
});

/* Funções globais */
function showModalDescricao(obj) {
  $(obj).attr("disabled", true);
  var resposta = $(obj).attr("data-target");
  $.post(
    _URL + "/gestaoleitos/getModalDescricao",
    {
      id: resposta,
    },
    function (data) {
      $("#divModalDesc").html(data);
      $("#modalDesc").modal("show");
      $("#modalDesc").on("hide.bs.modal", function () {
        $(obj).attr("disabled", false);
        $("#divModalDesc").empty();
      });
    }
  );
}

/* Barra Lateral */

// Permissão para solicitar vagas
$("#btSolicitacao").on("click", function () {
  var permissao_solicitar = null;

  $.ajax({
    url: "App/controller.php",
    type: "POST",
    data: { func: "getUserPerm", permission: "permissao_solicitar" },
    dataType: "html",
  })
    .done(function (resposta) {
      permissao_solicitar = resposta;

      if (permissao_solicitar == "false") {
        swal({
          title: "Usuario não tem permissão para criar solicitações",
          icon: "error",
          button: {
            confirm: true,
          },
        });
      } else {
        $("#body").loadingModal({
          position: "auto",
          text: "Carregando",
          color: "#fff",
          opacity: "0.7",
          backgroudColor: "rgb(0, 0, 0)",
          animation: "doubleBounce",
        });
        window.location.href = "criarSolic.php";
      }
    })
    .fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    });
});

// Permissão para transferir paciente
$("#btTransferencia").on("click", function () {
  var permissao_transferir = null;

  $.ajax({
    url: "App/controller.php",
    type: "POST",
    data: { func: "getUserPerm", permission: "permissao_transferir" },
    dataType: "html",
  })
    .done(function (resposta) {
      permissao_transferir = resposta;
      if (permissao_transferir == "false") {
        swal({
          title: "Usuario não tem permissão para transferir paciente",
          icon: "error",
          button: {
            confirm: true,
          },
        });
      } else {
        $("#body").loadingModal({
          position: "auto",
          text: "Carregando",
          color: "#fff",
          opacity: "0.7",
          backgroudColor: "rgb(0, 0, 0)",
          animation: "doubleBounce",
        });
        window.location.href = "transferencia_regulacao.php";
      }
    })
    .fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    });
});

// Permissão para ir ao painel de leitos
$("#btPainel").on("click", function () {
  var permissao_checklist = null;

  $.ajax({
    url: "App/controller.php",
    type: "POST",
    data: { func: "getUserPerm", permission: "permissao_checklist" },
    dataType: "html",
  })
    .done(function (resposta) {
      permissao_checklist = resposta;
      if (permissao_checklist == "false") {
        swal({
          title: "Usuario não tem permissão para visualizar o painel de leitos",
          icon: "error",
          button: {
            confirm: true,
          },
        });
      } else {
        window.location.href = "check_leitos.php";
      }
    })
    .fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    });
});

// Permissão para ir a pagina de indicadores
$("#btIndicadores").on("click", function () {
  $.ajax({
    url: "App/controller.php",
    type: "POST",
    data: { func: "getUserPerm", permission: "permissao_indicadores" },
    dataType: "html",
  })
    .done(function (resposta) {
      if (resposta == "false") {
        swal({
          title:
            "Usuario não tem permissão para visualizar a pagina de indicadores",
          icon: "error",
          button: {
            confirm: true,
          },
        });
      } else {
        $("#body").loadingModal({
          position: "auto",
          text: "Carregando",
          color: "#fff",
          opacity: "0.7",
          backgroudColor: "rgb(0, 0, 0)",
          animation: "doubleBounce",
        });
        window.location.href = "indicadores.php";
      }
    })
    .fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    });
});
