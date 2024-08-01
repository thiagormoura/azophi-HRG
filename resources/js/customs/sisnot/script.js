$(document).ready(() => {
  var calendarNotificacoesSisNot = flatpickr('#DateSisNot', {
    allowInput: true,
    altInput: true,
    altFormat: "j/m/Y",
    dateFormat: "d/m/Y",
    position: "below center",
    disableMobile: true,
    locale: ptBr,
    mode: "range",
    defaultDate: [new Date(), new Date().fp_incr(-6)],
    onOpen(selectedDates, value, datepicker) {
      datepicker.set('maxDate', false);
      datepicker.set('minDate', false);
    },
    onChange(selectedDates, value, datepicker) {
      if (value != '') {
        datepicker.set('maxDate', selectedDates[0].fp_incr(30));
        datepicker.set('minDate', selectedDates[0].fp_incr(-30));
      }
    },
  });
  var selectizeIncidente = new SelectSelectize('#select-sisnot-incidente', {
    create: false,
    sortField: "text",
    persist: false,
  });
  var selectizeNotificador = new SelectSelectize('#select-sisnot-notificador', {
    create: false,
    sortField: "text",
    persist: false,
  });
  var selectizePacientes = new SelectSelectize('#select-sisnot-pacientes', {
    create: false,
    sortField: "text",
    persist: false,
    maxItems: 1,
    maxOptions: 1000000000000
    // ,onType: (value) => {
    //   if (value.length > 0) {
    //     selectizePacientes.$dropdown.show();
    //   } else {
    //     selectizePacientes.$dropdown.hide();
    //   }
    // },
    // onDropdownOpen: ($dropdown) => {
    //   $dropdown.hide();
    // }

  });

  function SisNotGetNotificacaoDates(startDate, endDate) {
    $.ajax({
      type: "POST",
      data: `firstDate=${startDate}&secondDate=${endDate}`,
      dataType: "html"
    }).done(function (response) {
      $('.row-notificacoes').html(response);
    }).fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    }).always(function () { });
  }

  $('#sisnot-buscar-incidente').on('click', () => {
    let startDate = calendarNotificacoesSisNot.selectedDates[0] ? removeTime(formatDate(calendarNotificacoesSisNot.selectedDates[0]).date) : null;
    let endDate = calendarNotificacoesSisNot.selectedDates[1] ? removeTime(formatDate(calendarNotificacoesSisNot.selectedDates[1]).date) : null;
    SisNotGetNotificacaoDates(startDate, endDate);
  })

  var selectIncidente = $('#select-incidente').selectize({
    maxItems: null,
    plugins: ["remove_button"],
    delimiter: ",",
    persist: false,
    onChange: function (value) {
      if (!value.length) {
        $('.notificacao').show();
        return false;
      }

      $('.notificacao').hide();
      value.map((element, key) => {
        $(`.notificacao[data-incidente='${element}']`).show();
      })

    }
  });

  if (selectIncidente.length) {
    var selectize = selectIncidente[0].selectize;
    selectize.setValue('', false);
  }

  $("#data-sisnot").flatpickr({
    dateFormat: "Y-m-d",
    altInput: true,
    maxDate: new Date().fp_incr(0),
    altFormat: "j \\de F \\de Y",
    position: "below center",
    locale: ptBr
  });

  $("#horario-sisnot").flatpickr({
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    position: "above left",
    time_24hr: true,
    onOpen: function (selectedDates, dateStr, instance) {
      $('.modal').removeAttr('tabindex');
    },
    onClose: function (selectedDates, dateStr, instance) {
      $('.modal').attr('tabindex', -1);
    }
  });

  function handleSubmitAnswerForm(data) {
    $.ajax({
      type: "POST",
      data: data,
      beforeSend: () => {
        $('.loader-div').show();
      }
    }).done(function (response) {
      if (response.success) {
        showError(response.message, 'success');
        setTimeout(function () { window.location.href = response.redirect; }, 1500);
      }
      else if (!response.success) {
        showError(response.message);
      }
    }).fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    }).always(function () {
      $('.loader-div').hide();
    });
  }

  function validAdicionalText(form) {
    let isValid = true;

    const error = $('<label></label>')
      .addClass('error')
      .text('Este campo é obrigatório');

    form.find('.adicional-text').each((key, element) => {
      const inputText = $(element);
      const formGroup = $(inputText.closest('.form-group'));
      const textContainer = inputText.closest('.input-group');
      const checkButton = $(textContainer).find('.adicional-text-radio');
      formGroup.find('label.error').remove();

      if (checkButton.is(':checked') && inputText.val() === '') {
        formGroup.append(error);
        isValid = false;
      }

    });

    if (!isValid)
      showError('Preencha o(s) campo(s) de texto(s) adicional(is)', 'warning');

    return isValid;
  }

  $('.adicional-text-radio').on('change', (e) => {
    const textContainer = $(e.target).closest('.input-group');
    const inputText = $(textContainer).find('.adicional-text');
    const formGroup = $(inputText.closest('.form-group'));

    formGroup.find('label.error').remove();
  });

  $('.form-incidente :radio, .form-incidente :checkbox').on('change', (e) => {
    const textContainer = $(e.target).closest('.form-group');
    const uncheckedInputs = $(textContainer).find('.adicional-text-radio:not(:checked)');

    uncheckedInputs.each((key, element) => {
      const inputText = $(element).closest('.input-group').find('.adicional-text');
      inputText.prop('disabled', true).val('');
    });

    const inputText = $(e.target).is(':checked') && $(e.target).closest('.input-group').find('.adicional-text');
    if (inputText) {
      inputText.prop('disabled', false).focus();
    }

  });

  function validDetailsForm(e) {
    e.preventDefault();
    const form = $(e.target).closest('form');
    const isValid = validAdicionalText(form);

    if (!isValid)
      return false;

    form.validate({
      errorPlacement: function (error, element) {
        const formGroup = $(element.closest('.form-group'));
        formGroup.append(error);
      }
    });

    if (!$(form).valid()) {
      showError('Verifique se todos os campos foram preenchidos corretamente.', 'warning');
      return false;
    }

    const data = [];

    const validInput = (input) => {
      if (input.attr('disabled'))
        return false;

      if (input.is(':checkbox:not(:checked)') || input.is(':radio:not(:checked)'))
        return false;

      if (input.is('.adicional-text-radio'))
        return false;

      if (input.val() === '')
        return false;

      return true;
    }

    $('.form-incidente :input:not(:submit):not(.btn)').each((index, element) => {
      const input = $(element);

      const questionId = input.data('pergunta');
      const answer = input.data('resposta');
      const value = input.val();

      if (!validInput(input))
        return;

      data.push({
        id: questionId,
        answer,
        value
      });

    });

    const dataSerialized = JSON.stringify(data);
    handleSubmitAnswerForm(dataSerialized);
  }
  $('.btn-submit-sisnot').on('click', validDetailsForm);

  // Função para validar o formulário do incidente, remover o esse formulário
  // e adicionar o botão de voltar ao incidente ao formulário de detalhes do incidente
  function validIncidenteForm(e) {
    const form = $(e.target).closest('form');
    const adicionalsTextIsValid = validAdicionalText(form);

    if (!adicionalsTextIsValid)
      return false;

    $(form).validate({
      errorPlacement: function (error, element) {
        const formGroup = $(element.closest('.form-group'));
        formGroup.append(error);
      }
    });

    if (!$(form).valid()) {
      showError('Verifique se todos os campos foram preenchidos corretamente.', 'warning');
      return false;
    }

    $(form).addClass('d-none');
    const infoIncidente = $('.info-incidente');

    const backButton = `<div class="card-tools" style="height: 21px;">
    <button type="button" class="btn text-light btn-back-sisnot py-0" style="font-size: 0.825rem" >
    <i class="fas fa-angle-left"></i> Voltar
    </button>
    </div>`;

    infoIncidente.find('.card-header').append(backButton);

    $('.btn-back-sisnot').on('click', (e) => {
      e.preventDefault();
      infoIncidente
        .animate({ display: 'none', left: '100vw' }, 425)
        .addClass('info-incidente-hide');

      $(form).removeClass('d-none');
      $('.btn-back-sisnot').remove();
    }
    );

    infoIncidente
      .animate({ display: 'block', left: '0' }, 425)
      .removeClass('info-incidente-hide');
  }
  $('.btn-next-sisnot').on('click', validIncidenteForm)

  // Exibe as subquestões no formulário
  function showSubquestions(e) {
    const form = $('.form-incidente');
    const currentInput = $(e.target);
    const subquestionId = currentInput.data('resposta');

    if (subquestionId !== '' && currentInput.is(':checked')) {
      const subquestionContainer = form.find(`[data-resposta='${subquestionId}'].subpergunta-container`);
      const questionId = currentInput.data('pergunta');

      const sameQuestions = form.find(`input:radio[data-pergunta='${questionId}']`);

      sameQuestions.map((index, element) => {
        const subquestionId = $(element).data('resposta');
        const subquestionContainer = form.find(`[data-resposta='${subquestionId}'].subpergunta-container`);

        if (subquestionContainer.length > 0) {
          subquestionContainer.addClass('d-none')
            .find('input:not(.adicional-text)')
            .attr('disabled', true);
        }
      });

      if (subquestionContainer.length > 0) {
        subquestionContainer.removeClass('d-none')
          .find('input:not(.adicional-text)')
          .attr('disabled', false);

        return true;
      }
    }
  }
  // Adiciona o evento de exibir as subquestões no formulário quando o usuário seleciona uma opção
  $('.form-incidente').on('change', "input[type='radio']", showSubquestions);

  function validSelectize(selectize) {
    const formGroup = $($(selectize.$input).closest('.form-group'));

    if (selectize.getValue() === '') {
      if (formGroup.find('.error-selectize').length > 0)
        return false;
      formGroup.append('<label class="error-selectize" style="color:#ff6565; font-weight:normal !important;">Este campo é obrigatório</label>')
      return false;
    } else {
      formGroup.find('.error-selectize').remove();
      return true;
    }
  }

  $('#sisnot-incidente-72h').on('change', (e) => {
    const checkboxState = $(e.target).is(':checked');

    if (checkboxState) {
      selectizePacientes.settings.create = true;
      selectizePacientes.settings.placeholder = "Insira o registro do paciente";
    }
    else {
      selectizePacientes.settings.create = false;
      selectizePacientes.settings.placeholder = "Selecione o paciente do incidente";
    }
    selectizePacientes.updatePlaceholder();
  });


  $('.btn-create-notification').on('click', (e) => {
    e.preventDefault();
    const button = e.target;
    const form = $(button).closest('form');

    const incidenteValid = validSelectize(selectizeIncidente);
    const notificadorValid = validSelectize(selectizeNotificador);
    const pacientesValid = validSelectize(selectizePacientes);

    $(form).validate({
      errorPlacement: function (error, element) {
        const formGroup = $(element.closest('.form-group'));
        formGroup.append(error);
      }
    });

    if (form.valid() && notificadorValid && incidenteValid && pacientesValid)
      form.submit();
  })

  // --------------------------------------------- D A T A P O I N T S ------------------------------------------------------------

  var selectIncidenteDashboard = $('#select-incidente-dashboard').selectize({
    maxItems: null,
    plugins: ["remove_button"],
    delimiter: ",",
    persist: false,
    onChange: function (value) {
    }
  });
  if (selectIncidenteDashboard.length) {
    var selectizeDashBoard = selectIncidenteDashboard[0].selectize;
    selectizeDashBoard.setValue('', false);
  }

  var dashboardCalendarNS = flatpickr('#startDateSisNot-dash', {
    allowInput: true,
    dateFormat: "d/m/Y H:i",
    defaultDate: [new Date().fp_incr(-15), new Date()],
    position: "below center",
    disableMobile: "true",
    locale: ptBr,
    "plugins": [new rangePlugin({ input: "#endDateSisNot-dash" })],
    onReady(selectedDates, value, datepicker) {
    },
    onOpen(selectedDates, value, datepicker) {
      dashboardCalendarNS.set('maxDate', false)
      dashboardCalendarNS.set('minDate', false)
    },
    onChange(selectedDates, value, datepicker) {
      if (value != '') {
        dashboardCalendarNS.set('maxDate', selectedDates[0].fp_incr(60));
        dashboardCalendarNS.set('minDate', selectedDates[0].fp_incr(-60))
      }
    }
  });
  getDashboardSisNot();
  getChartsSisNot();

  $('#search-calendar-sisnot').on('click', () => {
    let incidentes = selectIncidenteDashboard[0].selectize.getValue()
    getDashboardSisNot(incidentes);
    getChartsSisNot(incidentes);
  })

  function getDashboardSisNot(incidentes = []) {
    if ($('#startDateSisNot-dash').length === 0) return;
    let dataInicio = dashboardCalendarNS.selectedDates[0] ? formatDate(dashboardCalendarNS.selectedDates[0]) : null;
    let dataFim = dashboardCalendarNS.selectedDates[1] ? formatDate(dashboardCalendarNS.selectedDates[1]) : null;
    if (dataInicio == null || dataFim == null) return;

    $.ajax({
      url: "dashboard",
      type: "POST",
      data: `dataInicio=${dataInicio.date}&dataFim=${dataFim.date}&incidentes=${incidentes}`,
      dataType: "html",
      async: false,
      beforeSend: () => {
        $('.loader-div').show();
        $('#container-sisnot-dashboard').html();
      }
    }).done((response) => {
      $('#container-sisnot-dashboard').html(response);
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
      $('.loader-div').hide();
    });
  }

  function getChartsSisNot(incidentes = []) {
    if ($('#startDateSisNot-dash').length === 0) return;
    const startDate = formartDateFp($('#startDateSisNot-dash').val());
    const endDate = formartDateFp($('#endDateSisNot-dash').val());
    $.ajax({
      url: "getDataPoints",
      type: "POST",
      data: `dataInicio=${startDate.fullDate}&dataFim=${endDate.fullDate}&incidentes=${incidentes}`,
    }).done((response) => {
      renderChartsSisNot(response);
    }).fail((jqXHR, textStatus) => {
      console.log("Request failed: " + textStatus);
    }).always(() => {
    });
  }

  function renderChartsSisNot(dataPoints) {
    const CHART_MONTH = {
      title: 'NOTIFICAÇÕES POR MÊS',
      dataName: 'Notificações',
      axisYTitle: 'Nº Notificações',
      axisXTitle: null,
    }
    let chartMonth = new ChartMonth('notificacoes-mes', dataPoints['notificacoes-mes'].data, dataPoints['notificacoes-mes'].media, CHART_MONTH);
    chartMonth.generateChart();

    const CHART_WEEK = {
      title: 'NOTIFICAÇÕES POR SEMANA',
      dataName: 'Notificações',
      axisYTitle: 'Nº Notificações',
      axisXTitle: null,
    }
    let chartWeek = new ChartWeek('notificacoes-semana', dataPoints['notificacoes-semana'], CHART_WEEK);
    chartWeek.generateChart();

    const CHART_SECTOR = {
      title: 'NOTIFICAÇÕES POR SETOR INCIDENTAL',
      dataName: 'Notificações',
      axisYTitle: 'Nº Notificações',
      axisXTitle: 'Setor',
    }
    let chartSector = new ChartColumn('notificacoes-setor', dataPoints['notificacoes-setor'], CHART_SECTOR);
    chartSector.generateChart();

    const CHART_RANKING = {
      title: 'INCIDENTES MAIS OCORRIDOS',
      dataName: 'Incidentes',
      axisYTitle: 'Nº Incidentes',
      axisXTitle: 'Incidente',
      lengend: 'Incidentes',
    }
    let chartRanking = new ChartPie('ranking-incidentes', dataPoints['ranking-incidentes'], CHART_RANKING, 'light2');
    chartRanking.generateChart();

  }
});
