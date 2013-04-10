<?php
/*-------------------------------------------------------------
Definimos las rutas
-------------------------------------------------------------*/

define('PK_MODEL_DIR', PK_APP_DIR.'model/');
define('PK_CONTROLLER_DIR', PK_APP_DIR.'controller/');
define('PK_VIEW_DIR', PK_APP_DIR.'view/');
define('PK_LAYOUT_DIR', PK_APP_DIR.'layouts/');
define('PK_UPLOAD_DIR', PK_APP_DIR.'uploads/');

if (!is_dir(PK_UPLOAD_DIR))
	mkdir(PK_UPLOAD_DIR, 0777);

@chmod(PK_UPLOAD_DIR, 0777);

/*-------------------------------------------------------------
Definimos los archivos por defecto para las vistas
-------------------------------------------------------------*/

define('ERR_403', PK_APP_DIR.'view/error/403.php');
define('ERR_403_DEF', PK_ROOT_DIR.'PK/view/error/403.php');

define('ERR_404', PK_APP_DIR.'view/error/404.php');
define('ERR_404_DEF', PK_ROOT_DIR.'PK/view/error/404.php');

define('VIEW_CONTROLLER', 'home');
define('VIEW_DEF_CONTROLLER', 'default');

define('VIEW_ACTION', 'index');
define('ACTION_SUFIX', 'Action');
define('ACTION_PREFIX', ACTION_SUFIX); // es un sufijo, no un prefijo :S

define('SITE_TITLE', 'Esto es Brandon'); 

/*-------------------------------------------------------------
Definimos los archivos de imagen por defecto
-------------------------------------------------------------*/

define('DEF_IMG', '/_default/_default');
?>