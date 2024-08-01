
jQuery.validator.addMethod("checkCPF", function (value, element, params) {
  // Validar se é String
  if (typeof value !== 'string') return false;
  
  if (value == '---') return true;

  // Tirar formatação
  cpf = value.replace(/[^\d]+/g, '');

  // Validar se tem tamanho 11 ou se é uma sequência de digitos repetidos
  if (cpf.length !== 11 || !!cpf.match(/(\d)\1{10}/)) return false

  // String para Array
  cpf = cpf.split('')

  const validator = cpf
    // Pegar os últimos 2 digitos de validação
    .filter((digit, index, array) => index >= array.length - 2 && digit)
    // Transformar digitos em números
    .map(el => +el)

  const toValidate = pop => cpf
    // Pegar Array de items para validar
    .filter((digit, index, array) => index < array.length - pop && digit)
    // Transformar digitos em números
    .map(el => +el)

  const rest = (count, pop) => (toValidate(pop)
    // Calcular Soma dos digitos e multiplicar por 10
    .reduce((soma, el, i) => soma + el * (count - i), 0) * 10)
    // Pegar o resto por 11
    % 11
    // transformar de 10 para 0
    % 10

  return !(rest(10, 2) !== validator[0] || rest(11, 1) !== validator[1])

}, 'Por favor, insira um cpf válido.');

jQuery.extend(jQuery.validator.messages, {
  required: "Este campo é obrigatório.",
  email: "Por favor, insira um email válido."
});


jQuery.validator.addMethod("selectize", function (value, element, params) {
  if (value === '') return false;
  return true;
}, 'Este campo é obrigatório.');

