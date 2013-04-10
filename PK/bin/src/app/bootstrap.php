<?php
/*-------------------------------------------------------------
Comprime la pagina para que descarge mas rapido
-------------------------------------------------------------*/

ob_start("ob_gzhandler");

if(!defined('PK_ROOT_DIR')) {
	$x = 0;
	$root_dir = dirname(realpath(__FILE__));
	while (!file_exists($root_dir.'/PK_root')):
		$root_dir = dirname($root_dir);
		++$x;
		
		if ($x >= 5)
			die('<h1>Error en la aplicacion</h1><p>No se han encontrado los archivos del sistema</p>');
	endwhile;
	
	define('PK_ROOT_DIR', $root_dir.'/');
}

set_include_path(PK_ROOT_DIR . PATH_SEPARATOR . get_include_path());

if(!defined('PK_APP_DIR')) {
	$x = 0;
	$root_dir = dirname(realpath(__FILE__));
	while (!file_exists($root_dir.'/PK_project')):
		$root_dir = dirname($root_dir);
		++$x;
		
		if ($x >= 5)
			die('<h1>Error en la aplicacion</h1><p>No se han encontrado los archivos del proyecto</p>');
	endwhile;
	
	define('PK_APP_DIR', $root_dir.'/');
}

defined('MAIL_CONTACT') or define('MAIL_CONTACT', 'info@estoesbrandon.com');
defined('DEF_LANG') or define('DEF_LANG', 'es');
defined('SALT') or define('SALT', strtr(PK_APP_DIR, array(dirname(PK_APP_DIR)=>'')));

/*-------------------------------------------------------------
Iniciamos la sesion
-------------------------------------------------------------*/
	
session_start(PK_ROOT_DIR);

include_once 'config/database.php';
include_once 'config/routes.php';

/*-------------------------------------------------------------
Definimos la configuracion del sitio
-------------------------------------------------------------*/
	
$charset = 'utf-8';

define('CHARSET', $charset);

define('DEF_LIMIT', 20);

$config['extended_url'] = false;
$config['extended_url'] = true;

$config['friendly_url'] = 'enabled';
//$config['friendly_url'] = 'disabled';

/*-------------------------------------------------------------
Incluimos los archivos necesarios
-------------------------------------------------------------*/
include_once 'PK/PK.php';

/*-------------------------------------------------------------
Traemos los controles
-------------------------------------------------------------*/	

$controllers = split(',', 'default,'.$_GET['url']['controller']);

foreach ($controllers as $c):
	get('controller', $c);
endforeach;

/*-------------------------------------------------------------
Traemos el layout
-------------------------------------------------------------*/	

$layout = 'default';

if (file_exists(PK_LAYOUT_DIR.strtolower($_GET['url']['layout']).'.php')) {
	$layout = strtolower($_GET['url']['layout']);
} elseif (file_exists(PK_LAYOUT_DIR.strtolower($_GET['url']['controller']).'.php')) {
	$layout = strtolower($_GET['url']['controller']);
}

get('layouts', $layout);

/*-------------------------------------------------------------
Comprime la pagina para que descarge mas rapido
-------------------------------------------------------------*/	

ob_end_flush();
?>