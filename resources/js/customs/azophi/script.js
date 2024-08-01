$(document).ready(() => {

    if ($('#azophi-sticky').length > 0) {
        const iObserver = new IntersectionObserver(

            ([e]) => {
                $(e.target).find('.container').toggleClass('d-none');
            }, { threshold: [1] }
        );
        const element = document.getElementById('azophi-sticky');
        iObserver.observe(element);
    }

    $('#azophi-select-convenios').selectize({
        maxItems: null,
        plugins: ["remove_button"],
        delimiter: ",",
        persist: false,
    });
    if ($('#azophi-select-convenios').length)
        $('#azophi-select-convenios').selectize()[0].selectize.setValue('', false);

    $('#azophi-all-convenios').on('change', (e) => {
        const selectize = $('#azophi-select-convenios').selectize()[0].selectize;
        const checkbox = $(e.target);
        let optKeys = Object.keys(selectize.options);
        if (checkbox.is(':checked')) {
            optKeys.forEach((element) => {
                selectize.addItem(element);
            });
            return true;
        }

        optKeys.forEach(function (element) {
            selectize.removeItem(element);
        });
        return false;
    });

    $('.modal-setor-paciente').on('click', modalSetor);

    async function modalSetor(e){
        const button = $(e.target);

        const setor = button.data('setor').trim();

        const selectize = $('#azophi-select-convenios')[0].selectize;
        const selectizeValue = selectize.items;
        let convenios = null;

        if (selectizeValue.length > 0)
            convenios = selectizeValue;
        else
            convenios = Object.keys(selectize.options);
    

        $.ajax({
            url: `azophi/setor/${setor}`,
            type: 'GET',
            data: {
                convenios: convenios
            },
            success: (data) => {
                const page = data;
                $('body').append(page);
                $('#sector-azophi').modal('show');

                $('#sector-azophi').on('hidden.bs.modal', () => {
                    $('#sector-azophi').remove();
                });
            },
            error: (jqXHR, textStatus) => {
                console.log("Request failed: " + textStatus);
            }
        });

    }

    $('#azophi-search-convenios').on('click', function () {
        const selectize = $('#azophi-select-convenios')[0].selectize;
        const selectizeValue = selectize.items;
        let convenios = null;

        if (selectizeValue.length > 0)
            convenios = selectizeValue;
        else
            convenios = Object.keys(selectize.options);

        $.ajax({
            url: "azophi/searchConvenios",
            type: "POST",
            data: `convenios=${convenios}`,
            beforeSend: () => {
                $('.loader-div').show();
                $('.container-monexm').html();
            }
        })
            .done(response => {
                renderCharts(response.graficos);
                reloadCards(response.cards);
            })
            .fail((jqXHR, textStatus) => console.log("Request failed: " + textStatus))
            .always(() => {
                $('.loader-div').hide();
            });
    })

    // Realiza a busca no controller por requisição asíncrona
    const getCharts = async () => {

        $.ajax({
            url: "azophi/getDataPoints",
            type: "GET",
            beforeSend: () => {
                $('.loader-div').show();
                $('.container-monexm').html();
            }
        })
        .done(response => {
            renderCharts(response.graficos);
            // reloadCards(response.cards);
        })
            .fail((jqXHR, textStatus) => console.log("Request failed: " + textStatus))
            .always(() => {
                $('.loader-div').hide();
            });
    }
    if ($('#azophi-internacao-convenios').length > 0)
        getCharts();


    const azophiData = {};

    // Objeto que direciona os gráficos com suas configurações e renderizações
    const chart = {
        internacaoConvenios: (data) => {
            if (!data) return;

            const optionsInternacaoConvenios = {
                animationEnabled: true,
                theme: 'light2',
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    gridDashType: "shortDot",
                    interval: 1,
                    labelAngle: -90,
                    labelPlacement: "outsite",
                    tickPlacement: "outsite",
                    valueFormatString: "####.",

                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                    gridDashType: "shortDot",
                    gridThickness: 1,
                },
                legend: {
                    cursor: "pointer",
                    fontSize: 14,
                    horizontalAlign: "left",
                    verticalAlign: "center",
                    itemclick: (e) => {
                        if (typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
                            e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
                        } else {
                            e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
                        }
                        e.chart.render();
                    },
                    itemTextFormatter: function (e) {
                        let posicao = 0;
                        e.dataSeries.dataPoints.find(function (convenio, index) {
                            if (e.dataPoint.name === convenio.name) {
                                posicao = index;
                                return true;
                            }
                        });
                        return `(${++posicao}) ${e.dataPoint.name}`;
                    }
                },
                toolTip: {
                    contentFormatter: function (e) {
                        const data = e.entries[0].dataPoint;
                        let toolTip = `<strong style='color: ${e.chart.toolTip.borderColor}'>${data.name} - ${data.label}%</strong></br>`
                        const tipos = data.tipo;
                        for (let tipo in tipos) {
                            toolTip += `<strong style='color: ${e.chart.toolTip.borderColor}'>${tipo}:</strong> ${tipos[tipo]}</br>`;
                        }
                        return toolTip;
                    }
                },
                data: [{
                    type: "pie",
                    showInLegend: true,
                    indexLabelFontSize: 12,
                    indexLabelPlacement: "outside",
                    indexLabel: "{name} {label}%",
                    dataPoints: data
                }]
            }
            const generatedChart = new CanvasJS.Chart("azophi-internacao-convenios", optionsInternacaoConvenios);
            generatedChart.render();
        },
        pacientesConvenios: (data) => {
            if (!data) return;
            const optionsPacientesConvenios = {
                animationEnabled: true,
                theme: 'light2',
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    gridDashType: "shortDot",
                    interval: 1,
                    labelAngle: -90,
                    labelPlacement: "outsite",
                    tickPlacement: "outsite",
                    valueFormatString: "####.",

                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                    gridDashType: "shortDot",
                    gridThickness: 1,
                },
                legend: {
                    cursor: "pointer",
                    fontSize: 14,
                    horizontalAlign: "left",
                    verticalAlign: "center",
                    itemclick: (e) => {
                        if (typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
                            e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
                        } else {
                            e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
                        }
                        e.chart.render();
                    },
                    itemTextFormatter: function (e) {
                        let posicao = 0;
                        e.dataSeries.dataPoints.find(function (convenio, index) {
                            if (e.dataPoint.name === convenio.name) {
                                posicao = index;
                                return true;
                            }
                        });
                        return `(${++posicao}) ${e.dataPoint.name}`;
                    }
                },
                toolTip: {
                    contentFormatter: function (e) {
                        const data = e.entries[0].dataPoint;
                        let toolTip = `<strong style='color: ${e.chart.toolTip.borderColor}'>${data.name} - ${data.label}%</strong></br>`
                        toolTip += `<strong style='color: ${e.chart.toolTip.borderColor}'>Pacientes:</strong> ${data.y}</br>`;
                        return toolTip;
                    }
                },
                data: [{
                    type: "pie",
                    showInLegend: true,
                    indexLabelFontSize: 12,
                    indexLabelPlacement: "outside",
                    indexLabel: "{name} {label}%",
                    dataPoints: data
                }]
            }
            const generatedChart = new CanvasJS.Chart("azophi-pacientes-convenios", optionsPacientesConvenios);
            generatedChart.render();
        },
        ocupacaoGeral: (data) => {
            if (!data)
                return;
            const optionsOcupacaoGeral = {
                animationEnabled: true,
                theme: 'light2',
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    crosshair: {
                        enabled: true,
                        snapToDataPoint: true,
                        labelBackgroundColor: '#66b99b',
                        color: "#66b99b",
                    }
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                    crosshair: {
                        enabled: true,
                        labelBackgroundColor: '#66b99b',
                        color: "#66b99b",
                    }
                },
                legend: {
                    cursor: "pointer",
                    fontSize: 14,
                    horizontalAlign: "center",
                    verticalAlign: "bottom",
                },
                toolTip: {
                    shared: true,
                },
                data: [{
                    type: "line",
                    showInLegend: true,
                    name: "Pacientes",
                    markerType: "square",
                    color: "#1c2e5f",
                    dataPoints: data['pacientes-atual']
                },
                {
                    type: "line",
                    showInLegend: true,
                    name: "Pico/Dia",
                    lineDashType: "dash",
                    dataPoints: data['pacientes-pico']
                }]
            }
            const generatedChart = new CanvasJS.Chart("azophi-ocupacao-geral", optionsOcupacaoGeral);
            generatedChart.render();
        },
        prontoAtendimentoConvenios: (data) => {
            if (!data)
                return;

            Object.entries(data).map((content) => {
                
                const prontoAtendimentoConvOptions = {
                    animationEnabled: true,
                    theme: 'light2',
                    axisX: {
                        titleFontFamily: 'Roboto, sans-serif',
                        gridDashType: "shortDot",
                        interval: 1,
                        labelPlacement: "outsite",
                        tickPlacement: "outsite",
                        valueFormatString: "####.",

                    },
                    axisY: {
                        titleFontFamily: 'Roboto, sans-serif',
                        gridDashType: "shortDot",
                        gridThickness: 1,
                    },
                    legend: {
                        cursor: "pointer",
                        fontSize: 10,
                        horizontalAlign: "left",
                        verticalAlign: "center",
                        itemclick: (e) => {
                            if (typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
                                e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
                            } else {
                                e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
                            }
                            e.chart.render();
                        }

                    },
                    toolTip: {
                        contentFormatter: function (e) {
                            const data = e.entries[0].dataPoint;
                            let toolTip = `<strong style='color: ${e.chart.toolTip.borderColor}'>${data.name}</strong></br>`
                            toolTip += `<strong style='color: ${e.chart.toolTip.borderColor}'>Atendimentos:</strong> ${data.y}</br>`;
                            return toolTip;
                        }
                    },
                    data: [{
                        type: "pie",
                        showInLegend: true,
                        indexLabelFontSize: 12,
                        indexLabelPlacement: "outside",
                        indexLabel: "{name}",
                        dataPoints: content[1]
                    }]
                }
                const generatedChart = new CanvasJS.Chart(`azophi-${content[0]}`, prontoAtendimentoConvOptions);
                generatedChart.render();
            })
        },
        prontoAtendimentoPacientes: (data) => {
            if (!data)
                return;

            const prontoAtendimentoPacOptions = {
                animationEnabled: true,
                theme: 'light2',
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    crosshair: {
                        enabled: true,
                        snapToDataPoint: true,
                        labelBackgroundColor: '#66b99b',
                        color: "#66b99b",
                    }
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                    crosshair: {
                        enabled: true,
                        labelBackgroundColor: '#66b99b',
                        color: "#66b99b",
                    }
                },
                legend: {
                    cursor: "pointer",
                    fontSize: 14,
                    horizontalAlign: "center",
                    verticalAlign: "bottom",
                    itemclick: (e) => {
                        if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                            e.dataSeries.visible = false;
                        } else {
                            e.dataSeries.visible = true;
                        }
                        generatedChart.render();
                    }
                },
                toolTip: {
                    shared: true,
                    contentFormatter: function (e) {
                        let entities = e.entries[0].dataPoint.label + "<br/>";
                        for (let i = 0; i < e.entries.length; i++) {
                            if (e.entries[i].dataPoint.y > 0) {

                                if (e.entries[i].dataSeries.visible === undefined || e.entries[i].dataSeries.visible === false)
                                    continue;

                                let tooltip_text = `<span style='color: ${e.entries[i].dataSeries.color}'> ${e.entries[i].dataSeries.name}</span>: ${e.entries[i].dataPoint.y}`;

                                if (e.entries[i].dataSeries.name === 'Total HRG') {
                                    tooltip_text += `<hr style="background-color: ${e.entries[0].dataSeries.color}" class="my-0">`;
                                    entities = entities.concat(tooltip_text);
                                    continue;
                                }

                                entities = entities.concat(tooltip_text + "<br/>");
                            }
                        }
                        return entities;
                    }
                },
                data: [...data['pa-hrg'], ...data['pa-maternidade']]
            }
            const generatedChart = new CanvasJS.Chart(`azophi-pa-pacientes`, prontoAtendimentoPacOptions);
            generatedChart.render();
        },
        altaAdmissao: (data) => {
            if (!data)
                return;

            const prontoAtendimentoPacOptions = {
                animationEnabled: true,
                theme: 'light2',
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    crosshair: {
                        enabled: true,
                        snapToDataPoint: true,
                        labelBackgroundColor: '#66b99b',
                        color: "#66b99b",
                    }
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                    crosshair: {
                        enabled: true,
                        labelBackgroundColor: '#66b99b',
                        color: "#66b99b",
                    }
                },
                legend: {
                    cursor: "pointer",
                    fontSize: 14,
                    horizontalAlign: "center",
                    verticalAlign: "bottom",
                },
                toolTip: {
                    shared: true,
                },
                data: [{
                    click: admissionAndDischarge,
                    name: "Altas",
                    type: "line",
                    showInLegend: true,
                    indexLabelFontSize: 12,
                    markerType: "square",
                    indexLabelPlacement: "outside",
                    color: "#1c2e5f",
                    indexLabel: "{y}",
                    dataPoints: data['altas'],
                },
                {
                    click: admissionAndDischarge,
                    name: "Admissões",
                    type: "line",
                    showInLegend: true,
                    indexLabelFontSize: 12,
                    indexLabelPlacement: "outside",
                    color: "#51cda0",
                    indexLabel: "{y}",
                    dataPoints: data['admissoes'],
                }]
            }
            const generatedChart = new CanvasJS.Chart(`azophi-altas-admissoes`, prontoAtendimentoPacOptions);
            generatedChart.render();
        },
    }

    function admissionAndDischarge(e) {
        const date = e.dataPoint.data;

        const altas = azophiData['altaAdmissao']['altas'].filter(alta => alta.data === date)[0];
        const admissoes = azophiData['altaAdmissao']['admissoes'].filter(adm => adm.data === date)[0];


        const data = { altas: [], admissoes: [] };

        const insertData = (object, data, dataId) => {
            Object.keys(object.turnos).map((key, value) => {
                data[dataId].push({
                    label: key,
                    y: object.turnos[key],
                })
            });
        }

        insertData(altas, data, 'altas');
        insertData(admissoes, data, 'admissoes');

        const options = {
            theme: "light2",
            axisX: {
                labelFontColor: "#717171",
                lineColor: "#a2a2a2",
                tickColor: "#a2a2a2"
            },
            toolTip: {
                shared: true
            },
            axisY: {
                gridThickness: 0,
                includeZero: false,
                labelFontColor: "#717171",
                lineColor: "#a2a2a2",
                tickColor: "#a2a2a2",
                lineThickness: 1
            },
            data: []
        };

        newChart = new CanvasJS.Chart("azophi-altas-admissoes", options);
        newChart.options.data = [{
            name: "Altas",
            type: "column",
            showInLegend: true,
            indexLabelFontSize: 12,
            indexLabelPlacement: "outside",
            color: "#1c2e5f",
            indexLabel: "{y}",
            dataPoints: data['altas'],
        },
        {
            name: "Admissões",
            type: "column",
            showInLegend: true,
            indexLabelFontSize: 12,
            indexLabelPlacement: "outside",
            color: "#51cda0",
            indexLabel: "{y}",
            dataPoints: data['admissoes'],
        }];
        newChart.render();

        $('#btn-azophi-altas-admissoes').removeClass('invisible');
    }

    $('#btn-azophi-altas-admissoes').on('click', (e) => {
        $('#btn-azophi-altas-admissoes').addClass('invisible');
        chart.altaAdmissao(azophiData['altaAdmissao']);
        newChart.render();
    });

    async function renderCharts(chartsOptions) {
        if ($('#azophi-internacao-convenios').length === 0)
            return;
        // Percorre as keys dos vetores de gráficos passados pelo controller
        Object.keys(chartsOptions).map((renderFunction) => {
            // A partir das keys chama as funções do objeto chart
            azophiData[renderFunction] = chartsOptions[renderFunction];
            chart[renderFunction](azophiData[renderFunction]);
        });
    }

    function reloadCards(cards) {
        $("#leitosTotal").html(cards.leitos.total);
        $("#leitosHRG").html(cards.leitos.HRG);
        $("#leitosMaternidade").html(cards.leitos.maternidade);
        
        $("#pacientesTotal").html(cards.pacientes.total);
        $("#pacientesHRG").html(cards.pacientes.HRG);
        $("#pacientesMaternidade").html(cards.pacientes.maternidade);
        $("#pacientesRN").html(cards.pacientes.rn);

        $("#ocupacaoTotal").html(cards.ocupacao.total);
        $("#ocupacaoHRG").html(cards.ocupacao.HRG);
        $("#ocupacaoMaternidade").html(cards.ocupacao.maternidade);

        $("#ultimas24").text(cards.altas24h);
        $("#ultimas12").text(cards.altas12h);

        $("#internacaoHojeHRG tbody").html(cards.internacaoHojeHrg);
        $("#internacaoOntemHRG tbody").html(cards.internacaoOntemHrg);

        $("#internacaoHojeMaternidade tbody").html(cards.internacaoHojeMaternidade);
        $("#internacaoOntemMaternidade tbody").html(cards.internacaoOntemMaternidade);
        
        $("#pacientes7Dias tbody").html(cards.pacientes7Dias);

        const optionsOcupacaoGeral = {
            animationEnabled: true,
            theme: 'light2',
            axisX: {
                titleFontFamily: 'Roboto, sans-serif',
                crosshair: {
                    enabled: true,
                    snapToDataPoint: true,
                    labelBackgroundColor: '#66b99b',
                    color: "#66b99b",
                }
            },
            axisY: {
                titleFontFamily: 'Roboto, sans-serif',
                crosshair: {
                    enabled: true,
                    labelBackgroundColor: '#66b99b',
                    color: "#66b99b",
                }
            },
            legend: {
                cursor: "pointer",
                fontSize: 14,
                horizontalAlign: "center",
                verticalAlign: "bottom",
            },
            toolTip: {
                shared: true,
            },
            data: [{
                type: "line",
                showInLegend: true,
                name: "Pacientes",
                markerType: "square",
                color: "#1c2e5f",
                dataPoints: cards.ocupacaoGeral['pacientes-atual']
            },
            {
                type: "line",
                showInLegend: true,
                name: "Pico/Dia",
                lineDashType: "dash",
                dataPoints: cards.ocupacaoGeral['pacientes-pico']
            }]
        }
        const generatedChart = new CanvasJS.Chart("azophi-ocupacao-geral", optionsOcupacaoGeral);
        generatedChart.render();

        const prontoAtendimentoPacOptions = {
            animationEnabled: true,
            theme: 'light2',
            axisX: {
                titleFontFamily: 'Roboto, sans-serif',
                crosshair: {
                    enabled: true,
                    snapToDataPoint: true,
                    labelBackgroundColor: '#66b99b',
                    color: "#66b99b",
                }
            },
            axisY: {
                titleFontFamily: 'Roboto, sans-serif',
                crosshair: {
                    enabled: true,
                    labelBackgroundColor: '#66b99b',
                    color: "#66b99b",
                }
            },
            legend: {
                cursor: "pointer",
                fontSize: 14,
                horizontalAlign: "center",
                verticalAlign: "bottom",
            },
            toolTip: {
                shared: true,
            },
            data: [{
                click: admissionAndDischarge,
                name: "Altas",
                type: "line",
                showInLegend: true,
                indexLabelFontSize: 12,
                markerType: "square",
                indexLabelPlacement: "outside",
                color: "#1c2e5f",
                indexLabel: "{y}",
                dataPoints: cards.admissaoAlta['altas'],
            },
            {
                click: admissionAndDischarge,
                name: "Admissões",
                type: "line",
                showInLegend: true,
                indexLabelFontSize: 12,
                indexLabelPlacement: "outside",
                color: "#51cda0",
                indexLabel: "{y}",
                dataPoints: cards.admissaoAlta['admissoes'],
            }]
        }
        const generatedChartAdmissao = new CanvasJS.Chart(`azophi-altas-admissoes`, prontoAtendimentoPacOptions);
        generatedChartAdmissao.render();

        const prontoAtendimentoPacOptionsPA = {
            animationEnabled: true,
            theme: 'light2',
            axisX: {
                titleFontFamily: 'Roboto, sans-serif',
                crosshair: {
                    enabled: true,
                    snapToDataPoint: true,
                    labelBackgroundColor: '#66b99b',
                    color: "#66b99b",
                }
            },
            axisY: {
                titleFontFamily: 'Roboto, sans-serif',
                crosshair: {
                    enabled: true,
                    labelBackgroundColor: '#66b99b',
                    color: "#66b99b",
                }
            },
            legend: {
                cursor: "pointer",
                fontSize: 14,
                horizontalAlign: "center",
                verticalAlign: "bottom",
                itemclick: (e) => {
                    if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                        e.dataSeries.visible = false;
                    } else {
                        e.dataSeries.visible = true;
                    }
                    generatedChart.render();
                }
            },
            toolTip: {
                shared: true,
                contentFormatter: function (e) {
                    let entities = e.entries[0].dataPoint.label + "<br/>";
                    for (let i = 0; i < e.entries.length; i++) {
                        if (e.entries[i].dataPoint.y > 0) {

                            if (e.entries[i].dataSeries.visible === undefined || e.entries[i].dataSeries.visible === false)
                                continue;

                            let tooltip_text = `<span style='color: ${e.entries[i].dataSeries.color}'> ${e.entries[i].dataSeries.name}</span>: ${e.entries[i].dataPoint.y}`;

                            if (e.entries[i].dataSeries.name === 'Total HRG') {
                                tooltip_text += `<hr style="background-color: ${e.entries[0].dataSeries.color}" class="my-0">`;
                                entities = entities.concat(tooltip_text);
                                continue;
                            }

                            entities = entities.concat(tooltip_text + "<br/>");
                        }
                    }
                    return entities;
                }
            },
            data: [...cards.paHRG, ...cards.paMaternidade]
        }
        const generatedChartPA = new CanvasJS.Chart(`azophi-pa-pacientes`, prontoAtendimentoPacOptionsPA);
        generatedChartPA.render();

        $('.modal-setor-paciente').on('click', modalSetor);
    }

});