const tableMedicos = $("#MedicosPlantao").DataTable({
  processing: true,
  serverSide: true,
  serverMethod: "post",
  ajax: {
    url: _URL + "/escalamedica/getMedicosPlantaoTable",
    type: "POST",
    data: function (obj) {
      // data to send to serverSide
    },
    // ,dataSrc: function (teste) {
    //     console.log(teste);
    // }
  },
  columns: [
    {
      width: "10%",
      data: "CRM",
      searchable: false,
    },
    {
      width: "50%",
      data: "MEDICO_NOME",
      searchable: true,
    },
    {
      width: "30%",
      data: "MEDICO_ESPECIALIDADE",
      searchable: true,
    },
    {
      width: "10%",
      data: "BUTTON",
      searchable: false,
    },
  ],
  language: ptBrDataTable,
  order: [[1, "asc"]],
});

$("#options").selectize({
  create: false,
  sortField: "text",
});

$("#gravarMed").click(function (e) {
  // Valor do Select
  var crm = "";
  var nome = "";

  if ($("#nomeMedico").val() && $("#crm").val()) {
    // Digitado no Input
    nome = $("#nomeMedico").val().toUpperCase();
    crm = $("#crm").val();
  } else if ($("#options").val() != null) {
    crm = $("#options").val().trim().split("/")[0];
    nome = $("#options").val().trim().split("/")[1];
  }

  // Especialidade do Medico
  var especialidades = $(".especialidades:checked").val();
  Swal.fire({
    title: "Você tem certeza?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sim",
    cancelButtonText: "Não",
  }).then((value) => {
    if (value.isConfirmed) {
      $.ajax({
        url: _URL + "/escalamedica/insertDoctorInDuty",
        type: "POST",
        data: {
          crm: crm,
          nome: nome,
          especialidades: especialidades,
        },
        cache: false,
      }).done(function (resposta) {
        if (resposta.success) {
          Swal.fire({
            title: resposta.message,
            icon: "success",
            confirmButtonText: "Ok",
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            title: resposta.message,
            icon: "error",
            confirmButtonText: "Ok",
          });
        }
      });
    }
  });
});
