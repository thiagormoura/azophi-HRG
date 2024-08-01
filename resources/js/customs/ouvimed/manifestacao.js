$(document).ready(function () {

    // get id from url
    var url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1);

    const { PDFDocument, StandardFonts, rgb } = PDFLib

    // prepare selectize
    $("select[name='slSetoresEnvolvidosVisualizacao']").each(function () {

        var options_values = [];
        $(this).find("option").each(function () {
            options_values.push($(this).val());
        });

        $(this).selectize({
            maxItems: null,
            items: options_values,
        });
    });

    // cancelamento de manifestacao
    $("#btCancelarManifestacao").click(function () {

        Swal.fire({
            title: "Cancelar Manifestação",
            text: "Tem certeza que deseja cancelar a manifestação? Essa ação não poderá ser desfeita.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sim, cancelar manifestação!",
            cancelButtonText: "Não, manter manifestação!",
            allowOutsideClick: false
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: _URL + "/ouvimed/cancelar-manifestacao/" + id,
                    type: "GET",
                    success: function (data) {
                        if (data[0] == true) {
                            Swal.fire({
                                title: "Manifestação cancelada com sucesso!",
                                text: "A manifestação foi cancelada com sucesso.",
                                icon: "success",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = _URL + "/ouvimed";
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Erro ao cancelar manifestação!",
                                text: "Ocorreu um erro ao cancelar a manifestação. Por favor, entre em contato com a TI",
                                icon: "error",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function (msg) {
                        Swal.fire({
                            title: "Erro ao cancelar manifestação!",
                            text: "Ocorreu um erro ao cancelar a manifestação. Por favor, entre em contato com a TI. Erro: " + msg,
                            icon: "error",
                            confirmButtonText: "Ok",
                            allowOutsideClick: false
                        });
                    }
                });


            }

        });

    });

    // edição de manifestação
    $("#btEditarManifestacao").click(function () {
        window.location.href = _URL + "/ouvimed/editar/" + id;
    });

    // processar manifestação
    $("#btProcessarManifestacao").click(function () {

        Swal.fire({
            title: "Processar Manifestação",
            text: "Tem certeza que deseja atualizar a manifestação para em processamento?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sim, atualizar manifestação!",
            cancelButtonText: "Não, manter manifestação!",
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: _URL + "/ouvimed/processar/" + id,
                    type: "POST",
                    success: function (data) {
                        if (data[0] == true) {
                            Swal.fire({
                                title: "Manifestação atualizada com sucesso!",
                                text: "A manifestação foi atualizada com sucesso.",
                                icon: "success",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = _URL + "/ouvimed/manifestacao/" + id;
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Erro ao atualizar manifestação!",
                                text: "Ocorreu um erro ao atualizar a manifestação. Por favor, entre em contato com a TI",
                                icon: "error",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function (msg) {
                        Swal.fire({
                            title: "Erro ao atualizar manifestação!",
                            text: "Ocorreu um erro ao atualizar a manifestação. Por favor, entre em contato com a TI. Erro: " + msg,
                            icon: "error",
                            confirmButtonText: "Ok",
                            allowOutsideClick: false
                        });
                    }
                });
            }
        });
    });

    // cancelar processamento
    $("#btCancelarProcessamento").click(function () {

        Swal.fire({
            title: "Cancelar processamento",
            text: "Tem certeza que deseja cancelar o processamneto da manifestação? O status da manifestação será alterado para 'Aberto'.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sim, atualizar manifestação!",
            cancelButtonText: "Não, manter manifestação!",
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: _URL + "/ouvimed/cancelarProcessamento/" + id,
                    type: "POST",
                    success: function (data) {
                        if (data[0] == true) {
                            Swal.fire({
                                title: "Manifestação atualizada com sucesso!",
                                text: "A manifestação foi atualizada com sucesso.",
                                icon: "success",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = _URL + "/ouvimed/manifestacao/" + id;
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Erro ao atualizar manifestação!",
                                text: "Ocorreu um erro ao atualizar a manifestação. Por favor, entre em contato com a TI",
                                icon: "error",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function (msg) {
                        Swal.fire({
                            title: "Erro ao atualizar manifestação!",
                            text: "Ocorreu um erro ao atualizar a manifestação. Por favor, entre em contato com a TI. Erro: " + msg,
                            icon: "error",
                            confirmButtonText: "Ok",
                            allowOutsideClick: false
                        });
                    }
                });
            }
        });

    });

    // atualizar ação tomada
    $("#btAtualizarAcaoTomada").click(function () {
        window.location.href = _URL + "/ouvimed/atualizarAcao/" + id;
    });

    // finalizar processamento
    $("#btFinalizarProcessamento").click(function () {

        Swal.fire({
            title: "Finalizar processamento da manifestação",
            text: "Tem certeza que deseja finalizar o processamento da manifestação?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sim, atualizar manifestação!",
            cancelButtonText: "Não, manter manifestação!",
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: _URL + "/ouvimed/finalizarProcessamento/" + id,
                    type: "POST",
                    success: function (data) {
                        if (data[0] == true) {
                            Swal.fire({
                                title: "Manifestação atualizada com sucesso!",
                                text: "A manifestação foi atualizada com sucesso.",
                                icon: "success",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = _URL + "/ouvimed/manifestacao/" + id;
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Erro ao atualizar manifestação!",
                                text: "Ocorreu um erro ao atualizar a manifestação. Por favor, entre em contato com a TI",
                                icon: "error",
                                confirmButtonText: "Ok",
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function (msg) {
                        Swal.fire({
                            title: "Erro ao atualizar manifestação!",
                            text: "Ocorreu um erro ao atualizar a manifestação. Por favor, entre em contato com a TI. Erro: " + msg,
                            icon: "error",
                            confirmButtonText: "Ok",
                            allowOutsideClick: false
                        });
                    }
                });
            }
        });

    });

    $("#btImprimirManifestacao").click(function () {

        // Adquire os dados da manifestacao
        $.ajax({
            url: _URL + "/ouvimed/getManifestacaoDataPDF/" + id,
            type: "POST",
            success: function (data) {
                modifyPdf(data);
            },
            error: function (msg) {
                console.log(msg);
            }
        });

    });

    // format date string to dd/mm/yyyy
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;

        return [day, month, year].join('/');
    }

    async function modifyPdf(data) {

        console.log(data);

        const url = _URL + "/resources/docs/modeloManifestacaoLimpo.pdf"
        const existingPdfBytes = await fetch(url).then(res => res.arrayBuffer())

        const pdfDoc = await PDFDocument.load(existingPdfBytes)
        const helveticaFont = await pdfDoc.embedFont(StandardFonts.Helvetica)

        const pages = pdfDoc.getPages()
        const firstPage = pages[0];
        const secondPage = pages[1];
        const { width, height } = firstPage.getSize();

        firstPage.drawText(data.PROTOCOLO, {
            x: width * 0.65,
            y: height * 0.888,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0)
        });


        firstPage.drawText(data.NOME_AUTOR, {
            x: width * 0.14,
            y: height * 0.783,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0)
        });

        firstPage.drawText(data.NOME_PACIENTE, {
            x: width * 0.2,
            y: height * 0.765,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0)
        });

        firstPage.drawText(data.REGISTRO_PACIENTE.toString(), {
            x: width * 0.68,
            y: height * 0.765,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0)
        });

        if(data.TELEFONE == null){
            data.TELEFONE = "";
        }

        firstPage.drawText(data.TELEFONE.toString(), {
            x: width * 0.2,
            y: height * 0.7455,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0)
        });

        // data.DTHR_MANIFESTACAO TO dd/mm/yyyy hh:mm:ss



        firstPage.drawText(formatDate(data.DTHR_MANIFESTACAO), {
            x: width * 0.135,
            y: height * 0.729,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0)
        });

        // Presencial
        if(data.VEICULO=="PRESENCIAL"){
            firstPage.drawRectangle({
                x: width * 0.070,
                y: height * 0.6932,
                width: width * 0.025,
                height: height * 0.011,
                borderColor: rgb(0, 0, 0),
                borderWidth: 1,
                color: rgb(0, 0, 0),
            });
        }

        else if(data.VEICULO=="TELEFONE"){

            // Telefone
            firstPage.drawRectangle({
                x: width * 0.2335,
                y: height * 0.6932,
                width: width * 0.025,
                height: height * 0.011,
                borderColor: rgb(0, 0, 0),
                borderWidth: 1,
                color: rgb(0, 0, 0),
            });
        }

        else if(data.VEICULO=="CAIXA_SUGESTOES"){

            // Caixa sugestões
            firstPage.drawRectangle({
                x: width * 0.526,
                y: height * 0.6935,
                width: width * 0.025,
                height: height * 0.011,
                borderColor: rgb(0, 0, 0),
                borderWidth: 1,
                color: rgb(0, 0, 0),
            });
        }

        else if(data.VEICULO=="EMAIL"){

            // E-mail
            firstPage.drawRectangle({
                x: width * 0.070,
                y: height * 0.676,
                width: width * 0.025,
                height: height * 0.011,
                borderColor: rgb(0, 0, 0),
                borderWidth: 1,
                color: rgb(0, 0, 0),
            });

        }

        else if(data.VEICULO=="REDE_SOCIAL"){

            // Rede social
            firstPage.drawRectangle({
                x: width * 0.2335,
                y: height * 0.676,
                width: width * 0.025,
                height: height * 0.011,
                borderColor: rgb(0, 0, 0),
                borderWidth: 1,
                color: rgb(0, 0, 0),
            });

            //Rede social - qual
            firstPage.drawText(data.VEICULO_DESCRICAO, {
                x: width * 0.44,
                y: height * 0.677,
                size: 10,
                font: helveticaFont,
                color: rgb(0, 0, 0)
            });
        }

        else if(data.VEICULO=="OUTRO"){

            // Outro
            firstPage.drawRectangle({
                x: width * 0.526,
                y: height * 0.676,
                width: width * 0.025,
                height: height * 0.011,
                borderColor: rgb(0, 0, 0),
                borderWidth: 1,
                color: rgb(0, 0, 0),
            });

            // Outro - qual
            firstPage.drawText(data.VEICULO_DESCRICAO, {
                x: width * 0.665,
                y: height * 0.677,
                size: 10,
                font: helveticaFont,
                color: rgb(0, 0, 0)
            });
       
        }

        for(let i=0; i<data.IDENTIFICACOES.length; i++){
        
            if(data.IDENTIFICACOES[i].IDENTIFICACAO == 'ELOGIO'){

                // Elogio
                firstPage.drawRectangle({
                    x: width * 0.070,
                    y: height * 0.6406,
                    width: width * 0.025,
                    height: height * 0.011,
                    borderColor: rgb(0, 0, 0),
                    borderWidth: 1,
                    color: rgb(0, 0, 0),
                });
            } 

            else if(data.IDENTIFICACOES[i].IDENTIFICACAO == 'RECLAMACAO'){
                // Reclamação
                firstPage.drawRectangle({
                    x: width * 0.202,
                    y: height * 0.6406,
                    width: width * 0.025,
                    height: height * 0.011,
                    borderColor: rgb(0, 0, 0),
                    borderWidth: 1,
                    color: rgb(0, 0, 0),
                });

            }

            else if(data.IDENTIFICACOES[i].IDENTIFICACAO == 'SOLICITACAO'){

                // Solicitação
                firstPage.drawRectangle({
                    x: width * 0.4313,
                    y: height * 0.6406,
                    width: width * 0.025,
                    height: height * 0.011,
                    borderColor: rgb(0, 0, 0),
                    borderWidth: 1,
                    color: rgb(0, 0, 0),
                });
            
            }

            else if(data.IDENTIFICACOES[i].IDENTIFICACAO == 'INFORMACAO'){
            
                // Informação
                firstPage.drawRectangle({
                    x: width * 0.6195,
                    y: height * 0.6408,
                    width: width * 0.025,
                    height: height * 0.011,
                    borderColor: rgb(0, 0, 0),
                    borderWidth: 1,
                    color: rgb(0, 0, 0),
                });

            }

            else if(data.IDENTIFICACOES[i].IDENTIFICACAO == 'SUGESTAO'){

                // Sugestão
                firstPage.drawRectangle({
                    x: width * 0.070,
                    y: height * 0.6228,
                    width: width * 0.025,
                    height: height * 0.011,
                    borderColor: rgb(0, 0, 0),
                    borderWidth: 1,
                    color: rgb(0, 0, 0),
                });

            }

            else if(data.IDENTIFICACOES[i].IDENTIFICACAO == 'CRITICA'){

                // Critica / sugestão
                firstPage.drawRectangle({
                    x: width * 0.202,
                    y: height * 0.6228,
                    width: width * 0.025,
                    height: height * 0.011,
                    borderColor: rgb(0, 0, 0),
                    borderWidth: 1,
                    color: rgb(0, 0, 0),
                });

            }

            else if(data.IDENTIFICACOES[i].IDENTIFICACAO == 'DENUNCIA'){

                // Denuncia
                firstPage.drawRectangle({
                    x: width * 0.4313,
                    y: height * 0.623,
                    width: width * 0.025,
                    height: height * 0.011,
                    borderColor: rgb(0, 0, 0),
                    borderWidth: 1,
                    color: rgb(0, 0, 0),
                });

            }

        }


        //Linhas da descrição da manifestacao

        // linha 1
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.59 },
            end: { x: width * 0.85, y: height * 0.59 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 2
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.573 },
            end: { x: width * 0.85, y: height * 0.573 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 3
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.557 },
            end: { x: width * 0.85, y: height * 0.557 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 4
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.54 },
            end: { x: width * 0.85, y: height * 0.54 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 5
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.524 },
            end: { x: width * 0.85, y: height * 0.524 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 6
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.507 },
            end: { x: width * 0.85, y: height * 0.507 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 7
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.49 },
            end: { x: width * 0.85, y: height * 0.49 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });
        
        // linha 8
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.4735 },
            end: { x: width * 0.85, y: height * 0.4735 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 9
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.457 },
            end: { x: width * 0.85, y: height * 0.457 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 10
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.44 },
            end: { x: width * 0.85, y: height * 0.44 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 11
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.4235 },
            end: { x: width * 0.85, y: height * 0.4235 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 12
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.407 },
            end: { x: width * 0.85, y: height * 0.407 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 13
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.391 },
            end: { x: width * 0.85, y: height * 0.391 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 14
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.374 },
            end: { x: width * 0.85, y: height * 0.374 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 15
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.357 },
            end: { x: width * 0.85, y: height * 0.357 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 16
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.341 },
            end: { x: width * 0.85, y: height * 0.341 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 17
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.324 },
            end: { x: width * 0.85, y: height * 0.324 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 18
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.307 },
            end: { x: width * 0.85, y: height * 0.307 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 19
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.291 },
            end: { x: width * 0.85, y: height * 0.291 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 20
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.274 },
            end: { x: width * 0.85, y: height * 0.274 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 21
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.257 },
            end: { x: width * 0.85, y: height * 0.257 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 22
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.241 },
            end: { x: width * 0.85, y: height * 0.241 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 23
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.224 },
            end: { x: width * 0.85, y: height * 0.224 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 24
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.2072 },
            end: { x: width * 0.85, y: height * 0.2072 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 25
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.191 },
            end: { x: width * 0.85, y: height * 0.191 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 26
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.174 },
            end: { x: width * 0.85, y: height * 0.174 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 27
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.158 },
            end: { x: width * 0.85, y: height * 0.158 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 28
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.141 },
            end: { x: width * 0.85, y: height * 0.141 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 29
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.124 },
            end: { x: width * 0.85, y: height * 0.124 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // linha 30
        firstPage.drawLine({
            start: { x: width * 0.066, y: height * 0.108 },
            end: { x: width * 0.85, y: height * 0.108 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });


        // Descrição da manifestação
        firstPage.drawText(data.DESCRICAO_MANIFESTACAO, {
            x: width * 0.066,
            y: height * 0.591,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0),
            maxWidth: width * 0.8,
            lineHeight: 14,
            wordsBreaks: [' ', '-', '/'],
        });

        // page 2
        
        // draw lines from setores envolvidos

        // Linha 1
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.89 },
            end: { x: width * 0.85, y: height * 0.89 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 2
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.874 },
            end: { x: width * 0.85, y: height * 0.874 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 3
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.857 },
            end: { x: width * 0.85, y: height * 0.857 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Descrição setores envolvidos
        secondPage.drawText(data.SETORES_ENVOLVIDOS, {
            x: width * 0.066,
            y: height * 0.892,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0),
            maxWidth: width * 0.8,
            lineHeight: 14,
            wordsBreaks: [' ', '-', '/'],
        });

        // Linhas da ação tomada

        // Linha 1
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.803 },
            end: { x: width * 0.85, y: height * 0.803 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 2
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.787 },
            end: { x: width * 0.85, y: height * 0.787 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 3
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.77 },
            end: { x: width * 0.85, y: height * 0.77 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 4
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.754 },
            end: { x: width * 0.85, y: height * 0.754 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 5
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.737 },
            end: { x: width * 0.85, y: height * 0.737 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 6
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.72 },
            end: { x: width * 0.85, y: height * 0.72 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 7
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.704 },
            end: { x: width * 0.85, y: height * 0.704 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 8
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.687 },
            end: { x: width * 0.85, y: height * 0.687 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 9
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.671 },
            end: { x: width * 0.85, y: height * 0.671 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 10
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.654 },
            end: { x: width * 0.85, y: height * 0.654 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 11
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.638 },
            end: { x: width * 0.85, y: height * 0.638 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 12
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.621 },
            end: { x: width * 0.85, y: height * 0.621 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 13
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.604 },
            end: { x: width * 0.85, y: height * 0.604 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 14
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.588 },
            end: { x: width * 0.85, y: height * 0.588 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 15
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.571 },
            end: { x: width * 0.85, y: height * 0.571 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 16
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.555 },
            end: { x: width * 0.85, y: height * 0.555 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 17
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.538 },
            end: { x: width * 0.85, y: height * 0.538 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 18
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.521 },
            end: { x: width * 0.85, y: height * 0.521 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 19
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.505 },
            end: { x: width * 0.85, y: height * 0.505 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 20
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.488 },
            end: { x: width * 0.85, y: height * 0.488 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 21
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.471 },
            end: { x: width * 0.85, y: height * 0.471 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 22
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.454 },
            end: { x: width * 0.85, y: height * 0.454 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 23
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.438 },
            end: { x: width * 0.85, y: height * 0.438 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 24
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.421 },
            end: { x: width * 0.85, y: height * 0.421 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 25
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.404 },
            end: { x: width * 0.85, y: height * 0.404 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 26
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.388 },
            end: { x: width * 0.85, y: height * 0.388 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 27
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.372 },
            end: { x: width * 0.85, y: height * 0.372 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 28
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.355 },
            end: { x: width * 0.85, y: height * 0.355 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Linha 29
        secondPage.drawLine({
            start: { x: width * 0.066, y: height * 0.338 },
            end: { x: width * 0.85, y: height * 0.338 },
            thickness: 1,
            color: rgb(0, 0, 0),
        });

        // Descrição da ação tomada
        secondPage.drawText(data.ACOES_TOMADAS, {
            x: width * 0.066,
            y: height * 0.805,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0),
            maxWidth: width * 0.8,
            lineHeight: 14,
            wordsBreaks: [' ', '-', '/'],
        });

        // Data resolvido em
        secondPage.drawText(formatDate(data.DTHR_RESOLVIDO.toString()), {
            x: width * 0.2,
            y: height * 0.275,
            size: 11,
            font: helveticaFont,
            color: rgb(0, 0, 0),
        });

        const pdfBytes = await pdfDoc.save()
        download(pdfBytes, "manifestacao.pdf", "application/pdf");

    }

});

