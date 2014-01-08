<?php
 /**
 * Front-end billing package for ZPanel xBilling Module
 * Version : 100
 * @author Aderemi Adewale (modpluz @ ZPanel Forums)
 * Email : goremmy@gmail.com
 * @desc Performs all basic operations from Basic User Registration to Creation of Hosting Domains
*/
    require_once('config.php');
    require_once('functions/xbilling.php');
    
    $settings = getSettings();
    
    //echo(strtotime(date("Y-m-d", strtotime(date("Y-m-d", strtotime("2012-11-13"))." +12 months"))));
    $captcha_disabled = 1;
    if((!isset($settings['recaptcha_disabled_yn']) || $settings['recaptcha_disabled_yn'] == 1) && isset($settings['recaptcha_public_key']) && isset($settings['recaptcha_private_key'])){
       $captcha_disabled = 0;
       require_once('classes/recaptchalib.php');        
    }
    
    $error = 0;
    $error_msg = '';
    if(isset($_POST['section'])){
        $section = ((int) $_POST['section']) ? $_POST['section']:'1';
        $package_id = isset($_POST['package_id']) ? (int) $_POST['package_id'] : 0;
        $period_id = isset($_POST['period_id']) ? (int) $_POST['period_id'] : 0;
        $domain_name = isset($_POST['domain']) ? filter_var($_POST['domain'], FILTER_SANITIZE_STRING) : '';
		$subdomain_name = isset($_POST['subdomain']) ? filter_var($_POST['subdomain'], FILTER_SANITIZE_STRING) : '';
        $domain_type_id = isset($_POST['domain_tid']) ? (int) ($_POST['domain_tid']) : 1;
		
		if($section == 2){
			if(isset($subdomain_name) && !$domain_name){
				$domain_name = $subdomain_name . '.gadeira.es';
			}
		}
		
        $fullname = isset($_POST['fullname']) ? filter_var($_POST['fullname'],FILTER_SANITIZE_STRING) : '';
        $email_address = isset($_POST['email_address']) ? filter_var($_POST['email_address'], FILTER_SANITIZE_STRING) : '';
        $address = isset($_POST['address']) ? filter_var($_POST['address'], FILTER_SANITIZE_STRING) : '';
        $postal_code = isset($_POST['postal_code']) ? filter_var($_POST['postal_code'], FILTER_SANITIZE_STRING) : '';
        $phone = isset($_POST['phone']) ? filter_var($_POST['phone'], FILTER_SANITIZE_STRING) : '';
        $username = isset($_POST['username']) ? filter_var($_POST['username'], FILTER_SANITIZE_STRING) : '';
		$shopname = isset($_POST['shopname']) ? filter_var($_POST['shopname'], FILTER_SANITIZE_STRING) : '';
        

		if(!$period_id){
			$error = 1;
            $error_msg = 'Debe seleccionar un periodo de servicio.';
		}
		
        if(!$package_id){
            $error = 1;
            $error_msg = 'Se ha producido un error, por favor haga click <a href="index.php">aquí</a> para empezar de nuevo.';
        }
        
        if(!$error){
            switch($section){
                case 1:
                   $captcha_challenge =  $_POST['recaptcha_challenge_field'];
                   $captcha_response =  $_POST['recaptcha_response_field'];
                   
                   if(!$package_id){
                     $error = 1;
                     $error_msg = 'Por favor selecciona un paquete válido.';
                   } elseif(!$period_id){
                     $error = 1;
                     $error_msg = 'Por favor selecciona un periodod válido.';
                   } elseif(!$domain_name && !$subdomain_name){
                     $error = 1;
                     $error_msg = 'El dominio/subdominio no puede estar vacío.';
                   } elseif(!$captcha_response && !$captcha_disabled){
                     $error = 1;
                     $error_msg = 'Por favor introduce el reCaptcha.';
                   }
                   
                   if(!$error && !$captcha_disabled){
                        $resp = recaptcha_check_answer ($settings['recaptcha_private_key'],
                                                        $_SERVER["REMOTE_ADDR"],
                                                        $_POST["recaptcha_challenge_field"],
                                                        $_POST["recaptcha_response_field"]);
                        if (!$resp->is_valid) {
                           $error = 1;
                           $error_msg = 'El reCaptcha introducido no es correcto.';
                        }                 
                   }

                break;
                case 2:
                   /*$fullname = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
                   $email_address = filter_var($_POST['email_address'], FILTER_SANITIZE_STRING);
                   $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
                   $postal_code = filter_var($_POST['postal_code'], FILTER_SANITIZE_STRING);
                   $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
                   $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
                   //$fullname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);*/
                
                   if(!$fullname){
                     $error = 1;
                     $error_msg = 'El nombre no puede estar vacío.';
                   } elseif(!$email_address){
                     $error = 1;
                     $error_msg = 'La dirección de correo no puede estar vacía.';
                   } elseif(!$username){
                     $error = 1;
                     $error_msg = 'El nombre de usuario no puede estar vacío.';
                   }
                   
                   if(!$error){
                    if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)){
                         $error = 1;
                         $error_msg = 'El correo no tiene un formato válido.';
                    }
                   }
                   
                   if(!$error){
                     if(checkUserName($username) <> 0){
                         $error = 1;
                         $error_msg = 'El nombre de usuario no está disponible.';                        
                     }
                   }
                break;
                case 3:
                                       
                break;
            }        
        }

        if(!$error){
          $section++;
        }
    }
    
    if(!isset($section)){
        $section = 1;
    }    
    
    if($section == 1){
        $packages = getPackages();
        if(is_array($packages) && !isset($packages[0])){
            $pkgs[0] = $packages;
            $packages = $pkgs;
            unset($pkgs);
        }
    } elseif($section == 3){
        $package_name = getPackageName($package_id);
        $period_info = getPeriodInfo($package_id, $period_id);
        if(is_array($period_info)){
            $period_duration = $period_info['duration'];
            $period_amount = number_format($period_info['amount'],2);
            $period_duration .= ' mes';
            if($period_info['duration'] > 1){
                $period_duration .= 'es';
            }
        }
    } elseif($section == 4){
	
        $new_user = registerUser();

        $invoice_url = '';
        if(!$new_user['error'] && $new_user['result']){
            if($settings['website_billing_url']){
                //$invoice_url = $settings['website_billing_url'].'/view_invoice.php?invoice='.$new_user['result'];
				$invoice_url = $new_user['result'];
            }
            unset($_POST);
        } else {
            $error = 1;
            $error_msg = "Se ha producido un error, no se puede crear la cuenta en este momento.";
            $result_msg = $new_user['result'];
        }
        
    }

  if(!isset($domain_name)){
    $domain_name = '';
  }
  
  if(!isset($subdomain_name)){
	$subdomain_name = '';
  }
  
  $js_header = js_header();  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo($settings['company_name']);?></title>
