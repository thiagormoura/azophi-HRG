$('.filter-pedido').on('change', (e) => {
  let input = e.target;
  let cards = $('.containerCard');
  $(cards).each((key, element) => {
    if ($(input).val() == 'Todos') {
      $(element).show();
      return;
    }
    if ($(element).data('categoria') == $(input).val()) {
      $(element).show();
      return;
    }
    $(element).hide();
  })
})

var calendarOrders;
var ptBr = {
  firstDayOfWeek: 0,
  weekdays: {
    shorthand: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
    longhand: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
  },
  months: {
    shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
    longhand: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
  },
};
function updateOrdersScreen() {
  const currentOrders = $('.legendas-pedido').find('.active');

  if (currentOrders.length > 1) {

    $.ajax({
      url: `getPedidos`,
      type: "GET",
      dataType: "html"
    }).done(function (response) {
      $('.row-cards').html(response);
      calendarOrders = createCalendario();
    }).fail(function (jqXHR, textStatus) {
      console.log("Request failed: " + textStatus);
    }).always();

    return;
  }

  const situation = currentOrders.data('situation');

  if (situation === 'entregue' || situation === 'cancelado') {
    $('#inutri-calendario-container')
      .removeClass('d-none').addClass('d-flex');
  } else {
    $('#inutri-calendario-container')
      .removeClass('d-flex').addClass('d-none');
  }

  $.ajax({
    url: `getPedidos/${situation}`,
    type: "GET",
    dataType: "html"
  }).done(function (response) {
    $('.row-cards').html(response);
    calendarOrders = createCalendario();
  }).fail(function (jqXHR, textStatus) {
    console.log("Request failed: " + textStatus);
  }).always();


}
if ($('.legendas-pedido').length > 0)
  setInterval(updateOrdersScreen, 1000 * 60); // 1 minuto


$('.card-header').on("click", ".legendas-pedido", function (e) {
  const button = e.target;

  if ($(button).hasClass('active'))
    return;

  $('.legendas-pedido').find('.active').removeClass('active');
  $(button).addClass('active');

  updateOrdersScreen();
});


$('.row.row-cards').on('click', '.inutri-imprimir-pedido', function (e) {
  const card = $(e.target).closest('.card');
  const pedido = $(card).data('pedido');
  $(e.target).prop('disabled', true);

  $.ajax({
    url: `pedidos/imprimir/${pedido}`,
    type: "POST",
  }).done(function (response) {
    if (response.success) {
      showError(response.message, 'success');
    } else if (!response.success) {
      showError(response.message);
    }
  }).fail(function (jqXHR, textStatus) {
    console.log("Request failed: " + textStatus);
  }).always(function () { });

  setTimeout(() => {
    $(e.target).prop('disabled', false);
  }, 1000);

})

function updatePedido(obj) {
  let card = obj.closest(".card");
  let pedido = $(card).data('pedido');
  let situacao = $(obj).data('situacao');
  let row = $(card).closest('.row-cards');

  $.ajax({
    url: `pedido/update/${pedido}`,
    type: "POST",
    data: `situacao=${situacao}`
  }).done(function (response) {
    if (response.success) {
      showError(response.message, 'success');
      $(card).closest('.containerCard').remove();
      if ($(row).children().length == 0) {
        $(row).html(response.content);
      }
    }
    else {
      showError(response.message);
      $(card).closest('.containerCard').remove();
    }
  }).fail(function (jqXHR, textStatus) {
    console.log("Request failed: " + textStatus);
  }).always(function () { });
}

function cancelPedido(obj) {
  let card = obj.closest(".card");
  let pedido = $(card).data('pedido');

  $.ajax({
    url: `getModalCancel/${pedido}`,
    type: "GET",
    dataType: "html",
  }).done(function (response) {
    $('#containerModal').html(response);
    var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'), {});
    cancelModal.show();
  }).fail(function (jqXHR, textStatus) {
    console.log("Request failed: " + textStatus);
  }).always(function () { });
}

function confirmCancel() {
  let pedido = $('#cancelModal').data('pedido');
  let dataSerialized = $('#cancelModal').find('form').serialize();
  $.ajax({
    url: `cancelPedido/${pedido}`,
    type: "POST",
    data: `${dataSerialized}`,
  }).done(function (response) {
    if (response.success) {
      showError(response.message, 'success');
      let pedidoCard = $("[data-pedido='" + pedido + "']");
      let card = $(pedidoCard).closest('.containerCard');
      let row = $(card).closest('.row-cards');

      if (response.admin) {
        $(card).remove();
      } else if (!response.admin) {
        $(card).find('.card-header').removeClass('bg-pendente').addClass('bg-cancelado');
        $(card).find('button[data-situacao="pendente"]').replaceWith(response.button);
        $(card).find('.car-subtitle.horario').html(`<i>Cancelado - </i><strong>${response['data-cancelamento']} </strong>`);
      }

      if ($(row).children().length == 0) {
        $(row).html(response.content);
      }

      $('#cancelModal').modal('hide');
    }
    else {
      showError(response.message);
    }
  }).fail(function (jqXHR, textStatus) {
    console.log("Request failed: " + textStatus);
  }).always(function () { });

}

function changeCalendar(selectedDates, value, instance) {
  var dataFormatada = instance.altInput.value.replace('to', 'até');
  instance.altInput.value = dataFormatada;
  instance.element.value = value.replace('to', ',').replace(/\s/g, '');

  let situation = $('.legendas-pedido').find('.active').data('situation');

  selectedDates[0] = instance.formatDate(selectedDates[0], 'Y-m-d');
  selectedDates[1] = instance.formatDate(selectedDates[1], 'Y-m-d');

  $.ajax({
    url: `getPedidosByDate/${situation}`,
    type: "POST",
    data: `firstDate=${selectedDates[0]}&secondDate=${selectedDates[1]}`,
    dataType: "html"
  }).done(function (response) {
    $('.row-cards').html(response);
  }).fail(function (jqXHR, textStatus) {
    console.log("Request failed: " + textStatus);
  }).always(function () { });
}

function createCalendario() {
  return $("#calendario-pedidos").flatpickr({
    defaultDate: [new Date().fp_incr(-7), new Date()],
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "j/m/Y",
    position: "below center",
    mode: "range",
    maxRange: 10,
    locale: ptBr,
    onOpen(selectedDates, value, instance) {
      calendarOrders.set('maxDate', false)
      calendarOrders.set('minDate', false)
      dataFormatada = instance.altInput.value.replace('to', 'até');
      instance.altInput.value = dataFormatada;
    },
    onChange(selectedDates, value, instance) {
      if (value != '') {
        calendarOrders.set('maxDate', selectedDates[0].fp_incr(30));
        calendarOrders.set('minDate', selectedDates[0].fp_incr(-30))
        if (selectedDates.length === 2) {
          changeCalendar(selectedDates, value, instance)
        }
      }
    }
  });
}