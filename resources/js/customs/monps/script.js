
$("body").on('click', "button[data-bs-dismiss='modal']", () => {
  var modal = document.getElementById('detailsModal');
  $(modal).modal('hide');
});

// $.fn.dataTable.ext.classes.sPageButton = 'botao-paginacao';;

jQuery.fn.dataTableExt.oSort["customNumber-desc"] = function (x, y) {
  console.log(x > y);
  return x < y;
};

jQuery.fn.dataTableExt.oSort["customNumber-asc"] = function (x, y) {
  console.log(x > y);
  return x > y;
}

jQuery.fn.dataTableExt.oSort["customNumber-pre"] = function (num) {
  if (num === "\u221E") return Infinity;
  var reg = /[+-]?((\d+(\.\d*)?)|\.\d+)([eE][+-]?[0-9]+)?/;
  num = num.match(reg);
  num = num !== null ? parseFloat(num[0]) : false;
  return num;
}

$('#tabelaMonitoramento').DataTable({
  order: [
    [2, "desc"],
    [1, "desc"],
  ],
  language: {
    "decimal": "",
    "emptyTable": "A tabela está vazia.",
    "info": "Exibindo _END_ de um total de _TOTAL_ elementos",
    "infoEmpty": "Exibindo um total de 0 elementos",
    "infoFiltered": "(Filtrando um total de _MAX_ elementos)",
    "infoPostFix": "",
    "thousands": ",",
    "lengthMenu": "Exibir _MENU_ elementos",
    "loadingRecords": "Carregando...",
    "processing": "Processando...",
    "search": "Pesquisar:",
    "zeroRecords": "Sem resultado...",
    "paginate": {
      "first": "Primeiro",
      "last": "Último",
      "next": "Próximo",
      "previous": "Anterior"
    },
  },
  responsive: true,
  aoColumnDefs: [{
    "sType": "customNumber",
    "bSortable": true,
    "aTargets": [1, 2]
  }]
});

$('table').on('click', '.paciente-nome', function () {
  $('#detailsModal').remove();
  let registro = $(this).data('registro');
  let fleBip = $(this).data('bip');
  let fila = $(this).data('fila');
  let tempo_fila = $(this).data('tempo-fila');
  if (registro) {
    $.ajax({
      url: "monps/getModalPaciente",
      type: "POST",
      data: `registro=${registro}&fleBip=${fleBip}&fila=${fila}&tempo_fila=${tempo_fila}`,
      dataType: "html",
      beforeSend: () => {
        $('.loader-div').show();
      }
    }).done(function (responseText) {
      $('main').before(responseText);
      var pacienteInfo = new bootstrap.Modal($('#detailsModal'), {
        keyboard: true,
        backdrop: true
      })
      pacienteInfo.show();
    }).fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    }).always(function () {
      $('#tabelaMonitoramento_filter').find('input[type=search]').prop('disabled', false);
      $('.loader-div').hide();
    });
    $('input').prop('disabled', true);
  }

});

if ($('#monps-paciente-filas').length) {
  setInterval(function () {
    $.ajax({
      url: "monps/getPacienteFilas",
      type: "GET",
    }).done(function (responseText) {
      $('#monps-paciente-filas').html(responseText);
    }).fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    }).always(function () { });
  }, 60000);
}