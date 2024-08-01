// container-paciente
$(document).ready(() => {
  function incrementValue(e) {
    e.preventDefault();
    var parent = $(e.target).closest('div');
    var inputNumber = parent.find('input[type=number]');
    var currentVal = parseInt($(inputNumber).val(), 10);

    if (!isNaN(currentVal) && currentVal < $(inputNumber).attr('max')) {
      $(inputNumber).val(currentVal + 1);
      $(inputNumber).addClass('changed');
    }
    checkValue(inputNumber, e.target);

  }

  function decrementValue(e) {
    e.preventDefault();
    var parent = $(e.target).closest('div');
    var inputNumber = parent.find('input[type=number]');
    var currentVal = parseInt($(inputNumber).val(), 10);

    if (!isNaN(currentVal) && currentVal > 0) {
      $(inputNumber).val(currentVal - 1);
    }
    if ($(inputNumber).val() == 0) $(inputNumber).removeClass('changed');
    checkValue(inputNumber, e.target);
  }

  $('.container-cardapio').on('click', '.button-plus', function (e) {
    incrementValue(e);
  });

  $('.container-cardapio').on('click', '.button-minus', function (e) {
    decrementValue(e);
  });

  function checkValue(valor, target) {
    var container = $(target).closest('fieldset');
    var itensContainer = $(target).closest(".container-itens");
    var limitePorcoes = $(itensContainer).data('potion-limite');

    var total = 0;
    var totalAllItens = 0;
    //get total of values of groups
    $(itensContainer).find('input[type=number]').each(function () {
      var thisVal = parseInt($(this).val(), 10);
      if (!isNaN(thisVal))
        total += thisVal;
    });

    //get total of values
    $(container).find('input[type=number]').each(function () {
      var thisVal = parseInt($(this).val(), 10);
      if (!isNaN(thisVal))
        totalAllItens += thisVal;
    });

    // enable submit button
    if (totalAllItens == 0) {
      $(container).find(".submit-button").prop("disabled", true);
    } else {
      $(container).find(".submit-button").prop("disabled", false);
    }
    // limit the inputs numbers
    if (total >= limitePorcoes) {
      $(itensContainer).find('input[type=number]').next().prop('disabled', true);
    } else {
      $(itensContainer).find('input[type=number]').next().prop('disabled', false);
    }
  }

  $('.container-cardapio').on("click", '.btn-solicitar-paciente', function (e) {
    e.preventDefault();

    let fieldPedido = $(this).closest('.fieldset-pedido');
    let containerItens = $(fieldPedido).find('.container-itens');
    let form = $(this).closest('form');
    let fieldInfo = $(form).find('.fieldset-info').serialize();
    let itemAdicional = $(form).find('#item-adicional').val();
    let registro = $(form).find('.fieldset-info').data('registro');


    let limitePorcao = 0;
    $(containerItens).each(function () {
      let thisVal = parseInt($(this).data('potion-limite'), 10);
      if (!isNaN(thisVal))
        limitePorcao += thisVal;
    });

    let data_array = [];
    $(fieldPedido).find(".changed").each(function () {
      let item = {};
      item['idComida'] = $(this).data('id');
      item['value'] = $(this).val();

      data_array.push(item);
    });

    $(form).validate({
      debug: true,
      errorElement: 'span',
      errorClass: 'text-danger text-left error d-block',
      validClass: 'success d-none',
      rules: {
        destinatario: {
          required: true
        },
        destino: {
          required: true
        },
        solicitante: {
          required: true
        }
      },
      messages: {
        destinatario: {
          required: "Insira o destinatário da refeição.",
        },
        destino: {
          required: "Insira o destino da refeição.",
        },
        solicitante: {
          required: "Insira o nome do solicitante da refeição.",
        }
      },
      errorPlacement: function (error, element) {
        var placement = $(element).closest('.form-group');
        $(placement).append(error)
      },
      highlight: function (element, errorClass, validClass) {
        var div = $(element).closest('.form-group');
        $(div).find('.error').addClass(errorClass).removeClass(validClass);
      },
      unhighlight: function (element, errorClass, validClass) {
        var div = $(element).closest('.form-group');
        $(div).find('.error').removeClass(errorClass).addClass(validClass);
      }
    });

    if ($(form).valid()) {
      let pedido = JSON.stringify(data_array);
      $.ajax({
        url: `paciente/${registro}`,
        type: "POST",
        data: `pedido=${pedido}&${fieldInfo}&limite_porcao=${limitePorcao}&item_adicional=${itemAdicional}`,
      }).done(function (responseText) {
        if (responseText.success) {
          showError(responseText.message, 'success');
          setTimeout(function () { window.location.href = responseText.redirect; }, 1500);
          return;
        }
        else {
          showError(responseText.message);
        }
      }).fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
      });
    }
  });

});
