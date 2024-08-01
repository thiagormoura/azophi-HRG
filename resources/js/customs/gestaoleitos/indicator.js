$(document).ready(() => {

    $('#gleitos-indicadores-container').length && (
        $.ajax({
            url: 'indicadores/get-data-points',
            type: 'GET',
            dataType: 'json',
            success: (data) => {
                console.log(data);
                renderCharts['solicitation-units'](data['solicitation-units']);
                renderCharts['solicitation-accomodations'](data['solicitation-accomodations']);
                renderCharts['solicitation-profiles'](data['solicitation-profiles']);
                renderCharts['solicitation-hours'](data['solicitation-hours']);
                renderCharts['solicitation-hours-unit'](data['solicitation-hours-unit']);
                renderCharts['time-attendance'](data['time-attendance']);
                renderCharts['time-release'](data['time-release']);
            },
            error: (error) => {
                console.log(error);
            }
        })
    )

    const syncTooltip = (charts) => {
        this.onToolTipUpdated = function (e) {
            for (var j = 0; j < charts.length; j++) {
                if (charts[j] != e.chart)
                    charts[j].toolTip.showAtX(e.entries[0].xValue);
            }
        }

        this.onToolTipHidden = function (e) {
            for (var j = 0; j < charts.length; j++) {
                if (charts[j] != e.chart)
                    charts[j].toolTip.hide();
            }
        }

        for (var i = 0; i < charts.length; i++) {
            if (!charts[i].options.toolTip)
                charts[i].options.toolTip = {};

            charts[i].options.toolTip.updated = this.onToolTipUpdated;
            charts[i].options.toolTip.hidden = this.onToolTipHidden;
        }
    }

    const renderCharts = {
        'solicitation-units': (data) => {
            chart = new CanvasJS.Chart("gleitos-solicitacao-setor", {
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
                data: [{
                    name: "Solicitações",
                    type: "column",
                    showInLegend: true,
                    indexLabelFontSize: 12,
                    indexLabelPlacement: "outside",
                    indexLabel: "{y}",
                    dataPoints: data,
                }]
            });
            chart.render();
        },
        'solicitation-accomodations': (data) => {
            chart = new CanvasJS.Chart("gleitos-solicitacao-acomodacao", {
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
                data: [{
                    name: "Solicitações",
                    type: "column",
                    showInLegend: true,
                    indexLabelFontSize: 12,
                    indexLabelPlacement: "outside",
                    color: "#1c2e5f",
                    indexLabel: "{y}",
                    dataPoints: data,
                }]
            });
            chart.render();
        },
        'solicitation-profiles': (data) => {
            chart = new CanvasJS.Chart("gleitos-solicitacao-perfil", {
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
                data: [{
                    name: "Solicitações",
                    type: "column",
                    showInLegend: true,
                    indexLabelFontSize: 12,
                    indexLabelPlacement: "outside",
                    color: "#1c2e5f",
                    indexLabel: "{y}",
                    dataPoints: data,
                }]
            });
            chart.render();
        },
        'solicitation-hours': (data) => {
            chart = new CanvasJS.Chart("gleitos-solicitacao-hora", {
                animationEnabled: true,
                theme: 'light2',
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
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
                    name: "Solicitações",
                    type: "line",
                    showInLegend: true,
                    indexLabelFontSize: 12,
                    indexLabelPlacement: "outside",
                    color: "#1c2e5f",
                    indexLabel: "{y}",
                    dataPoints: data,
                }]
            });
            chart.render();
        },
        'solicitation-hours-unit': (data) => {
            const chart = new CanvasJS.Chart("gleitos-solicitacao-hora-setor", {
                animationEnabled: true,
                zoomEnabled: true,
                theme: 'light2',
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    crosshair: {
                        enabled: true,
                        snapToDataPoint: true,
                        labelBackgroundColor: '#1c2e5f',
                        color: "#1c2e5f",
                    }
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                    crosshair: {
                        enabled: true,
                        labelBackgroundColor: '#1c2e5f',
                        color: "#1c2e5f",
                    }
                },
                legend: {
                    cursor: "pointer",
                    itemmouseover: function (e) {
                        e.dataSeries.lineThickness = e.chart.data[e.dataSeriesIndex].lineThickness * 2;
                        e.dataSeries.markerSize = e.chart.data[e.dataSeriesIndex].markerSize + 2;
                        e.chart.render();
                    },
                    itemmouseout: function (e) {
                        e.dataSeries.lineThickness = e.chart.data[e.dataSeriesIndex].lineThickness / 2;
                        e.dataSeries.markerSize = e.chart.data[e.dataSeriesIndex].markerSize - 2;
                        e.chart.render();
                    },
                    itemclick: function (e) {
                        if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                            e.dataSeries.visible = false;
                        } else {
                            e.dataSeries.visible = true;
                        }
                        e.chart.render();
                    }
                },
                toolTip: {
                    shared: true
                },
                data: data
            });

            chart.render();
        },
        'time-attendance': (data) => {
            const charts = [];
            charts.push(new CanvasJS.Chart("gleitos-tempo-atendimento-geral", {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: "Geral",
                },
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    labelFormatter: function (e) {
                        return '';
                    },
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                },

                toolTip: {
                    contentFormatter: function (e) {
                        const element = e.entries[0];
                        return `<span style="color:${element.dataSeries._colorSet[element.index]}">${element.dataPoint.label}: </span>${element.dataPoint.y}min`;
                    }
                },
                data: [{
                    name: "Setor",
                    type: "column",
                    dataPoints: data,
                }],
            }));

            charts.push(new CanvasJS.Chart("gleitos-tempo-atendimento-perfil", {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: "Por perfil",
                },
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    labelFormatter: function (e) {
                        return '';
                    },
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                },
                toolTip: {
                    contentFormatter: function (e) {
                        const element = e.entries[0];
                        let content = '';
                        element.dataPoint.profiles.map((profile) => {
                            content += `<br><span><strong>${profile.label}:</strong> ${profile.y}min</span>`;
                        });
                        return `<span style="color:${element.dataSeries._colorSet[element.index]}">${element.dataPoint.label}: </span>${content}`;
                    }
                },
                data: [{
                    name: "Setor",
                    type: "column",
                    dataPoints: data,
                }],
            }));

            charts.push(new CanvasJS.Chart("gleitos-tempo-atendimento-acomodacao", {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: "Por acomodação",
                },
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    labelFormatter: function (e) {
                        return '';
                    },
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                },
                toolTip: {
                    contentFormatter: function (e) {
                        const element = e.entries[0];
                        let content = '';
                        element.dataPoint.accomodations.map((accomodation) => {
                            content += `<br><span><strong>${accomodation.label}:</strong> ${accomodation.y}min</span>`;
                        });
                        return `<span style="color:${element.dataSeries._colorSet[element.index]}">${element.dataPoint.label}: </span>${content}`;
                    }
                },
                data: [{
                    name: "Setor",
                    type: "column",
                    dataPoints: data,
                }],
            }));

            syncTooltip(charts);

            for (var i = 0; i < charts.length; i++) {
                charts[i].render();
            }
        },
        'time-release': (data) => {
            const charts = [];
            charts.push(new CanvasJS.Chart("gleitos-tempo-liberacao-geral", {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: "Geral",
                },
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    labelFormatter: function (e) {
                        return '';
                    },
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                },

                toolTip: {
                    contentFormatter: function (e) {
                        const element = e.entries[0];
                        return `<span style="color:${element.dataSeries._colorSet[element.index]}">${element.dataPoint.label}: </span>${element.dataPoint.y}min`;
                    }
                },
                data: [{
                    name: "Setor",
                    type: "column",
                    dataPoints: data,
                }],
            }));

            charts.push(new CanvasJS.Chart("gleitos-tempo-liberacao-perfil", {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: "Por perfil",
                },
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    labelFormatter: function (e) {
                        return '';
                    },
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                },
                toolTip: {
                    contentFormatter: function (e) {
                        const element = e.entries[0];
                        let content = '';
                        element.dataPoint.profiles.map((profile) => {
                            content += `<br><span><strong>${profile.label}:</strong> ${profile.y}min</span>`;
                        });
                        return `<span style="color:${element.dataSeries._colorSet[element.index]}">${element.dataPoint.label}: </span>${content}`;
                    }
                },
                data: [{
                    name: "Setor",
                    type: "column",
                    dataPoints: data,
                }],
            }));

            charts.push(new CanvasJS.Chart("gleitos-tempo-liberacao-acomodacao", {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: "Por acomodação",
                },
                axisX: {
                    titleFontFamily: 'Roboto, sans-serif',
                    labelFormatter: function (e) {
                        return '';
                    },
                },
                axisY: {
                    titleFontFamily: 'Roboto, sans-serif',
                },
                toolTip: {
                    contentFormatter: function (e) {
                        const element = e.entries[0];
                        let content = '';
                        element.dataPoint.accomodations.map((accomodation) => {
                            content += `<br><span><strong>${accomodation.label}:</strong> ${accomodation.y}min</span>`;
                        });
                        return `<span style="color:${element.dataSeries._colorSet[element.index]}">${element.dataPoint.label}: </span>${content}`;
                    }
                },
                data: [{
                    name: "Setor",
                    type: "column",
                    dataPoints: data,
                }],
            }));

            syncTooltip(charts);

            for (var i = 0; i < charts.length; i++) {
                charts[i].render();
            }
        }
    }

})