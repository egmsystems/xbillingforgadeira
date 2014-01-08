<?php
// Global variables of script
define("HTDOCS", "/var/zpanel/hostdata/");
define("BASIC_PLAN","./opencart-1.5.6/upload");
define("PROFESIONAL_PLAN","./opencart-1.5.6/upload");
define("PREMIUM_PLAN","./opencart-1.5.6/upload");

function error($numero,$texto){
	$ddf = fopen('error.log','a');
	fwrite($ddf,"[".date("r")."] Error $numero: $texto");
	fclose($ddf);
	exit();
}

function recurse_rm($dst){ 
	$dir=opendir($dst); 
	while($file=readdir($dir)){ 
		if($file!='.' && $file !='..'){ 
			if(is_dir($dst.'/'.$file)){ 
				recurse_rm($dst.'/'.$file); 
				rmdir($dst.'/'.$file); 
			}else{
				unlink($dst.'/'.$file); 
			} 
		} 
	} 
	closedir($directorio); 
	rmdir($dst);
}

function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}

function recursive_chmod ($path, $filePerm=0755, $dirPerm=0755) {
    if (!file_exists($path)) {
         return(false);
    }

    if (is_file($path)) {
         chmod($path, $filePerm);
    } elseif (is_dir($path)) {
         $foldersAndFiles = scandir($path);
         $entries = array_slice($foldersAndFiles, 2);
         foreach ($entries as $entry) {
            recursive_chmod($path."/".$entry, $filePerm, $dirPerm);
         }
         chmod($path, $dirPerm);
    }
    return(true);
}

function create_shop ($username,$password,$email,$plan){
	// Select the plan
	$template
	
	// Select the destiny foolder
	$destinyfoolder = HTDOCS.$user.'/public_html';
	
	// Databasename
	$bd_name = 'oc_'.$username;
	
	// 1º Se crea la carpeta de la tienda.
	if(!mkdir($destinyfoolder,0755)){
		error('001','No se pudo crear la carpeta.');
	}

	// 2º Se copia el contenido del carpeta del plan escogido
	recurse_copy($template,$destinyfoolder);

	// 3º Se modifica el nombre de los ficheros de configuracion.
	if(!rename($destinyfoolder.'/config-dist.php',$destinyfoolder.'/config.php') or !rename($destinyfoolder.'/admin/config-dist.php',$destinyfoolder.'/admin/config.php')){
		error('002','No se pudieron renombrar los ficheros config.');
	}

	// 4º Se proporcionan permisos de escritura a los ficheros correspondientes
	if(!recursive_chmod($destinyfoolder)){
		error('003','No se pudieron modificar los permisos de los ficheros y directorios.');
	
	}

	// 5º create de database
	$enlace =  mysql_connect('localhost', 'root', '');
	if (!$enlace) {
		error('006','No se pudo conectar a la bd.');
	}
	$sql = 'CREATE DATABASE '.$bd_name;
	if (!mysql_query($sql, $enlace)) {
		error('007','Error al crear la base de datos: ' . mysql_error() . "\n");
	}
	mysql_close($enlace);

	// 6º excecute opencart's install script
	shell_exec('php '.$destinyfoolder.'/install/cli_install.php install 
								--db_host localhost \
	                            --db_user root \
	                            --db_password "" \
	                            --db_name '.$bd_name.' \
	                            --username '.$username.' \
	                            --password '.$password.' \
	                            --email '.$email.' \
	                            --agree_tnc yes \
	                            --http_server http://localhost/'.$username.'/');
	
	// 7º Finally, we remove the install foloder
	recurse_rm($destinyfoolder.'/install');
}                            
?>