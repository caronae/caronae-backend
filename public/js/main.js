$(function() {

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
			"url": '/vendor/dataTables-pt_BR.json'
		}
	} );

	$.extend( true, $.fn.datepicker.defaults, {
		'format': "dd/mm/yyyy",
		'autoclose': "true",
		'todayBtn': "linked"
	} );

});
