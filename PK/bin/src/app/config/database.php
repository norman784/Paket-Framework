<?php
/*-------------------------------------------------------------
Set the database connection
-------------------------------------------------------------*/

switch ($_SERVER['HTTP_HOST']) {
// development
case 'localhost':
	$config['dbhost']     = 'localhost';
	$config['dbuser']     = 'root';
	$config['dbpass']     = '';
	$config['dbname']     = 'test';
	$config['dbengine']   = 'mysql';
	$config['dbport']     = 0;
	$config['dbprefix']     = '';
	
	$config['debug']['enabled'] = true;
	
	break;
// production
default:
	$config['dbhost']     = 'localhost';
	$config['dbuser']     = '';
	$config['dbpass']     = '';
	$config['dbname']     = '';
	$config['dbengine']   = 'mysql';
	$config['dbport']     = 0;
	$config['dbprefix']     = '';
	
	$config['debug']['enabled'] = false;
	
//	$config['debug']['enabled'] = true;
//	$config['debug']['show_all'] = true;
	
	break;
}

//defined('KEYWORDS') or define('KEYWORDS', '');