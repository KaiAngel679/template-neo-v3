$('.cards__live-filter .filter').on('click', function () {
    const filterType = $(this).data('cards');
    $('.cards__live-filter .filter').removeClass('active');
    $(this).addClass('active');
    $('.cards__live-wrapper').hide();
    $('#' + filterType).show();
});