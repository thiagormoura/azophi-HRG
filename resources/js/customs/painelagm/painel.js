$(document).ready(function(){
    if ($(".tot-cirurgia").attr("data-count") > 11)
        startFunction();
    else
        console.log($(".tot-cirurgia").attr("data-count"));
});

function startFunction(){
    setTimeout(paginate(), 15000);
}

function paginate(){
    setInterval(function(){
        $.ajax({
            url: _URL + "/painelagm/paginate",
            type: "POST",
            data: {
                initial: $(".tot-cirurgia").attr("data-index-count"),
                final: parseInt($(".tot-cirurgia").attr("data-index-count"))+11
            },
            dataType: "json"
        }).done(function(resposta) {
            
            $("#painel tbody").empty();
            $("#painel tbody").html(resposta.rows);
            $(".tot-cirurgia").attr("data-count", resposta.countRows);
            $(".tot-cirurgia").attr("data-index-count", resposta.index);

        }).fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
    }, 15000);
}