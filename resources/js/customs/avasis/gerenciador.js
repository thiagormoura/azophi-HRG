$(document).ready(()=>
{
    $('.btn-gerenciador').click(function(){
        if(!$(this).hasClass('selected')) {
            $(this).addClass('selected');
            $('.btn-gerenciador').not(this).removeClass('selected');
        }
    });
});