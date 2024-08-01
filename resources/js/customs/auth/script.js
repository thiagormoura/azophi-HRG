$(document).ready(() => {
  $('#paciente-solicitar-acesso').on('click', (e) => {
    e.preventDefault();
    let form = $(document).find('form');
    let cpf = $(document).find('.cpf').val();
    $(form).validate({
      rules: {
        cpf: {
          checkCPF: true,
        },
        dataNascimento: {
          checkDataNascimento: true,
        },
        registro: { number: true }
      },
      messages: {
        email: {
          email: "Por favor, insira um email válido."
        },
        registro: {
          number: "Por favor, insira um registro válido."
        },
      }
    });

    if ($(form).valid()) {
      $(form).submit();
    }
  });
});