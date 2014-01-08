$(document).ready(function() {
    $('#mostrardominio').click(function() {
		$('.subdominio').slideUp("fast");
		
		$('.dominio').slideToggle("fast");
    });
});

$(document).ready(function() {
    $('#mostrarsubdominio').click(function() {
		$('.dominio').slideUp("fast");
		
        $('.subdominio').slideToggle("fast");
    });
});