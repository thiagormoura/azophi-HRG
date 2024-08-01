$(document).ready(function () {

  $('#ouvidoria-modal').on('click', () => {

    $.ajax({
      url: "ouvidoria/getModal",
      type: "GET",
      dataType: "html"
    }).done((responseText) => {
      $('#ouvidoria-container').html(responseText);
      $("#contato").mask("(99) 99999-9999");
      var modalOuvidoria = new bootstrap.Modal(document.getElementById('modalOuvidoria'))
      modalOuvidoria.show()
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
    });

  })

  $('#ouvidoria-container').on('click', '#submit-form-ouvidoria', () => {
    let form = $('#submit-form-ouvidoria').closest('form');

    $(form).validate({
      errorClass: "error text-danger",
      errorElement: "span",
      rules: {
        nome: "required",
        email: {
          required: true,
          email: true
        }
      },
      messages: {
        nome: "É necessário preencher seu nome",
        email: {
          required: "É necessário inserir seu email",
          email: "Seu email precisa ter esse formato: nome@dominio.com"
        }
      },
      submitHandler: function (form) {
        let serializedForm = $(form).serialize();
        $.ajax({
          url: "ouvidoria/getModal",
          type: "POST",
          data: serializedForm,
          dataType: "html",
          beforeSend: () => {
            $('.loader-div').show();
            $('.container-monexm').html();
          }
        }).done(function (responseText) {
          showError('Enviado! Obrigado pelo seu feedback!', 'success');
          $('.modal').modal('hide');
        }).fail(function (jqXHR, textStatus) {
          console.log("Request failed: " + textStatus);
        }).always(function () {
          $('.loader-div').hide();
        });
      },
      highlight: function (element) {
        $(element).removeClass("text-danger");
      }
    });

  })

});