<script src="res/jquery-1.4.2.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/filoxenia.css">
<link rel="stylesheet" href="css/magnific-popup.css">
<script language="javascript" type="text/javascript">
<!--//--><![CDATA[//>
<!--
	<?php echo($js_header);?>  
//--><!]]>
</script>
<script>
	var plan = "<?php echo $_GET['plan']; ?>";
	
	switch(plan){
		case '1': plan = 5; break;
		case '2': plan = 3; break;
		case '3': plan = 4; break;
		default: plan = 0; break;
	}
	
	if(plan!=0){
		$(document).ready(function() {
			_select_pkg(plan);
		});
	}
</script>
</head>

<body>

<header class="contain-to-grid">
  <div class="row">
    <div class="large-12 column">
      <nav id="menu" class="top-bar">
        <ul class="title-area">
          <li class="name"><a href="http://billing.gadeira.es"><img src="images/logo.png" alt="logo"></a></li>
          <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
        </ul>
        <section class="top-bar-section">
          <ul class="right">
            <li><a href="http://www.gadeira.es/about.html">CONÓCENOS</a></li>
            <li><a href="http://www.gadeira.es/support.html">AYUDA</a></li>
            <li><a href="http://www.gadeira.es/login.html">INICIAR SESIÓN</a></li>
          </ul>
        </section>
      </nav>
    </div>
  </div>
