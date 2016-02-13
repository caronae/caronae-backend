$(function() {

    var $errorAlert = $('.error-alert');
    var $exportButton = $('.export-button');
    var $exportButtonCSV = $('.export-button-csv');

    $exportButton.on('click', function(){
        document.location.href = routes.ranking.excel('xlsx', getPeriodStart(), getPeriodEnd());
    });

    $exportButtonCSV.on('click', function(){
        document.location.href = routes.ranking.excel('csv', getPeriodStart(), getPeriodEnd());
    });

    var getPeriodStart = function () {
        return $('.period-start').val();
    };

    var getPeriodEnd = function () {
        return $('.period-end').val();
    };

    var showError = function (text) {
        $errorAlert.find('.content').html(text);
        $errorAlert.slideDown();
    };

    var hideError = function () {
        $errorAlert.slideUp();
    };

    $errorAlert.find('.close').on('click', function () {
        hideError();
    });

    var getDatatablesDataURL = function() {
        return routes.ranking.json(getPeriodStart(), getPeriodEnd());
    };

    $('.search-period-form').on('submit', function (event) {
        event.preventDefault();
        hideError();
        $('.table').DataTable().ajax.url(getDatatablesDataURL()).load();
    });

    var showFeedbackForResponse = function (response) {
        var data = JSON.parse(response.responseText);
        var text = '';
        for (var prop in data) {
            if (data.hasOwnProperty(prop))
                text = text + data[prop];
            text = text + '<br>';
        }

        showError(text);
    };

    $.extend(true, $.fn.dataTable.defaults, {
        "ajax": {
            'url': getDatatablesDataURL(),
            'dataSrc': '',
            'error': showFeedbackForResponse
        },
        "ordering": false
    });

});