jQuery.validator.addMethod("checkDataNascimento", function (value, element) {
  let birthDate = value.split('-');
  birthDate = new Date(birthDate[0], birthDate[1] - 1, birthDate[2]);
  let today = new Date();
  if (birthDate > today) return false;
  let age = today.getFullYear() - birthDate.getFullYear();
  let month = today.getMonth() - birthDate.getMonth();
  if (month < 0 || (month === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  if (age > 125) return false;
  return true;
}, 'Por favor, insira uma data válida.');

var options =  {
  onKeyPress: function(cpf, e, field, options) {
    var masks = ['000.000.000-00'];
    $('#inputCpf').mask(masks[0], options);
  },
  reverse: false
};

$('#inputCpf').mask('000.000.000-00', options);

function objectifyForm(formArray) {
  //serialize data function
  var returnArray = {};
  var otherArray = [];
  var lastName = '';
  for (var i = 0; i < formArray.length; i++) {
    if (formArray[i]['name'].includes('[]')) {
      let formName = formArray[i]['name'].replace('[]', '');
      if (lastName !== formName) {
        lastName = formName;
        otherArray = [];
      }
      otherArray.push(formArray[i]['value']);
      lastName = formName;
      returnArray[formName] = otherArray;
      continue;
    }
    returnArray[formArray[i]['name']] = formArray[i]['value'];
  }
  return returnArray;
}

function isValidCPF(number) {
  number = number.replace('-', '').replace('.', '').replace('.', '');
  let sum;
  let rest;
  sum = 0;
  if ((number.match(/[1{11}2{11}3{11}4{11}5{11}6{11}7{11}8{11}9{11}]/g) || []).length == 11) return false;
  if (number == "00000000000") return false;

  for (i = 1; i <= 9; i++) sum = sum + parseInt(number.substring(i - 1, i)) * (11 - i);
  rest = (sum * 10) % 11;

  if ((rest == 10) || (rest == 11)) rest = 0;
  if (rest != parseInt(number.substring(9, 10))) return false;

  sum = 0;
  for (i = 1; i <= 10; i++) sum = sum + parseInt(number.substring(i - 1, i)) * (12 - i);
  rest = (sum * 10) % 11;

  if ((rest == 10) || (rest == 11)) rest = 0;
  if (rest != parseInt(number.substring(10, 11))) return false;
  return true;
}

var ptBr = {
  firstDayOfWeek: 0,
  weekdays: {
    shorthand: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
    longhand: ['Domingo', 'Segunda', 'TerÃ§a', 'Quarta', 'Quinta', 'Sexta', 'SÃ¡bado'],
  },
  months: {
    shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
    longhand: ['Janeiro', 'Fevereiro', 'MarÃ§o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
  }
};

function formatDate(date, divided = false) {
  var d = new Date(date),
    month = '' + (d.getMonth() + 1),
    day = '' + d.getDate(),
    year = d.getFullYear(),
    hour = '' + d.getHours(),
    minute = '' + d.getMinutes(),
    seconds = '' + d.getSeconds();

  month = month.length < 2 ? month = '0' + month : month;
  day = day.length < 2 ? day = '0' + day : day;
  hour = hour.length < 2 ? hour = '0' + hour : hour;
  minute = minute.length < 2 ? minute = '0' + minute : minute;
  seconds = seconds.length < 2 ? seconds = '0' + seconds : seconds;

  let time = [hour, minute, seconds].join(':')
  let definedDate = [year, month, day].join('-')
  if (divided = true) return {
    'date': definedDate,
    'time': time,
  };
  return [definedDate, time].join(' ');
}
errorToastElMain = document.getElementById('errorToastMain') ?? false;
var errorToastMain = null;
if (errorToastElMain) {
  errorToastMain = new bootstrap.Toast(errorToastElMain) // Returns a Bootstrap toast instance
}
function showError(message, type = 'error') {
  const errorToast = $('#errorToastMain');
  const containerError = $('#errorToastMain').closest('.position-fixed');
  $(containerError).removeClass('d-none');
  console.log()
  switch (type) {
    case 'error':
      errorToast.removeClass('bg-warning bg-success').addClass('bg-danger');
      break;
    case 'success':
      errorToast.removeClass('bg-warning bg-danger').addClass('bg-success');
      break;
    case 'warning':
      errorToast.removeClass('bg-success bg-danger').addClass('bg-warning text-dark');
      break;
    default:
      errorToast.removeClass('bg-warning bg-success').addClass('bg-danger');
      break;
  }

  $('#toast-message').text(message ?? 'Ocorreu um erro inesperado. Por favor, entre em contato com a TI.');
  errorToastMain.show()

  setTimeout(() => {
    $(containerError).addClass('d-none');
  }, 3000);
}


function explodePie(e) {
  if (typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
    e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
  } else {
    e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
  }
  e.chart.render();

}

function showNotification(sistema, message) {
  const NOTFICIATION_ELEMENT = document.getElementById('notification-toast') ?? false;
  if (NOTFICIATION_ELEMENT) {
    NOTFICIATION_ELEMENT.addEventListener('show.bs.toast', function () {
      $(NOTFICIATION_ELEMENT).parent().toggleClass('invisible');
    })
    NOTFICIATION_ELEMENT.addEventListener('hide.bs.toast', function () {
      $(NOTFICIATION_ELEMENT).parent().toggleClass('invisible');
    })
    $('#notification-sistema').text(sistema);
    $('#notification-body').text(message);
    notification_toast = new bootstrap.Toast(NOTFICIATION_ELEMENT) // Returns a Bootstrap toast instance
    notification_toast.show();


  }
}

errorToastEl = document.getElementById('errorToast') ?? false;
var errorToast = null;
if (errorToastEl) {
  errorToast = new bootstrap.Toast(errorToastEl) // Returns a Bootstrap toast instance
  errorToast.show();
}

function formartDateFp(date) {
  let newDate = date.split('/');

  let yearAndTime = newDate[2].split(' ');
  newDate[2] = yearAndTime[0];
  let time = yearAndTime[1];
  if (time && time.split(':').length < 3) time = time + ':00';

  return {
    'date': `${newDate[2]}-${newDate[1]}-${newDate[0]}`,
    'time': time || '00:00:00',
    'fullDate': `${newDate[2]}-${newDate[1]}-${newDate[0]} ${time}`
  };
}

function removeTime(date) {
  let partDate = date.split(' ');
  return partDate[0];
}

function IsJsonString(str) {
  try {
    JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
}

class ChartMonth {
  constructor(id, dataPoints, media, type) {
    this.id = id;
    this.dataPoints = dataPoints;
    this.media = media;
    this.type = type;
  }
  generateChart() {
    let chart = new CanvasJS.Chart(this.id, {
      animationEnabled: true,
      theme: "light2",
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: this.type.title,
        fontSize: 18
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        interval: 1,
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      axisY: {
        titleFontFamily: 'Roboto, sans-serif',
        title: this.type.axisYTitle,
        gridDashType: "shortDot",
        gridThickness: 1,
        stripLines: [
          {
            value: this.media,
            label: `Média - ${this.media}`,
            labelFontColor: "#9E8355",
            color: "#9E8355"
          }
        ],
      },
      legend: {
        cursor: "pointer",
        fontSize: 16,
      },
      toolTip: {
        shared: true
      },
      data: [{
        name: this.type.dataName,
        type: "spline",
        color: "#525f9b",
        showInLegend: true,
        dataPoints: this.dataPoints
      }]
    });

    chart.render();
  }
}

class ChartWeek {
  constructor(id, dataPoints, type) {
    this.id = id;
    this.dataPoints = dataPoints;
    this.type = type;
  }
  generateChart() {
    let chart = new CanvasJS.Chart(this.id, {
      animationEnabled: true,
      theme: 'light2',
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: this.type.title,
        fontSize: 20,
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        gridDashType: "shortDot",
        interval: 1
      },
      axisY: {
        gridDashType: "shortDot",
        titleFontFamily: 'Roboto, sans-serif',
        title: this.type.axisYTitle,
      },
      legend: {
        cursor: "pointer",
        fontSize: 16,
      },
      toolTip: {
        shared: true
      },
      data: [{
        name: this.type.dataName,
        type: "splineArea",
        showInLegend: true,
        dataPoints: this.dataPoints
      }
      ]
    });
    chart.render();
  }
}

class ChartColumn {
  constructor(id, dataPoints, type, theme = 'light2') {
    this.id = id;
    this.dataPoints = dataPoints;
    this.type = type;
    this.theme = theme;
  }
  generateChart() {
    if (!(typeof this.theme === 'string')) {

    };

    let chart = new CanvasJS.Chart(this.id, {
      animationEnabled: true,
      theme: this.theme,
      title: {
        fontFamily: 'Roboto, sans-serif',
        text: this.type.title,
        fontSize: 18
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        gridDashType: "shortDot",
        title: this.type.axisXTitle,
        interval: 1,
        labelAngle: -90,
        labelPlacement: "outsite",
        tickPlacement: "outsite",
        valueFormatString: "####.",
        labelFormatter: function (e) {
          return "";
        }
      },
      axisY: {
        titleFontFamily: 'Roboto, sans-serif',
        title: this.type.axisYTitle,
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      legend: {
        cursor: "pointer",
        fontSize: 16,
      },
      data: [{
        type: "column",
        legendMarkerColor: "grey",
        dataPoints: this.dataPoints
      }]
    });
    chart.render();
  }
}
const ptBrDataTable = {
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
};

class TableDataTable {
  constructor(id, options = null) {
    this.id = id;
    let defaultOptions = {
      language: {
        "decimal": "",
        "emptyTable": "A tabela está vazia.",
        "info": "Exibindo _END_ de um total de _MAX_ elementos",
        "infoEmpty": "Exibindo um total de 0 elementos",
        "infoFiltered": "(Filtrando um total de _TOTAL_ elementos)",
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
      }
    };
    this.options = {
      ...defaultOptions,
      ...options
    }
    return this.generateTable();
  }
  generateTable() {
    let table = $(this.id).DataTable(this.options);
    return table;
  }
}

class SelectSelectize {
  constructor(id, options, emptyValue = true) {
    this.id = id;
    this.options = options;
    this.emptyValue = emptyValue;
    return this.generateSelect();
  }
  generateSelect() {
    let select = $(this.id).selectize(this.options);
    if (select.length) {
      let selectizeSelect = select[0].selectize;
      if (this.emptyValue)
        selectizeSelect.setValue('', false);
      return selectizeSelect;
    }
    return false;
  }
}

class ChartPie {
  constructor(id, dataPoints, type, theme = 'light2') {
    this.id = id;
    this.dataPoints = dataPoints;
    this.type = type;
    this.theme = theme;
  }
  generateChart() {
    let chart = new CanvasJS.Chart(this.id, {
      animationEnabled: true,
      theme: this.theme,
      title: {
        text: this.type.title
      },
      axisX: {
        titleFontFamily: 'Roboto, sans-serif',
        gridDashType: "shortDot",
        title: this.type.axisXTitle,
        interval: 1,
        labelAngle: -90,
        labelPlacement: "outsite",
        tickPlacement: "outsite",
        valueFormatString: "####.",
        labelFormatter: function (e) {
          return "";
        }
      },
      axisY: {
        titleFontFamily: 'Roboto, sans-serif',
        title: this.type.axisYTitle,
        gridDashType: "shortDot",
        gridThickness: 1,
      },
      legend: {
        cursor: "pointer",
        fontSize: 16,
        itemclick: this.explodePie
      },
      data: [{
        type: "pie",
        toolTipContent: "{label}: <strong>{percent}%</strong>",
        indexLabel: "{label} - {percent}%",
        dataPoints: this.dataPoints
      }]
    });
    chart.render();
  }
  explodePie(e) {
    if (typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
      e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
    } else {
      e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
    }
    e.chart.render();

  }
}

function changeButton(button) {
  var line = $(button).closest('tr');
  $(line).find(".form-control").each(function () {
    if ($(button).hasClass('btn-active')) {
      $(this).prop('disabled', true);
    } else {
      $(this).prop('disabled', false);
    }
  });
  $(button).toggleClass('bg-success btn-active').toggleClass('btn-cancel bg-danger');
  $(button).children().remove();
  if ($(button).hasClass('btn-active')) {
    $(button).append('<i class="fas fa-unlock"></i>');
  } else {
    $(button).append('<i class="fas fa-lock"></i>');
  }
  var activeButton = $(button).clone();
  var divParent = $(button).parent();
  divParent.append(activeButton);
  $(button).remove();
}