</header>

<section id="main" role="main">
  <div class="breadcrumb-container">
    <div class="row">
      <div class="large-12 column">
        <nav class="breadcrumbs animated bounceInDown"><a href="http://billing.gadeira.es">Inicio</a> <a class="current" href="#">Planes</a></nav>
      </div>
    </div>
  </div>
  
	<div id="container">
    	<div id="header">&nbsp;</div>
            <?php
                if($error && $error_msg){
            ?>
                    <div class="error">
						<p class="text-center">
							<br>
							<?php echo($error_msg);?>
						<p>
                    </div>
                    <div class="clear">&nbsp;</div>
            <?php 
                }
            ?>
        <div id="content">
            <form name="frm_order" id="frm_order" action="" method="post">
                <input type="hidden" id="package_id" name="package_id" value="<?php echo($package_id);?>" />
                <input type="hidden" id="period_id" name="period_id" value="<?php echo($period_id);?>" />
                <input type="hidden" id="section" name="section" value="<?php echo($section);?>" />
                <?php 
                    if($section > 1){
                ?>
                <input type="hidden" id="domain" name="domain" value="<?php echo($domain_name);?>" />
				<input type="hidden" id="subdomain" name="subdomain" value="<?php echo($subdomain_name);?>" />
                <input type="hidden" id="domain_tid" name="domain_tid" value="<?php echo($domain_type_id);?>" />
                <?php
                    }
                ?>
                <?php 
                    if($section > 2){
                ?>
                <input type="hidden" name="fullname" value="<?php echo($fullname);?>" />
                <input type="hidden" name="email_address" value="<?php echo($email_address);?>" />
                <input type="hidden" name="address" value="<?php echo($address);?>" />
                <input type="hidden" name="postal_code" value="<?php echo($postal_code);?>" />
                <input type="hidden" name="phone" value="<?php echo($phone);?>" />
                <input type="hidden" name="username" value="<?php echo($username);?>" />
                <?php
                    }
                ?>
            <?php 
             switch($section){
                //hosting info
                case 1:
                  if(is_array($packages)){
            ?>
				
				
                
					<section class="part">
						<div class="row title">
						  <div class="large-12 column">
							  <h6><?php $plan=$_GET['plan']; if($plan!=0){echo "Plan elegido";}else{echo "Elija su plan";} ?></h6>
							  <span id ="planes"></span>
						  </div>
						</div>  
						<div class="row">
						  <div class="large-12 column">
							<div class="row">
								<?php 
									$plan=$_GET['plan'];
									if($plan == 1){
								?>
								
										<div class="large-5 column">
											<ul class="pricing-table">
											  <li class="title">Básico</li>
											  <li class="price">8€ <span>/ mes</span></li>
											  <li class="bullet-item"><strong>100</strong> Artículos</li>
											  <li class="bullet-item"><strong>2</strong> Fotos/Artículo</li>
											  <li class="bullet-item"><strong>12</strong> Categorías</li>
											  <li class="bullet-item">Multilenguaje</li>
											  <li class="bullet-item">Ayuda por correo electrónico</li>
											  <li class="bullet-item">Integración en redes sociales</li>
											  <li class="bullet-item">3 Estilos de Tienda</li>
											  <li class="bullet-item"></li>
											</ul>
										</div>
										<div class="large-1 column">
										</div>
										<div class="large-6 column">
											<div id="package_periods">
												<h6>Seleccione un período de servicio:</h6>
											</div>
											<div id="service_period">
												Por favor selecciona un plan.
											</div>
											<h6>Seleccione un dominio o subdominio:</h6>
											<div id="mostrardominio" class="button">Dominio</div> <div id="mostrarsubdominio" class="button">Subdominio</div>
											<div class="dominio" style="display: none;">
												<div id="domain_info" style="margin-right: 20px;">
												<h6>Dominio</h6>
													<input type="text" placeholder="www." name="domain" id="domain" size="15" value="<?php echo($domain_name);?>" />
													<div align="left" style="margin-left: 50px;">
														<p>
															<input type="radio" name="domain_tid" id="domain_type_own" value="1"<?php if(!$domain_type_id || $domain_type_id == 1){echo(' checked="checked"');}?> /> Tengo este dominio.<br />
															<!-- <input type="radio" name="domain_tid" id="domain_type_new" value="2"<?php if($domain_type_id == 2){echo(' checked="checked"');}?> /> Quiero comprar este dominio. -->
															<br>
														</p>
													</div>
												</div>
											</div>
											
											<div class="subdominio" style="display: none;">
												<div id="domain_info" style="margin-right: 20px;">
												<h6>Subdominio</h6>
													<div class="row">
														<div class="large-6 column">
															<input type="text" placeholder="www." name="subdomain" id="subdomain" size="15" value="<?php echo($subdomain_name);?>" />
														</div>
														<br>.gadeira.es
													</div>
													<div class="row">
														<div align="left" style="margin-left: 50px;">
															<p>
																<input type="radio" name="domain_tid" id="domain_type_own" value="1"<?php if(!$domain_type_id || $domain_type_id == 1){echo(' checked="checked"');}?> /> Quiero este subdominio.<br />
																<br>
															</p>
														</div>
													</div>
												</div>
											</div>
										</div>
								
								<?php
									}elseif($plan == 2){
								?>
									<div class="large-5 column">
										<ul class="pricing-table highlight">
										  <li class="title">Profesional</li>
										  <li class="price">14.95€ <span>/ mes</span></li>
										  <li class="bullet-item"><strong>250</strong> Artículos</li>
										  <li class="bullet-item"><strong>4</strong> Fotos/Artículo</li>
										  <li class="bullet-item"><strong>20</strong> Categorías</li>
										  <li class="bullet-item">Multilenguaje</li>
										  <li class="bullet-item">Ayuda por correo electrónico</li>
										  <li class="bullet-item">Integración en redes sociales</li>
										  <li class="bullet-item">Todos los estilos de Tienda</li>
										  <li class="bullet-item"></li>
										</ul>
									</div>
									<div class="large-1 column">
										</div>
										<div class="large-6 column">
											<div id="package_periods">
												<h6>Seleccione un período de servicio:</h6>
											</div>
											<div id="service_period">
												Por favor selecciona un plan.
											</div>
											<h6>Seleccione un dominio o subdominio:</h6>
											<div id="mostrardominio" class="button">Dominio</div> <div id="mostrarsubdominio" class="button">Subdominio</div>
											<div class="dominio" style="display: none;">
												<div id="domain_info" style="margin-right: 20px;">
												<h6>Dominio</h6>
													<input type="text" placeholder="www." name="domain" id="domain" size="15" value="<?php echo($domain_name);?>" />
													<div align="left" style="margin-left: 50px;">
														<p>
															<input type="radio" name="domain_tid" id="domain_type_own" value="1"<?php if(!$domain_type_id || $domain_type_id == 1){echo(' checked="checked"');}?> /> Tengo este dominio.<br />
															<!-- <input type="radio" name="domain_tid" id="domain_type_new" value="2"<?php if($domain_type_id == 2){echo(' checked="checked"');}?> /> Quiero comprar este dominio. -->
															<br>
														</p>
													</div>
												</div>
											</div>
											
											<div class="subdominio" style="display: none;">
												<div id="domain_info" style="margin-right: 20px;">
												<h6>Subdominio</h6>
													<div class="row">
														<div class="large-6 column">
															<input type="text" placeholder="www." name="subdomain" id="subdomain" size="15" value="<?php echo($subdomain_name);?>" />
														</div>
														<br>.gadeira.es
													</div>
													<div class="row">
														<div align="left" style="margin-left: 50px;">
															<p>
																<input type="radio" name="domain_tid" id="domain_type_own" value="1"<?php if(!$domain_type_id || $domain_type_id == 1){echo(' checked="checked"');}?> /> Quiero este subdominio.<br />
																<br>
															</p>
														</div>
													</div>
												</div>
											</div>
										</div>

								<?php
									}elseif($plan == 3){
								?>
									<div class="large-5 column">
										<ul class="pricing-table">
										  <li class="title">Premium</li>
										  <li class="price">21.95€ <span>/ mes</span></li>
										  <li class="bullet-item"><strong>3000</strong> Artículos</li>
										  <li class="bullet-item"><strong>7</strong> Fotos/Artículo</li>
										  <li class="bullet-item"><strong>200</strong> Categorías</li>
										  <li class="bullet-item">Multilenguaje</li>
										  <li class="bullet-item">Ayuda por correo electrónico</li>
										  <li class="bullet-item">Integración en redes sociales</li>
										  <li class="bullet-item">Todos los estilos de Tienda</li>
										  <li class="bullet-item"></li>
										</ul>
									</div>
									<div class="large-1 column">
										</div>
										<div class="large-6 column">
											<div id="package_periods">
												<h6>Seleccione un período de servicio:</h6>
											</div>
											<div id="service_period">
												Por favor selecciona un plan.
											</div>
											<h6>Seleccione un dominio o subdominio:</h6>
											<div id="mostrardominio" class="button">Dominio</div> <div id="mostrarsubdominio" class="button">Subdominio</div>
											<div class="dominio" style="display: none;">
												<div id="domain_info" style="margin-right: 20px;">
												<h6>Dominio</h6>
													<input type="text" placeholder="www." name="domain" id="domain" size="15" value="<?php echo($domain_name);?>" />
													<div align="left" style="margin-left: 50px;">
														<p>
															<input type="radio" name="domain_tid" id="domain_type_own" value="1"<?php if(!$domain_type_id || $domain_type_id == 1){echo(' checked="checked"');}?> /> Tengo este dominio.<br />
															<!-- <input type="radio" name="domain_tid" id="domain_type_new" value="2"<?php if($domain_type_id == 2){echo(' checked="checked"');}?> /> Quiero comprar este dominio. -->
															<br>
														</p>
													</div>
												</div>
											</div>
											
											<div class="subdominio" style="display: none;">
												<div id="domain_info" style="margin-right: 20px;">
												<h6>Subdominio</h6>
													<div class="row">
														<div class="large-6 column">
															<input type="text" placeholder="www." name="subdomain" id="subdomain" size="15" value="<?php echo($subdomain_name);?>" />
														</div>
														<br>.gadeira.es
													</div>
													<div class="row">
														<div align="left" style="margin-left: 50px;">
															<p>
																<input type="radio" name="domain_tid" id="domain_type_own" value="1"<?php if(!$domain_type_id || $domain_type_id == 1){echo(' checked="checked"');}?> /> Quiero este subdominio.<br />
																<br>
															</p>
														</div>
													</div>
												</div>
											</div>
										</div>
								
								<?php
									}else{
								?>
									<div class="large-4 column">
										<ul class="pricing-table">
										  <li class="title">Básico</li>
										  <li class="price">8€ <span>/ mes</span></li>
										  <li class="bullet-item"><strong>100</strong> Artículos</li>
										  <li class="bullet-item"><strong>2</strong> Fotos/Artículo</li>
										  <li class="bullet-item"><strong>12</strong> Categorías</li>
										  <li class="bullet-item">Multilenguaje</li>
										  <li class="bullet-item">Ayuda por correo electrónico</li>
										  <li class="bullet-item">Integración en redes sociales</li>
										  <li class="bullet-item">3 Estilos de Tienda</li>
										  <li class="cta-button"><a class="button" href="plan1.html">¡Este es mi plan!</a></li>
										</ul>
									  </div>
									  <div class="large-4 column">
										<ul class="pricing-table highlight">
										  <li class="title">Profesional</li>
										  <li class="price">14.95€ <span>/ mes</span></li>
										  <li class="bullet-item"><strong>250</strong> Artículos</li>
										  <li class="bullet-item"><strong>4</strong> Fotos/Artículo</li>
										  <li class="bullet-item"><strong>20</strong> Categorías</li>
										  <li class="bullet-item">Multilenguaje</li>
										  <li class="bullet-item">Ayuda por correo electrónico</li>
										  <li class="bullet-item">Integración en redes sociales</li>
										  <li class="bullet-item">Todos los estilos de Tienda</li>
										  <li class="cta-button"><a class="button" href="plan2.html">¡Este es mi plan!</a></li>
										</ul>
									  </div>
									  <div class="large-4 column">
										<ul class="pricing-table">
										  <li class="title">Premium</li>
										  <li class="price">21.95€ <span>/ mes</span></li>
										  <li class="bullet-item"><strong>3000</strong> Artículos</li>
										  <li class="bullet-item"><strong>7</strong> Fotos/Artículo</li>
										  <li class="bullet-item"><strong>200</strong> Categorías</li>
										  <li class="bullet-item">Multilenguaje</li>
										  <li class="bullet-item">Ayuda por correo electrónico</li>
										  <li class="bullet-item">Integración en redes sociales</li>
										  <li class="bullet-item">Todos los estilos de Tienda</li>
										  <li class="cta-button"><a class="button" href="plan3.html">¡Este es mi plan!</a></li>
										</ul>
									  </div>
								
								<?php
									}
								?>
					
					<div class="row">
					<div class="large-12 column">
						<div class="clear">&nbsp;</div>
						<div class="clear">&nbsp;</div>
						<?php if(!$captcha_disabled){?>
						<script type="text/javascript">
						 var RecaptchaOptions = {
							theme : 'clean'
						 };
						 </script>                    
						<div id="recaptcha" class="clear" align="center" style="">
							<h3>Por favor rellena el reCaptcha siguiente:</h3>
							<?php echo recaptcha_get_html($settings['recaptcha_public_key']);?>
						</div>
						<div class="clear">&nbsp;</div>
						<?php } 
						if($plan != 0){
						?>
							<div id="btn" class="right">
								<input type="submit" class="button" name="btn_submit" value="Siguiente &raquo;" class="submit">
							</div>
						 <?php } 
						 ?>
					 </div>
					 </div>

							</div>
						</div>
					</div>
				</section>
            <?php
                  } else {
                    echo('<p>&nbsp;</p><div align="center" style="color: #f00;font-size:15px;">No hay ningún paquete disponible.</div>');
                  }
                break;
                //account info
				
                case 2:
            ?>
				
				<section class="part">
			
					<div id="account_info">
						<div class="row">
							<div class="large-6 column">
								
									<div class="row title">
										<div class="large-8 column">
										  <h6>Datos de la empresa:</h6>
										</div>
									</div>
								
									<div class="row">
										<div class="large-8 column">
										  <label for="fullname">Nombre:</label>
										  <input type="text" name="fullname" id="fullname" size="15" value="<?php echo($fullname);?>" />
										</div>
									</div>
								
									<div class="row">
										<div class="large-8 column">
										  <label for="email_address">Correo:</label>
										  <input type="text" name="email_address" id="email_address" size="15" value="<?php echo($email_address);?>" />
										</div>
									</div>
									
									<div class="row">
										<div class="large-8 column">
										  <label for="address">Dirección:</label>
										  <textarea cols="25" rows="3" name="address" id="address"><?php echo($address);?></textarea>
										</div>
									</div>
									
									<div class="row">
										<div class="large-8 column">
										  <label for="postal_code">Código postal:</label>
										  <input type="text" name="postal_code" id="postal_code" size="15" value="<?php echo($postal_code);?>" />
										</div>
									</div>
									
									<div class="row">
										<div class="large-8 column">
										  <label for="phone">Teléfono:</label>
										  <input type="text" name="phone" id="phone" size="15" value="<?php echo($phone);?>" />
										</div>
									</div>
							</div>
							<div class="large-6 column">
								
								<div class="row title">
										<div class="large-8 column">
										  <h6>Datos de la tienda:</h6>
										</div>
								</div>
								
								<div class="row">
									<div class="large-8 column">
										<label for="username">Nombre de usuario:</label>
										<input type="text" name="username" id="username" size="10" value="<?php echo($username);?>" />
									</div>
								</div>
								
								<div class="row">
									<div class="large-8 column">
										<label for="shopname">Nombre de tienda:</label>
										<input type="text" name="shopname" id="shopname" size="15" value="<?php echo($shopname);?>" />
									</div>
								</div>
								
							</div>
						</div>
						
					</div> 

					
					<div class="row">          
						<div id="btn" class="right">
							<!-- <input type="submit" name="btn_submit" value="&laquo; Back" class="submit btn" onclick="$('#section').val(0);">&nbsp; -->
							<input type="submit" class="button" name="btn_submit" value="Siguiente &raquo;" class="submit">
						 </div>
					 </div>
				</section>
            <?php
                break;
                //review order
                 case 3:
            ?>
				<section class="part">
					<div id="review_order">
					
						<div class="row title">
										<div class="large-12 column">
										  <h6>Por favor, revisa la información:</h6>
										</div>
									</div>
						<div class="row spacy">
									
									<div class="large-4 column">
										<div class="row title">
											<h6>Información del plan:</h6>
										</div>
										<h4>Plan:</h4>
										<?php echo($package_name);?> por <?php echo($period_duration);?> a <?php echo($period_amount);?> <?php echo($settings['currency']);?>
										<br>
										
											<?php
												// Si el dominio esta vacio ponemos el subdominio
												if(!$domain_name){
													$domain_name = $subdomain_name;
													$domain_type = 'Quiero este subdominio';
												}else{
													if(strpos($domain_name, '.gadeira.es')){echo "<h4>Subdominio:</h4>";}else{echo "<h4>Dominio:</h4>";}
													$domain_type = ($domain_type_id == 1) ? 'Tengo este dominio':'Quiero comprar este dominio';
												}
											?>
											<?php echo($domain_name);?>
										<br>
									</div>
									
									<div class="large-4 column">
										<div class="row title">
											<h6>Información de la cuenta:</h6>
										</div>
											<h4>Nombre de la empresa:</h4>
											<?php echo($fullname);?>
											<h4>Correo:</h4>
											<?php echo($email_address);?>
											<h4>Dirección:</h4>
											<?php 
												if(!$address){
													echo('No proporcionada');
												} else {
												  echo($address);
												}
											?>
											<h4 class="no-top-margin">Código postal:</h4>
											<?php 
												if(!$postal_code){
													echo('No proporcionado');
												} else {
												  echo($postal_code);
												}
											?>
											<h4 class="no-top-margin">Teléfono:</h4>
											<?php 
												if(!$phone){
													echo('No proporcionado');
												} else {
												  echo($phone);
												}
											?>
									</div>
									
									<div class="large-4 column">
										<div class="row title">
											<h6>Información de la tienda:</h6>
										</div>
										<h4>Nombre de usuario:</h4>
										<?php echo($username);?>
										<h4>Nombre de la tienda:</h4>
										<?php echo($shopname);?>
									</div>
						</div>
									
						<div class="row">
							<div id="btn" class="right">
								<input type="submit" name="btn_submit" value="&laquo; Atrás" class="submit btn button" onclick="$('#section').val(1);">&nbsp;<input type="submit" name="btn_submit" value="Confirmar &raquo;" class="submit button">
							</div>
						</div>
					</div>
				</section>
            <?php        
                break;
                //Thank you
                 case 4:
            ?>
			
				<section class="part">
					<?php if(!$error){?>
					<div class="row title">
						<h6>Gracias por confiar en nosotros.</h6>
					</div>
					<?php } else {?>
					<div class="row title">
						<h6>¡Error!</h6>
					</div>					
					<?php } ?>
					<div class="clear">&nbsp;</div>
					<div id="payment_thanks">
						<?php if(!$error){?>
						<div class="row">		
							<div class="large-12 column">
								<div align="center">
									Se ha mandado un correo a <?php echo($email_address);?> con la dirección para ver su factura.
									<br /><br />
									<a class="button" href="<?php echo "http://billing.gadeira.es/factura/" . $invoice_url;?>">Ver factura</a>
									<?php } else {?>
										<span class="error" style="font-size: 13px;"><?php echo($result_msg);?></span>
										<br/>
										Haz click <a href="index.php">aquí</a> para empezar de nuevo.
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</section>
            <?php
             }
            ?>
            </form>
        </div>
    </div>
	
<!-- FOOTER -->

<footer>

  <div class="row spacy">

    <!-- EQUIPO -->

    <div class="large-5 column">
      <h4>Nuestro Equipo</h4>
      <p>Gaderia es una empresa joven cuyo principal objetivo es cubrir las necesidades del cliente y proporcionar unos servicios que sirvan como impulso a la economía de cualquier pequeña o mediana empresa.</p>
      <ul class="large-block-grid-5 popup">
        <li><a href="images/demo/person1.jpg"><img src="images/demo/person1_s.jpg" alt=""></a></li>
        <li><a href="images/demo/person2.jpg"><img src="images/demo/person2_s.jpg" alt=""></a></li>
        <li><a href="images/demo/person3.jpg"><img src="images/demo/person3_s.jpg" alt=""></a></li>
        <li><a href="images/demo/person4.jpg"><img src="images/demo/person4_s.jpg" alt=""></a></li>
        <li><a href="images/demo/person5.jpg"><img src="images/demo/person5_s.jpg" alt=""></a></li>
      </ul>
    </div>

    <!-- SERVICIOS -->

    <div class="large-3 column">
      <h4>Servicios</h4>
      <ul class="side-nav">
        <li><a href="http://www.gadeira.es/blog.html">Blog</a></li>
        <li><a href="http://www.gadeira.es/about.html">Conócenos</a></li>
        <li><a href="http://www.gadeira.es/404.html">Términos de Servicio</a></li>
        <li><a href="http://www.gadeira.es/404.html">Política de Privacidad</a></li>
        <li><a href="http://www.gadeira.es/contact.html">Contacto</a></li>
      </ul>
    </div>

    <!-- CONTACTO -->

    <div class="large-4 column">
      <h4>Contacto</h4>
        <div class="row">

          <div class="small-3 column">
            <h6>Dirección:</h6>
          </div>

          <div class="small-9 column">
            <p>C/ Chile, 1. 11002 Cádiz</p>
          </div>

        </div>

        <div class="row">

          <div class="small-3 column text">
            <h6>Teléfono:</h6>
          </div>

          <div class="small-9 column text">
            <p>629 44 99 67<br></p>
          </div>

        </div>

        <div class="row">

          <div class="small-3 column text">
            <h6>Email:</h6>
          </div>

          <div class="small-9 column text">
            <p><a href="mailto:contacto@gadeira.es">contacto@gadeira.es</a><br></p>
          </div>

        </div>

    </div>
  </div>

  <div class="row">
    <div class="large-12 column">
      <hr>
    </div>
  </div>

  <!-- COPYRIGHT Y REDES SOCIALES -->

  <div class="row">
      <p class="small-12 large-4 large-uncentered column copyright">Copyright &copy; 2013 Gadeira.</p>
    <p class="small-12 large-8 column social">
      <a href="mailto:contacto@gadeira.es"><i class="icon-envelope"></i></a> 
      <a href="http://www.facebook.com/" target="_blank"><i class="icon-facebook"></i></a>
      <a href="http://www.twitter.com/" target="_blank"><i class="icon-twitter"></i></a> 
      <a href="http://www.linkedin.com/" target="_blank"><i class="icon-linkedin"></i></a> 
  </div>

</footer>

<!-- SCRIPTS -->

    <script src="js/vendor/jquery.js"></script>
    <script src="js/vendor/jquery.magnific-popup.js"></script>
    <script src="js/foundation/foundation.js"></script>
    <script src="js/foundation/foundation.topbar.js"></script>
    <script src="js/foundation/foundation.section.js"></script>
    <script src="js/filoxenia.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/demo.js"></script>
    <script src="js/scroll.js"></script>
</body>
</html>
