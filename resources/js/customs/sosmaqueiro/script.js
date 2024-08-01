var tableMaqueiros = null;
$(document).ready(function () {

  $.fn.dataTable.moment = function ( format, locale ) {
    var types = $.fn.dataTable.ext.type;
 
    // Add type detection
    types.detect.unshift( function ( d ) {
        return moment( d, format, locale, true ).isValid() ?
            'moment-'+format :
            null;
    } );
 
    // Add sorting method - use an integer for the sorting
    types.order[ 'moment-'+format+'-pre' ] = function ( d ) {
        return moment( d, format, locale, true ).unix();
    };
  };

  $.extend($.fn.dataTable.ext.type.order, {
    "status-asc": function (a, b) {
      a = $(a).text().toLowerCase();
      b = $(b).text().toLowerCase();
      if(a == 'aberto'){
        if(b != 'aberto')
          return -1;
        return 0;
      }
      else if(a == 'atendimento'){
        if(b == 'aberto')
          return 1;
        else if(b != 'aberto' && b != 'atendimento')
          return -1;
        return 0;
      }
      else if(a == 'pausado'){
        if(b == 'aberto' || b == 'atendimento')
          return 1;
        else if(!(b == 'aberto' || b == 'atendimento' || b == 'pausado'))
          return -1;
        return 0;
      }
      else if(a == 'finalizado'){
        if(b == 'aberto' || b == 'atendimento' || b == 'pausado')
          return 1;
        else if(!(b == 'aberto' || b == 'atendimento' || b == 'pausado' || b == 'finalizado'))
          return -1;
        return 0;
      }
      else if(a == 'cancelado'){
        if(b != 'cancelado')
          return 1;
        return 0;
      }
    },
    "status-desc": function (a, b) {
      a = $(a).text().toLowerCase();
      b = $(b).text().toLowerCase();
      if(a == 'aberto'){
        if(b != 'aberto')
          return 1;
        return 0;
      }
      else if(a == 'atendimento'){
        if(b == 'aberto')
          return -1;
        else if(b != 'aberto' && b != 'atendimento')
          return 1;
        return 0;
      }
      else if(a == 'pausado'){
        if(b == 'aberto' || b == 'atendimento')
          return -1;
        else if(!(b == 'aberto' || b == 'atendimento' || b == 'pausado'))
          return 1;
        return 0;
      }
      else if(a == 'finalizado'){
        if(b == 'aberto' || b == 'atendimento' || b == 'pausado')
          return -1;
        else if(!(b == 'aberto' || b == 'atendimento' || b == 'pausado' || b == 'finalizado'))
          return 1;
        return 0;
      }
      else if(a == 'cancelado'){
        if(b != 'cancelado')
          return -1;
        return 0;
      }
    }
  });

  // $.fn.dataTable.ext.search.push(function( settings, searchData, index, rowData, counter ) {
  //   var dateMixed = searchData[1];
  //   let textCreator = $(dateMixed).children().first().text().trim().split(' - ')[1];
  //   let spansText = $(dateMixed).children().last().text().trim().split(' - ')[1];
  //   var valueSearched = $("#sosmaqueiro-chamados-tabela_filter input[type='search']").val();
    
  //   if(valueSearched == '')
  //     return true;

  //   if(textCreator.includes(valueSearched) || spansText.includes(valueSearched))
  //     return true;

  //   return false;
  // });
  $.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss', 'pt-br');
  tableMaqueiros = $("#sosmaqueiro-chamados-tabela").DataTable({
    language: ptBrDataTable,
    processing: true,
    ajax: {
      url: _URL + "/sosmaqueiros/reloadTable",
      type: "POST"
    },
    sDom: "<'row' <'d-flex justify-content-center align-items-sm-center col-sm-4' l >  <'d-flex justify-content-center align-items-sm-center col-sm-4' f > <'text-center col-sm-4 abrir-chamado-div'> >tip",
    order: [
      [0, "desc"]
    ],
    columns:[
      {
        data: "id",
        searchable: false,
        orderable: true,
        orderDataType: "dom-text"
      },
      {
        data: "data-atualizacao",
        searchable: true,
        orderable: true,
        type: 'moment-date',
        render: $.fn.dataTable.render.moment('YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY HH:mm:ss', 'pt-br')
      },
      {
        data: "maqueiro-selecionado",
        searchable: true,
        orderable: true,
        orderDataType: "dom-text"
      },
      {
        data: "paciente-local",
        searchable: true,
        orderable: false
      },
      {
        data: "setor-solicitante",
        searchable: true,
        orderable: true,
      },
      {
        data: "uso-destino",
        searchable: true,
        orderable: false,
      },
      {
        data: "recurso-obs",
        searchable: true,
        orderable: false,
      },
      {
        data: "status",
        searchable: false,
        orderable: true,
        type: 'status'
      },
      {
        data: "buttons",
        searchable: false,
        orderable: false
      }
    ],
    drawCallback: function (settings) {
      $("#sosmaqueiro-chamados-tabela tbody tr").each(function() {
        var td = $(this).children().last().children();
        for (let index = 0; index < 3; index++) {
          try {
            if($._data($(td)[index], "events") == undefined)
              $($(td)[index]).click(acoes);
          } catch (error) {
            $($(td)[index]).click(acoes);
          }
        }
      });
      if($(".card .overlay").length != 0)
        $(".card .overlay").remove();
    }
  });

  // tableMaqueiros.language.processing = "<div class='overlay'><i class='fas fa-2x fa-sync-alt fa-spin'></i></div>";

  $('#sosmaqueiro-chamados-tabela').on('click', '.sosmaqueiro-buttons a', acoes);

  $.ajax({
    url: _URL + "/sosmaqueiros/getAbrirChamadoButton",
    type: "GET",
  }).done(function (response){
    $(".abrir-chamado-div").html(response);
    $('#sosmaqueiro-abrir-chamado').on('click', (e) => {
      $.ajax({
        url: "sosmaqueiro/solicitar",
        type: "GET",
        async: false,
        dataType: "html"
      }).done(function (response) {
        $('#sosmaqueiro-container-modal').html(response);
        new SelectSelectize('#sosmaqueiro-select-paciente', {
          create: false,
          sortField: "text",
          persist: false,
        });
        new SelectSelectize('#sosmaqueiro-select-solicitante', {
          create: false,
          sortField: "text",
          persist: false,
        });
        new SelectSelectize('#sosmaqueiro-select-destino', {
          create: false,
          sortField: "text",
          persist: false,
        });
        $('#sosmaqueiro-solicitar-modal').modal('show');
  
      }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
      }).always(function () {
  
      });
    });
  }).fail(function (jqXHR, textStatus) {
    console.log(textStatus);
  });

  var selectTransfer = $('#sosmaqueiro-select-transferencia').length > 0 && new SelectSelectize('#sosmaqueiro-select-transferencia', {
    create: false,
    sortField: "text",
    persist: false,
  });

  function updateSolicitations(data) {
    const container = $(`.sosmaqueiro-solicitacao[data-id="${data.id}"]`);
    const span = $('<span></span>').addClass(`badge ${data.status_color}`).text(data.status);
    $(container).data('status', data.status);
    $(container).find('td span.sosmaqueiro-acao').html(data.action);
    $(container).find('td.sosmaqueiro-status').html(span);
    $(container).find('td.sosmaqueiro-buttons').html(data.buttons);
  }

  $('#sosmaqueiro-container-modal').on('click', '#sosmaqueiro-solicitar-maqueiro', (e) => {
    let modal = $(e.target).closest('#sosmaqueiro-solicitar-modal');
    let form = $(modal).find('form');
    $(form).validate({
      ignore: ':hidden:not([class~=selectized]),:hidden > .selectized, .selectize-control .selectize-input input',
      rules: {
        paciente: {
          selectize: true,
        },
        setor_solicitante: {
          selectize: true,
        },
        destino: {
          selectize: true,
        },
        'recurso[]': {
          required: true,
        },
        transporte: {
          required: true,
        }
      },
      errorPlacement: function (error, element) {
        var placement = $(element).closest('.form-group');
        $(placement).append(error)
      },
      highlight: function (element, errorClass, validClass) {
        var div = $(element).closest('.form-group');
        $(div).next('.error').addClass(errorClass).removeClass(validClass);
      },
      unhighlight: function (element, errorClass, validClass) {
        var div = $(element).closest('.form-group');
        $(div).next('.error').removeClass(errorClass).addClass(validClass);
      }
    });
    const serializedForm = $(form).serialize();

    $.ajax({
      url: "sosmaqueiro/solicitar",
      type: "POST",
      dataType: "json",
      data: serializedForm,
    }).done(function (response) {
      if (response.success) {
        showError(response.message, 'success');
        $('#sosmaqueiro-solicitar-modal').modal('hide').remove();
      } else {
        showError(response.message);
      }
    }).fail(function (jqXHR, textStatus) {
      showError('Ocorreu um error inesperado, por favor, contate a TI.');
    });
  });

  function acoes(e){
      const action = $(e.target).closest('a').data('acao');
      const solicitationId = action != 'info' ? $($(e.target).parents()[2]).find('.id').data('id')
        : $(e.target).closest('a').data('id');

      if (action === 'finalizar') {
        Swal.fire({
          title: "Você realmente deseja finalizar a solicitação?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sim",
          cancelButtonText: "Não"
        }).then((response) => {
          if(response.isConfirmed){
            $.ajax({
              url: `sosmaqueiro/atender/${solicitationId}`,
              type: "POST",
              data: {
                action: action,
              },
              dataType: "json",
              async: false
            }).done(function (response) {
              if (response.success) {
                showError(response.message, 'success');
                if($(".card .overlay").length == 0)
                  $(".card").append('<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>');
                tableMaqueiros.ajax.reload(null, false);
              } else {
                showError(response.message);
              }
            }).fail(function (jqXHR, textStatus) {
              console.log("Request failed: " + textStatus);
            });
          }
          return;
        });
      }
      else{
        if(action === 'aceitar'){
          $.ajax({
            url: `sosmaqueiro/atender/${solicitationId}`,
            type: "POST",
            data: {
              action: action,
            },
            dataType: "json",
            async: false
          }).done(function (response) {
            if (response.success) {
              showError(response.message, 'success');
              if($(".card .overlay").length == 0)
                  $(".card").append('<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>');
              tableMaqueiros.ajax.reload(null, false);
              return;
            } else {
              if(response.code === undefined){
                showError(response.message);
                return;
              }
              else{
                getBearerTransfer().then((response) => {
                  $.ajax({
                    url: `sosmaqueiro/atender/${solicitationId}`,
                    type: "POST",
                    data: {
                      action: 'atribuir',
                      user: response.user
                    },
                    dataType: "json",
                    async: false
                  }).done(function (response) {
                    if (response.success) {
                      showError(response.message, 'success');

                      $.ajax({
                        url: `sosmaqueiro/atender/${solicitationId}`,
                        type: "POST",
                        data: {
                          action: action,
                        },
                        dataType: "json",
                        async: false
                      }).done(function (response) {
                        if (response.success) {
                          showError(response.message, 'success');
                          if($(".card .overlay").length == 0)
                            $(".card").append('<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>');
                          tableMaqueiros.ajax.reload(null, false);
                          return;
                        } 
                        else 
                          showError(response.message);
                        
                      }).fail(function (jqXHR, textStatus) {
                        console.log("Request failed: " + textStatus);
                      });

                    } else {
                      showError(response.message);
                    }
                  }).fail(function (jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                  });
                }).catch((error) => error);
              }
            }
          }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
          });

          return;
        }

        if (action === 'transferir') {
          getBearerTransfer().then((response) => {
            $.ajax({
              url: `sosmaqueiro/atender/${solicitationId}`,
              type: "POST",
              data: {
                action: action,
                user: response.user
              },
              dataType: "json"
            }).done(function (response) {
              if (response.success) {
                showError(response.message, 'success');
                if($(".card .overlay").length == 0)
                  $(".card").append('<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>');
                tableMaqueiros.ajax.reload(null, false);
              } else {
                showError(response.message);
              }
            }).fail(function (jqXHR, textStatus) {
              console.log("Request failed: " + textStatus);
            }).always(function () {
    
            });
          }).catch((error) => error);
    
          return;
        }
    
        if (action === 'cancelar' || action === 'pausar') {
          getJustificationSolic(action).then((response) => {
            $.ajax({
              url: `sosmaqueiro/atender/${solicitationId}`,
              type: "POST",
              data: {
                action: action,
                justification: response.justification
              },
              dataType: "json"
            }).done(function (response) {
              if (response.success) {
                showError(response.message, 'success');
                if($(".card .overlay").length == 0)
                  $(".card").append('<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>');
                tableMaqueiros.ajax.reload(null, false);
              } else {
                showError(response.message);
              }
            }).fail(function (jqXHR, textStatus) {
              console.log("Request failed: " + textStatus);
            });
    
          }).catch((error) => error);
    
          return;
        }
    
        if (action === 'info') {
          const type = $(e.target).closest('a').data('tipo');
          $.ajax({
            url: `sosmaqueiro/atender/${solicitationId}/${type}`,
            type: "GET",
          }).done(function (response) {
            console.log(response);
            $('#sosmaqueiro-container-modal').html(response);
            $('#sosmaqueiro-info-modal').modal('show');
          }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
          });
    
          return;
        }
  
        $.ajax({
          url: `sosmaqueiro/atender/${solicitationId}`,
          type: "POST",
          data: {
            action: action,
          },
          dataType: "json",
          async: false
        }).done(function (response) {
          if (response.success) {
            showError(response.message, 'success');
            tableMaqueiros.ajax.reload(null, false);
          } else {
            showError(response.message);
          }
        }).fail(function (jqXHR, textStatus) {
          console.log("Request failed: " + textStatus);
        });
      }
  }

  async function getBearerTransfer() {
    // Realizar uma promise que espera a resposta do usuário, caso ele cancele a transferencia retornar o catch
    // caso ele escolha um usuário e confirme, retorna o then
    return new Promise(function (resolve, reject) {
      $('#sosmaqueiro-transferir-modal').modal('show');
      console.log('a');
      selectTransfer.setValue('', false);
      $('#sosmaqueiro-transferir-modal .btn-transferir').click(function () {
        if (selectTransfer.getValue() == '') {
          showError('É necessário escolher um maqueiro para realizar a transferência.');
          return;
        }

        $('#sosmaqueiro-transferir-modal').modal('hide');

        resolve({
          user: selectTransfer.getValue()
        });
      });

      $('#sosmaqueiro-transferir-modal .btn-cancelar').click(() => reject(false));
    });
  };

  async function getJustificationSolic(action) {
    // Realizar uma promise que espera a resposta do usuário, caso ele cancele a transferencia retornar o catch
    let operation = 'do cancelamento';

    if (action === 'pausar')
      operation = 'da pausa';

    // caso ele escolha um usuário e confirme, retorna o then
    return new Promise(function (resolve, reject) {
      $('#sosmaqueiro-motivo-modal').modal('show');
      $('#sosmaqueiro-motivo-modalLabel').text(`Informe o motivo ${operation}`);
      $('#sosmaqueiro-motivo').val('');
      $('#sosmaqueiro-motivo-label').text(`Motivo ${operation}`);

      $('#sosmaqueiro-motivo-modal .btn-confirmar').click(function () {
        if ($('#sosmaqueiro-motivo').val() == '') {
          showError(`É necessário inserir o motivo ${operation}.`)
          return;
        }
        $('#sosmaqueiro-motivo-modal').modal('hide');
        resolve({
          justification: $('#sosmaqueiro-motivo').val()
        });
      });

      $('#sosmaqueiro-motivo-modal .btn-cancelar').click(() => reject(false));
    });

  };

  function ordenarSolicitacoesMaqueiro() {
    const solicitationsTable = $('#sosmaqueiro-chamados-tabela tr.sosmaqueiro-solicitacao');

    const statusType = {
      aberto: 0,
      atendimento: 1,
      pausado: 2,
      finalizado: 3,
      cancelado: 4
    }
    const tableSorted = solicitationsTable.sort(function (a, b) {
      const statusElemA = $(a).data('status').toLowerCase();
      const statusElemB = $(b).data('status').toLowerCase();
      return statusType[statusElemA] - statusType[statusElemB];
    });

    $('#sosmaqueiro-chamados-tabela tbody').html(tableSorted);
  }
  ordenarSolicitacoesMaqueiro();
});