$(document).ready(() => {
    $('#select-perfis').selectize({
        sortField: 'text'
    });

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success mr-1',
            cancelButton: 'btn btn-danger ml-1'
        },
        buttonsStyling: false
    })

    //Método de validação para os campos de comida e porção
    jQuery.validator.addMethod("checkTwoInputs", function (value, element, params) {
        var divContainer = $(element).closest('form');
        var select = $(element);
        var input = $(select).next()[0];
        var validate = true;
        if (!$(select).val() || !$(input).val()) validate = false;

        return validate;
    }, 'Os campos acima precisam estar preenchidos.');

    //Variaveis do plugin flatpickr calendarCopy = calendario do campo cópia, $flatpickr = calendario global.
    var calendarCopy;
    var $flatpickr = $("#calendar-cardapios").flatpickr({
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j/m/Y",
        position: "below center",
        mode: "range",
        defaultDate: [new Date(), new Date().fp_incr(6)],
        maxRange: 10,
        locale: {
            firstDayOfWeek: 0,
            weekdays: {
                shorthand: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
                longhand: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            },
            months: {
                shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                longhand: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            },
        },
        onReady(selectedDates, value, datepicker) {
            changeCalendar(selectedDates, value, datepicker);
        },
        onOpen(selectedDates, value, datepicker) {
            $flatpickr.set('maxDate', false)
            $flatpickr.set('minDate', false)
            dataFormatada = datepicker.altInput.value.replace('to', 'até');
            datepicker.altInput.value = dataFormatada;
        },
        onChange(selectedDates, value, datepicker) {
            if (value != '') {
                $flatpickr.set('maxDate', selectedDates[0].fp_incr(30));
                $flatpickr.set('minDate', selectedDates[0].fp_incr(-30))
                if (selectedDates.length === 2) {
                    changeCalendar(selectedDates, value, datepicker)
                }
            }
        }
    });

    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear(),
            hour = '' + d.getHours(),

            month = month.length < 2 ? month = '0' + month : month;
        day = day.length < 2 ? day = '0' + day : day;
        hour = hour.length < 2 ? hour = '0' + hour : hour;

        // let time = [hour, minute, seconds].join(':')
        let definedDate = [year, month, day].join('-')
        return [definedDate].join(' ');
    }

    //Altera os cards da página dependendo da data e do perfil.
    function changeCalendar(selectedDates, value, datepicker) {
        let dataFormatada = datepicker.altInput.value.replace('to', 'até');
        datepicker.altInput.value = dataFormatada;
        datepicker.element.value = value.replace('to', ',').replace(/\s/g, '');

        let dataInicio = datepicker.selectedDates[0] ? formatDate(datepicker.selectedDates[0]) : null;
        let dataFim = datepicker.selectedDates[1] ? formatDate(datepicker.selectedDates[1]) : null;
        let idPerfil = $('#select-perfis').val()

        if (idPerfil) {
            $.ajax({
                url: `cardapio_perfil/${idPerfil}`,
                type: "POST",
                data: `firstDate=${dataInicio}&secondDate=${dataFim}`,
                dataType: "html"
            }).done(function (responseText) {
                $('.container-p-c').html(responseText);
            }).fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            }).always(function () { });
        }
    }

    //Atualiza os cards quando alterado o perfil.
    $('.select-perfis').on('change', function () {
        var divContainer = $('.container-p-c');
        $(divContainer).children().remove();
        var today = new Date();
        var lastDayWeek = new Date().fp_incr(6);
        today = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
        lastDayWeek = lastDayWeek.getFullYear() + '-' + (lastDayWeek.getMonth() + 1) + '-' + lastDayWeek.getDate();
        $flatpickr.clear();
        $flatpickr.setDate([today, lastDayWeek]);
        changeCalendar('', today + ' to ' + lastDayWeek, $flatpickr);
    });

    //Chama o modal dos itens cardápios ao clicar em um elemento da lista 
    $('.container-p-c').on('click', '.cardapio-list', function () {
        var idCardapio = $(this).data('id');
        var dateHeader = $(this).closest('.card').data('date');
        var dividedDate = dateHeader.replace(/[^0-9/\.]/g, '').split('/');
        var date = dividedDate[2] + '-' + dividedDate[1] + '-' + dividedDate[0];
        var idPerfil = $('#select-perfis').val();

        $.ajax({
            url: `editItemCardapio/${idCardapio}`,
            type: "POST",
            data: `data=${date}&idPerfil=${idPerfil}`,
            dataType: "html"
        }).done(function (responseText) {
            $('#divItemCardapio').html(responseText);
            // Open modal
            $('#modalItemCardapio').modal('show');
            $("#calendar-copy").flatpickr({
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j/m/Y",
                minDate: new Date().fp_incr(1),
                mode: "multiple",
                position: "above right",
                maxDate: new Date().fp_incr(30),
                conjunction: ", ",
                locale: {
                    firstDayOfWeek: 0,
                    weekdays: {
                        shorthand: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
                        longhand: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
                    },
                    months: {
                        shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                        longhand: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                    },
                },
                onOpen: function (selectedDates, dateStr, instance) {
                    $('#modalItemCardapio').removeAttr('tabindex');
                },
                onClose: function (selectedDates, dateStr, instance) {
                    $('#modalItemCardapio').attr('tabindex', -1);
                }
            });
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });

    });

    //Adiciona a classe change aos inputs e selects recem adicionados a página
    $('#divItemCardapio').on('change', 'input, select', function () {
        $(this).addClass('changed');
        $(this).addClass('not-saved');
    });

    //Salva as alterações feitas nos cardápios (Com validação dos campos)
    $('#divItemCardapio').on('click', '.btn-save', function () {
        let form = $(this).closest('.modal').find('form');
        let fieldsets = $(form).find('fieldset');
        let idCardapio = $(form).find('#idCardapio').val();
        let dataSemana = $(form).find('#dataSemana').val();
        if (fieldsets.length == 1 && $(fieldsets).hasClass('cardapio-preenchido')) {
            let selectIsEmpty = $(fieldsets).find('select').val() == '';
            let numberIsEmpty = true;
            $(fieldsets).find('input[type=number]').each(function () {
                if ($(this).val() !== '') {
                    numberIsEmpty = false;
                }
            });
            if (selectIsEmpty && numberIsEmpty) {
                DeleteItensCardapio(idCardapio, dataSemana)
                return;
            }
        }
        if (ValidarForm(form)) {
            DeleteItensCardapio(idCardapio, dataSemana);
            $(fieldsets).each(function (key, element) {
                var inputs = $(element).serialize();
                SaveItemCardapio(inputs, idCardapio, dataSemana);
            });

            $('.modal').modal('hide');
            $('input:not(.changed), select:not(.changed)').prop('disabled', false);
        }
    });

    function SaveItemCardapio(inputs, idCardapio, dataSemana) {
        const datasSelecionadas = $($flatpickr.input).val().split(',');
        $.ajax({
            url: `insertItemCardapio/${idCardapio}`,
            type: "POST",
            async: false,
            data: `${inputs}&selectedDate=${dataSemana}`,
            dataType: "html"
        }).done(function (responseText) {
            changeCalendar(null, datasSelecionadas[0] + ' to' + datasSelecionadas[1], $flatpickr);
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    }

    function DeleteItensCardapio(idCardapio, dataSemana) {
        const datasSelecionadas = $($flatpickr.input).val().split(',');
        $.ajax({
            url: `deleteItemCardapio/${idCardapio}`,
            type: "POST",
            data: `seletectedDate=${dataSemana}`,
            dataType: "html"
        }).done(function (responseText) {
            changeCalendar(null, datasSelecionadas[0] + ' to' + datasSelecionadas[1], $flatpickr);
            $('.modal').modal('hide');
        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        }).always(function () { });
    }

    function CopyCardapio(fieldsets, dataSemana, idCardapio) {
        const datasSelecionadas = $($flatpickr.input).val().split(',');
        $(dataSemana).each((index, element) => {
            DeleteItensCardapio(idCardapio, element);
            $(fieldsets).each(function () {
                const inputs = $(this).serialize();
                SaveItemCardapio(inputs, idCardapio, element);
            });
        })

        $('.modal').modal('hide');
        changeCalendar(null, datasSelecionadas[0] + ' to' + datasSelecionadas[1], $flatpickr);
    }

    function ValidarForm(form) {
        $(form).validate({
            debug: true,
            errorElement: 'span',
            errorClass: 'text-danger error d-block pl-2 mb-2',
            validClass: 'success d-none',
            rules: {
                "opcao[]": {
                    checkTwoInputs: true
                },
                "quantidade-grupo": {
                    required: true
                }
            },
            messages: {
                "quantidade-grupo": {
                    required: "É necessário inserir um limite de porções"
                }
            },
            errorPlacement: function (error, element) {
                var placement = $(element).closest('.form-group');
                $(placement).after(error)
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

        var selectValue = $(form).find('select').valid();
        var groupQuantity = $(form).find('.group-quantity').valid();

        if (selectValue && groupQuantity) return true;
        return false;
    }

    //Verifica se os campos estão corretos, se há ou não dados novos não salvos e realiza a cópia do cardápio para outro dia.
    $('#divItemCardapio').on('click', 'button.btn-copy', function () {
        const calendarioCopia = $('#calendar-copy');
        const diasSelecionados = $('#calendar-copy').val().split(', ');

        if (diasSelecionados[0] === '') {
            let alertEmpty = $('<span></span>')
                .addClass('text-danger d-block my-2 mt-3 alertCopy')
                .text('É necessário selecionar pelo menos uma data.');
            calendarioCopia.closest('div').after(alertEmpty);

            if ($('.alertCopy').length > 1) $('.alertCopy')[0].remove();
            return;
        }
        $('.alertCopy').remove();

        const modal = $('.modal');
        let inputsNaoSalvos = $(modal).find('fieldset').find('.not-saved');
        if (inputsNaoSalvos.length || modal.hasClass('not-saved')) {
            let alertEmpty = $('<span></span>')
                .addClass('text-danger d-block my-2 mt-3 alertNotSaved')
                .text('É necessário salvar o cardápio antes de realizar uma cópia.');
            calendarioCopia.closest('div').after(alertEmpty);

            if ($('.alertNotSaved').length > 1) $('.alertNotSaved')[0].remove();

            $('input:not(.changed), select:not(.changed)').prop('disabled', false);
            return;
        }
        $('.alertNotSaved').remove();

        const form = modal.find('form');
        const fieldsets = form.find('fieldset');
        const idCardapio = form.find('#idCardapio').val();
        const dataSemana = form.find('#dataSemana').val();
        if (ValidarForm(form)) {
            $.ajax({
                url: `getCheckedCardapiosDays/${idCardapio}`,
                type: "POST",
                data: `diasSelecionados=${diasSelecionados}&dataSemana=${dataSemana}`,
            }).done((responseText) => {
                console.log(responseText);
                if (responseText.success) {
                    showError(responseText.message, 'success');
                    CopyCardapio(fieldsets, diasSelecionados, idCardapio);
                } else {
                    let text = "Os cardápios dos dias: ";
                    let datasPreenchidas = responseText.dates;
                    for (i = 0; i < datasPreenchidas.length; i++) {
                        dataDividida = datasPreenchidas[i].replace(' 00:00:00', '').split('-');
                        text += dataDividida[2] + ', ';
                    }
                    text = text.replace(/,\s*$/, " já possuem itens\nDeseja sobrescrever?");
                    swalWithBootstrapButtons.fire({
                        title: "Tem certeza?",
                        text: text,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Confirmar",
                        cancelButtonText: "Cancelar",
                    }).then((result) => {
                        if (result.value) {
                            CopyCardapio(fieldsets, diasSelecionados, idCardapio);
                            showError('Cópia concluída com sucesso!', 'success');
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            showError('A cópia dos cardápios foi cancelada!', 'success');
                        }
                    })
                }
            }).fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            }).always(function () { });
        }
        $('input:not(.changed), select:not(.changed)').prop('disabled', false);
    });

    //Remove os fieldsets com o input de número de porção e o select de comida.
    $('#divItemCardapio').on('click', '.btn-close-option', function () {
        let modal = $(this).closest('.modal').addClass('not-saved');
        let form = $(this).closest('form')
        let fields = $(form).find('fieldset');
        let thisField = $(this).closest('fieldset');
        if (fields.length > 1) {
            thisField.remove();
        } else if (fields.length == 1) {
            $(thisField).find('input[type=number]').val('');
            $(thisField).find('input[type=text]').val('');
            $(thisField).find('select').val('');
            $(thisField).find('input[type=hidden]').remove();
        }
        for (i = 0; i < $(form).find('fieldset').length; i++) {
            let newValue = "Opção " + (i + 1);
            let currentField = $(form).find('fieldset')[i];
            $(currentField).find('.legend-group').text(newValue);
        }
    });

    //Abre o calendário dos itens cardápios.
    $('#divItemCardapio').on('click', '.input-button-toggle', function () {
        calendarCopy.toggle();
    });

    //Limpa o calendário dos itens cardápios.
    $('#divItemCardapio').on('click', '.input-button-trash', function () {
        calendarCopy.clear();
    });

    //Abre o calendário global.
    $('.calendar-cardapio-content').on('click', '.input-button-toggle', function () {
        $flatpickr.toggle();
    });

    //Função responsavel pelo abre e fecha da sidebar
    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    //Função que reposiciona a sidebar na tela
    $(window).scroll(function () {
        if ($(window).scrollTop() <= 0) $('#sidebar-wrapper').css('top', '3.10rem');
        if ($(window).scrollTop() > 0) $('#sidebar-wrapper').css('top', '0');
    });
});