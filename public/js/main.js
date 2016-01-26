$(function() {

    var baseURL = '';
	url = function(url){
	    if(!baseURL) baseURL = $('head').data('url');

	    return baseURL + '/' + url;
	};

	var token = '';
	csrf_token = function(){
		if(!token) token = $('head').data('token');

		return token;
	};

	getQueryParameterByName = function(name) {
		var match = new RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
		return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	};

	$.extend( true, $.fn.dataTable.defaults, {
		"processing": true,
		"language" : {
			"url": url('vendor/DataTables/Portuguese-Brasil.json')
		}
	} );

	$.extend( true, $.fn.datepicker.defaults, {
		'format': "dd/mm/yyyy",
		'autoclose': "true",
		'todayBtn': "linked"
	} );

    /*
        Fullscreen background
    */
    //$.backstretch(url('assets/img/backgrounds/1.jpg'));


});
