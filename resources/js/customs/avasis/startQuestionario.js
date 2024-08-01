$("#start_quest").click(function() {
    $.post(_URL + "/avasis/startModal", function(data) {
        $('#start').append(data);
        $('#showQuestionario').modal('show');
        $("#startBodyQuestionario tr").click(function(){
            if($(this).attr('data-id') !== 'undefined' && $(this).attr('data-id') !== false){
                location.href = "questionario.php?id_questionario="+$(this).attr('data-id');
            }
        });
        $("#startBodyQuestionario tr").hover(function(){
            $(this).css("background-color", "#d7d7d7");
        }, function(){
            $(this).css("background-color", "white");
        });
    });
});