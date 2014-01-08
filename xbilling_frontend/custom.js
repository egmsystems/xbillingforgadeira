$(document).ready(function() {
    $('#mostrardominio').click(function() {
		$('.subdominio').slideUp("fast")
        $('.dominio').slideToggle("fast");
		
		var div_data = "<div id='domain_info' style='margin-right: 20px;'>
							<h6>Dominio</h6>
							<input type='text' placeholder='www.' name='domain' id='domain' size='15' value='<?php echo($domain_name);?>' />
							<div align='left' style='margin-left: 50px;'>
								<p>
									<input type='radio' name='domain_tid' id='domain_type_own' value='1'<?php if(!$domain_type_id || $domain_type_id == 1){echo(' checked='checked'');}?> /> Tengo este dominio.<br />
									<input type='radio' name='domain_tid' id='domain_type_new' value='2'<?php if($domain_type_id == 2){echo(' checked='checked'');}?> /> Quiero comprar este dominio.
									<br>
								</p>
							</div>
						</div>";
		$("#dominio").html(div_data);
    });
});

$(document).ready(function() {
    $('#mostrarsubdominio').click(function() {
		$('.dominio').slideUp("fast")
        $('.subdominio').slideToggle("fast");
    });
});