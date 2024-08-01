$(document).ready(() => {

    $('#gleitos-panel-beds-container').on('click', '.refresh-button', function () {
        const buttonRefresh = $(this);
        const cardBody = buttonRefresh.closest('.card').find('.card-body');
        const cardId = buttonRefresh.closest('.card').data('id');

        const replaceContent = (id, data) => {
            const icon = $(`#${id}`).children().first();
            $(`#${id}`).html(` ${data}`).prepend(icon);
        }

        buttonRefresh.CardRefresh({
            source: `painel_leitos/${cardId}/leitos`,
            responseType: 'json',
            content: '.card-body div',
            onLoadDone: function (data) {
                replaceContent(`${cardId}-total`, data['count-beds']);
                replaceContent(`${cardId}-virtual`, data['count-virtual-beds']);
                replaceContent(`${cardId}-ocupados`, data['occupied-beds']);
                replaceContent(`${cardId}-reservados`, data['reserved-beds']);
                replaceContent(`${cardId}-bloqueados`, data['blocked-beds']);
                replaceContent(`${cardId}-disponiveis`, data['disponible-beds']);
                replaceContent(`${cardId}-porcentagem-ocupacao`, `${data['ocupation']}%`);

                $(cardBody).find('div').html(data.options);
            }
        })
    });

    $('.unit-card [data-card-widget="card-refresh"]').on('loaded.lte.cardrefresh', () => {
        console.log('a')
    });
})