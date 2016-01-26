$(function() {

    var $errorAlert = $('.error-alert');
    var $exportButton = $('.export-button');
    var $exportButtonCSV = $('.export-button-csv');

    $exportButton.on('click', function(){
        document.location.href = getUrlExcel('xlsx');
    });

    $exportButtonCSV.on('click', function(){
        document.location.href = getUrlExcel('csv');
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

    var getUrl = function () {
        return document.location.href + '.json' + '?start=' + getPeriodStart() + '&' + 'end=' + getPeriodEnd();
    };

    var getUrlExcel = function(type){
        return document.location.href + '.excel' + '?' + 'type=' + type + '&' + 'start=' + getPeriodStart() + '&' + 'end=' + getPeriodEnd();
    };

    $('.search-period-form').on('submit', function (event) {
        event.preventDefault();
        hideError();
        $('.table').DataTable().ajax.url(getUrl()).load();
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
            'url': getUrl(),
            'dataSrc': '',
            'error': showFeedbackForResponse
        },
        "ordering": false
    });

});