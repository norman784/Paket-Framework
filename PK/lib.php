<?php
defined('MAIL_WEBMASTER') or define('MAIL_WEBMASTER', 'normanpaniagua@gmail.com');

$i18n_def['en'] = array(
	/* controllers */
	'people', 'files',
	/* fields */
	'content', 'category', 'title', 'subtitle', 'introduction', 'published', 'name', 
	'message', 'description', 'show_images',
);

$i18n_def['es'] = array(
	/* controllers */
	'usuarios', 'archivos',
	/* fields */
	'contenido', 'categoria', 'titulo', 'subtitulo', 'introduccion', 'publicado', 'nombre', 
	'mensaje', 'descripcion', 'mostrar_imagenes',
);

/**
 * Reemplaza algunos caracteres del get extendido
 */
function get_extended() {
	global $config, $GET;
	
	if ($config['extended_url'] != true)
		return;
	
	$GET['url']['controller'] = str_replace('-', '_', $GET['url']['controller']);
	$GET['url']['action'] = str_replace('-', '_', $GET['url']['action']);
} // get_extended()
/**
 * Quita los tag no permitidos de la cadena
 *
 * @param string/array $str
 * @return string
 */
function disallowed_tags($str) {
	$disalowedtags = array("script",
                        "object",
                        "iframe",
                        "image",
                        "applet",
                        "meta",
                        "form",
                        "onmouseover",
                        "onmouseout");
	$output = '';
	
	if (is_array($str)) {
		foreach ($str as $index=>$value):
			$output[$index] = disallowed_tags($value);
		endforeach;
	} else {
		foreach ($disalowedtags as $tag):
			$output = eregi_replace( "<".$tag."[^>]*>[^>]*</".$tag.">", "", $str);
		endforeach;
	} // else
	
	return $output;
} // disallowed_tags()
/**
 * Incluimos los modelos, vistas o controladores
 *
 * @param string $mode
 * @param string $mod
 * @param string $action
 */
function get($mode='controller', $controller='', $action=''){
	global $_GET, $_POST, $_SESSION, $row_Template, $config, $acl;
	return;
//	echo "mode: $mode<br>mod: $mod<br>action: $action<hr>";
	
	/*-------------------------------------------------------------
	Si el modulo esta vacio traemos los datos de..
	... $_GET['url']['controller']
	... VIEW_MOD
	... VIEW_DEF_MOD
	-------------------------------------------------------------*/
	$controller = trim($controller);
	
	if (empty($controller))
		$controller = trim($_GET['controller']);

	if (empty($controller))
		$controller = trim($_GET['url']['controller']);
		
	if (empty($controller))
		$controller = VIEW_CONTROLLER;
		
	if (empty($controller))
		$controller = VIEW_DEF_CONTROLLER;
	
	/*-------------------------------------------------------------
	Si la accion esta vacio traemos los datos de..
	... $_GET['url']['action']
	... VIEW_ACTION
	-------------------------------------------------------------*/
		
	if (empty($action))
		$action = $_GET['url']['action'];
	
	if (empty($action))
		$action = VIEW_ACTION;

	$name = trim($controller);
	$name = strtolower($name);
	$eval = array();
	
	$plugins = app_get('', 'plugins');
		
	switch ($mode) {
		case 'controller':
		case 'controllers':
			if (!isset($_GET['url']['plugin'])) {
				$dirs = array(
					PK_CONTROLLER_DIR,
					PK_PLUGIN_DIR.$_GET['url']['plugin'].'/controller/',
					PK_GLOBAL_PLUGIN_DIR.$_GET['url']['plugin'].'/controller/'
				);
			} else {
				$dirs = array(
					PK_PLUGIN_DIR.$_GET['url']['plugin'].'/controller/',
					PK_GLOBAL_PLUGIN_DIR.$_GET['url']['plugin'].'/controller/',
					PK_CONTROLLER_DIR
				);
			}
			
			foreach ($plugins as $plugin):
				$dirs[count($dirs)] = PK_PLUGIN_DIR.$plugin.'/controller/';
				$dirs[count($dirs)] = PK_GLOBAL_PLUGIN_DIR.$plugin.'/controller/';
			endforeach;
			
			$path = null;
			$controller .= '_controller';
			$sfile = $name.'_controller.php';
			$eval[0] = '$method = new  '.$controller.';'."\n";
			$eval[1] = '$method->'.$action.ACTION_PREFIX.'();';
			
//			echo "<br>=============================================================<br>";
			
			foreach ($dirs as $dir) {
//				echo "<br>".$dir.$sfile;
				
				if (!file_exists($dir.$sfile))
					continue;
				$sfile = $dir.$sfile;
				
//				echo " exists<br>";
				break;
			}
			
//			echo "<br><br>=============================================================<br>";
			
			break;
		case 'model':
		case 'models':
			if (!isset($_GET['url']['plugin'])) {
				$dirs = array(
					PK_MODEL_DIR,
					PK_PLUGIN_DIR.$_GET['url']['plugin'].'/model/',
					PK_GLOBAL_PLUGIN_DIR.$_GET['url']['plugin'].'/model/'
				);
			} else {
				$dirs = array(
					PK_PLUGIN_DIR.$_GET['url']['plugin'].'/model/',
					PK_GLOBAL_PLUGIN_DIR.$_GET['url']['plugin'].'/model/',
					PK_MODEL_DIR
				);
			}
			
			foreach ($plugins as $plugin):
				$dirs[count($dirs)] = PK_PLUGIN_DIR.$plugin.'/model/';
				$dirs[count($dirs)] = PK_GLOBAL_PLUGIN_DIR.$plugin.'/model/';
			endforeach;
			
			$path = null;
			$sfile = $name.'.php';
			
			foreach ($dirs as $dir) {
				if (!file_exists($dir.$sfile))
					continue;
				$sfile = $dir.$sfile;
				break;
			}
			
			break;
		case 'view':
		case 'views':
			if ($row_Template['access'][$_GET['url']['controller']][$_GET['url']['action']] === false && $controller != 'layouts') {
				$sfile = ERR_403;
				break;
			}
			
			if (!isset($_GET['url']['plugin'])) {
				$dirs = array(
					PK_VIEW_DIR,
					PK_PLUGIN_DIR.$_GET['url']['plugin'].'/view/',
					PK_GLOBAL_PLUGIN_DIR.$_GET['url']['plugin'].'/view/',
					PK_DEF_VIEW_DIR
				);
			} else {
				$dirs = array(
					PK_PLUGIN_DIR.$_GET['url']['plugin'].'/view/',
					PK_GLOBAL_PLUGIN_DIR.$_GET['url']['plugin'].'/view/',
					PK_VIEW_DIR,
					PK_DEF_VIEW_DIR
				);
			}
			
			foreach ($plugins as $plugin):
				$dirs[count($dirs)] = PK_PLUGIN_DIR.$plugin.'/view/';
				$dirs[count($dirs)] = PK_GLOBAL_PLUGIN_DIR.$plugin.'/view/';
			endforeach;
			
			$path = null;
			$sfile = $name.'/'.strtolower($action).'.php';
			
			foreach ($dirs as $dir) {
				if (!file_exists($dir.$sfile))
					continue;
				$sfile = $dir.$sfile;
				break;
			}
			
			if (!file_exists($sfile)) {
				if (file_exists(PK_SCAFFOLDING_DIR.strtolower($action).'.php') && $config['scaffolding'] === true) {
					$sfile = PK_SCAFFOLDING_DIR.strtolower($action).'.php';
				}
			}
			
			break;
		case 'layouts':
		case 'layout':
			if (!isset($_GET['url']['plugin'])) {
				$dirs = array(
					PK_LAYOUT_DIR,
					PK_PLUGIN_DIR.$_GET['url']['plugin'].'/layouts/',
					PK_GLOBAL_PLUGIN_DIR.$_GET['url']['plugin'].'/layouts/',
					PK_DEF_LAYOUT_DIR
				);
			} else {
				$dirs = array(
					PK_PLUGIN_DIR.$_GET['url']['plugin'].'/layouts/',
					PK_GLOBAL_PLUGIN_DIR.$_GET['url']['plugin'].'/layouts/',
					PK_LAYOUT_DIR,
					PK_DEF_LAYOUT_DIR
				);
			}
			
			foreach ($plugins as $plugin):
				$dirs[count($dirs)] = PK_PLUGIN_DIR.$plugin.'/layouts/';
				$dirs[count($dirs)] = PK_GLOBAL_PLUGIN_DIR.$plugin.'/layouts/';
			endforeach;
			
			if ($config['scaffoldding'] === true)
				$dirs[count($dirs)] = PK_SCAFFOLDING_DIR;
			
			$path = null;
			$sfile = $name.'.php';
			
			foreach ($dirs as $dir) {
				if (!file_exists($dir.$sfile)) {
					if (!file_exists($dir.'default.php'))
						continue;
					else
						$sfile = 'default.php';
				}
				
				$sfile = $dir.$sfile;
				break;
			}
			
			break;
		default:
			$sfile = ERR_404;
			break;
	} // switch
	
	PK_debug('Try to get', $sfile);
	
	if (!file_exists($sfile)) {
		PK_debug($mode, $controller, array('class'=>'error'));
		PK_debug('File not exists ', $sfile);
		
		$sfile = ERR_404;
		
		switch ($mode) {
		case 'controller':
			return;
		case 'model':
			return;
		case 'view':
			if ($row_Template['access'][$_GET['url']['controller']][$_GET['url']['action']] === false && $controller != 'layouts')
				$sfile = ERR_403_DEF;
		} // switch
		
		if (!file_exists($sfile))
			$sfile = ERR_404_DEF;
	} else {// if
		PK_debug('MODE', '<b>'.$mode.'</b><br>'.$controller, array('class'=>'success'));
	}
	
	PK_debug($mode,'','',__FILE__, __LINE__);
	PK_debug('$sfile', $sfile);
	
	include_once($sfile);
	
	if (!empty($eval) && class_exists($controller)) {
		eval($eval[0]);
		PK_debug('$eval[0]', $eval[0]);
		
//		echo '<hr>'.print_r($controller, true);
//		echo '<br>'.$eval[1].' = '.method_exists($controller,$action.ACTION_PREFIX);
		
		
		//if (is_callable(array($controller,$action))) {
		if (!method_exists($controller,$action.ACTION_PREFIX)) {
			$action = VIEW_ACTION;
			$eval[1] = '$method->'.$action.ACTION_PREFIX.'();';
		} // if
		
//		echo '<br>'.$eval[1].' = '.method_exists($controller,$action.ACTION_PREFIX).'<br>';
		
		if (method_exists($controller,$action.ACTION_PREFIX)) {
			eval($eval[1]);
			PK_debug('$eval[1]', $eval[1]);
		} // if
	} // if
} // get()
/**
 * Genera la salida para los mensajes de debug
 *
 * @param string $title
 * @param string $details
 * @param array $mode
 */
function PK_debug($title='', $details='', $param='', $file='', $line=''){
	global $fb_msg, $config, $log_msg;
	
	if (is_array($param)) {
		if (!empty($param['file']))
			$file = $param['file'];
			
		if (!empty($param['line']))
			$line = $param['line'];
			
		if (!empty($param['class']))
			$class = $param['class'];
			
		if (!empty($param['mode']))
			$mode = $param['mode'];
	} else {
		$mode = $param;
	}
	
	if ($mode == 'output') {
		$config['output_called'] = true;
		if ($config['debug']['enabled'] === true) {
			if (empty($fb_msg)) {
				$fb_msg = $log_msg;
				
				if (empty($fb_msg))
					$fb_msg = TXT_EMPTY;
			}
			
			echo '<div class="container_12" id="debug">'."\n".'<div class="grid_12">'."\n".'<h3><center>Debug msg<center></h3>'."\n".'</div>'."\n".'<div class="clearfix">'."\n".'</div>'."\n".$fb_msg."\n".'</div>';
		} else  {
			if (!empty($log_msg)) {
				$sender = 'PK Error Log<errorlog@pkfw.com>';
				$headers = "From: $sender\nContent-Type: text/html; charset=".CHARSET;
				$log_msg = '<h2>'.SERVER_URL.'</h2><p>'.request_uri().'</p><div class="container_12" id="debug"><div class="grid_12"><h3><center>Debug msg<center></h3></div><div class="clearfix"></div>'.$log_msg.'</div>';
//				@mail(MAIL_WEBMASTER, 'Error Log :: '.SERVER_URL, $log_msg, $headers);
				$log_msg = NULL;
			}
		}
		$fb_msg = NULL;
		return;
	} // if
	
	if ($config['debug']['show_all'] !== true && $class != 'error') {
		return;		
	}
	
	if (!empty($details) || is_array($details)) {
		$br = "<br>\n";
		if (is_array($details)) {
			$details = print_r($details, true);
		}
		$details = '<pre>'."\n".$details."\n".'</pre>'."\n";
	} // if
		
	if (!empty($file)) {
		$br = '';
		$file = $br.'<em>'."\n".$file;
		if (!empty($line))
			$file .= ' en la linea '.$line."\n";
		$file .= '</em>'."\n";
	} // if
		
	if (!empty($title) || !empty($details)) {
		$msg = '<div class="grid_3"'.((strpos($title, 'BOTTLENECK') !== false)?' id="bottleneck"':'').'><h5>'.$title.'</h5></div><div class="grid_9"'.((strpos($title, 'BOTTLENECK') !== false)?' id="bottleneck"':'').'>'.$details.$file.'</div><div class="clearfix"></div>';
		
		if (!empty($param['class']))
			$msg = '<div class="'.$param['class'].'">'."\n".$msg."\n".'</div>';
		
		if ($config['debug']['enabled'] === true) {
			$fb_msg .= $msg;
		} else {
			$log_msg .= $msg;
		}
	}
} // PK_debug()

function PK_bottleneck($mode='start', $title='') {
	return;
	
	global $timeparts,$starttime;
	
	if ($mode == 'end') {
		if (!empty($title))
			$title = ' ['.$title.']';
		
		$endtime = $timeparts[1].substr($timeparts[0],1);
		PK_debug('BOTTLENECK'.$title, bcsub($endtime,$starttime,6));
	} else {
		$timeparts = explode(" ",microtime());
		$starttime = $timeparts[1].substr($timeparts[0],1);
		$timeparts = explode(" ",microtime());
	}
}
/**
 * Genera la url amigable a partir del query string
 *
 * @param string $value
 * @param string $output
 * @return string
 */
function url($value, $output='echo') {
	global $config;
	
	$value = html_entity_decode($value);
	
	$var = str_replace('?', '', $value);
	$var = explode('&', $var);
	$value = '';
	$amp = '?';
	$bar = '';
	
	foreach ($var AS $val):
		
		$val = explode('=', $val);
		$val[0] = strip_special_chars(trim($val[0]));
		$val[1] = strip_special_chars(trim($val[1]));
		
		if (empty($val[1]) || ($val[0] == 'action' && $val[1] == VIEW_ACTION))
			continue;
		
		if ($config['friendly_url'] == 'enabled') {
			$value .= $bar.$val[1];
			$bar = '/';
		} else {
			$value .= $amp.$val[0].'='.$val[1];
			$amp = '&';
		} // else
	endforeach;
	
	$server_url = SERVER_URL;
	
	if (substr($server_url, -1) != '/')
		$server_url .= '/';
	
	$value = $server_url.$value;
	
	if ($output != 'return')
		echo $value;
	
	return $value;
} // url()
/**
 * Corta la url $_GET['url'] en:
 * $_GET['lang']
 * $_GET['url']['controller']
 * $_GET['url']['action']
 * $_GET['url']['p']
 * $_GET['url']['id']
 * $_GET['q']
 */
function split_url() {
	global $_GET;
	
	$url = $_GET['url'];
	$url = split('/', $url);
	
	$key = array('lang', 'mod', 'func', 'p', 'val', 'q');
	$eval = array('strlen($val) == 2', 'true', 'true', "strpos('pag-')", 'true', 'true');
	
	foreach ($url as $v) {
		if (strpos($v, '_')) {
			$v = split('_', $v);
			
			foreach ($v as $val) {
				if (eval($eval[0])) {
					$_GET[$key[0]] = $v;
				}
				
				array_shift($key);
				array_shift($eval);
			}
		} else {
			$val = $v;
			if (eval($eval[0])) {
				$_GET[$key[0]] = $v;
			}
			
			array_shift($key);
			array_shift($eval);
		}
	}
	
}
/**
 * Setea la llave del array para el url
 *
 * @param string $cadena_a_ser_analizada
 * @param int $pos
 * @param array $key
 * @return string $llave_del_array
 */
function set_key($cadena_a_ser_analizada, &$pos, &$key) {
	
	if ((int)$pos == 0)
		$pos = 0;

	if (!is_array($key))
		$key = array();
	
	if (strlen($cadena_a_ser_analizada) == 2 && $pos == 0) {
		$llave_del_array = 'lang';
	} elseif ($pos == 0 || ($pos == 1 && !in_array('mod', $key))) {
		$llave_del_array = 'mod';
	} elseif ($pos == 1)
	
	$key[$pos] = $llave_del_array;
	$pos++;
	
	return $llave_del_array;
}
/**
 * Extrae los caracteres especiales de una cadena de texto
 *
 * @param string $cadena_a_ser_convertida
 * @param string $mode
 * @return string
 */
function strip_special_chars($cadena_a_ser_convertida, $mode='nospaces') {
	
	$search = array(
		/* A, E, I, O, U, N (acentos y enhes) */
		193, 201, 205, 211, 218, 209,
		/* a, e, i, o, u, n (acentos y enhes) */
		225, 233, 237, 243, 250, 241,
		
		/* A, E, I, O, U (dieresis) */
		196, 203, 207, 214, 220,
		/* a, e, i, o, u (dieresis) */
		228, 235, 239, 246, 252,
		
		/* A, E, I, O, U (casita) */
		194, 202, 206, 212, 219,
		/* a, e, i, o, u (casita) */
		226, 234, 238, 244, 251,
		
		/* A, E, I, O, U (acentos invertido) */
		192, 200, 204, 210, 217,
		/* a, e, i, o, u (acento invertido) */
		224, 232, 236, 242, 249
	);
	
	$replace = array(
		/* A, E, I, O, U, N */
		65, 69, 73, 79, 85, 78,
		/* a, e, i, o, u, n */
		97, 101, 105, 111, 117, 110,
		
		/* A, E, I, O, U */
		65, 69, 73, 79, 85,
		/* a, e, i, o, u */
		97, 101, 105, 111, 117,
		
		/* A, E, I, O, U */
		65, 69, 73, 79, 85,
		/* a, e, i, o, u */
		97, 101, 105, 111, 117,
		
		/* A, E, I, O, U */
		65, 69, 73, 79, 85,
		/* a, e, i, o, u */
		97, 101, 105, 111, 117
	);
	
	/*
	for ($i=0; $i<count($search); $i++) {
		echo chr($search[$i]).' : '.chr($replace[$i])."<br>\n\n";
	}
	*/
	
	$cadena_a_ser_convertida = html_entity_decode($cadena_a_ser_convertida);
	
	$cadena_convertida = NULL;
	
	for ($i=0; $i<strlen($cadena_a_ser_convertida); $i++):
		//echo $cadena_a_ser_convertida[$i].' = '.ord($cadena_a_ser_convertida[$i]).' ['.chr(ord($cadena_a_ser_convertida[$i]))."]<br>\n";
		$chr = str_replace($search, $replace, ord($cadena_a_ser_convertida[$i]));
		$cadena_convertida .= chr($chr); 
	endfor;
	
	$cadena_convertida = trim($cadena_convertida);
	
	if (empty($cadena_convertida))
		$cadena_convertida = $cadena_a_ser_convertida;
	
	if (strtolower($mode) == 'spaces') {
		$cadena_convertida = preg_replace('/[^a-z0-9\%\_\-\ \-|]/i', '', $cadena_convertida);
	} else {
		$cadena_convertida = str_replace(' ', '_', $cadena_convertida);
		$cadena_convertida = preg_replace('/[^a-z0-9\%\_\-|]/i', '', $cadena_convertida);
	} // else
   
	//echo "antes = $cadena_a_ser_convertida<br>\ndespues = $cadena_convertida\n<br>";
	
	//PK_debug('Strip_special_chars ANTES', $cadena_a_ser_convertida);
	//PK_debug('Strip_special_chars DESPUES', $cadena_convertida);		

	return $cadena_convertida;
} // strip_special_chars()
/**
 * Redirecciona la pagina
 *
 * @param string $url
 * @param string $msg
 * @param string $js
 */
function go($url='', $msg='', $js=''){
	$output = '<script language="javascript">';
    
    if (!empty($msg))
		$output .= 'alert("'. $msg .'");';
    
	if (!empty($js)) {
		$output .= $js.';';
	} elseif (empty($url)) {
		if (!empty($_SERVER['HTTP_REFERER']))
			$output .= 'document.location = "'. $_SERVER['HTTP_REFERER'] .'";';
		else
			$output .= 'history.go(-1);';
    } elseif (fasel !== strpos($url, 'http://')) {
		//die('meta content="0; URL='.$url.'" http-equiv="refresh"');
		die('<meta content="0; URL='.$url.'" http-equiv="refresh" />');
	} else
    	$output .= 'document.location = "'. $url .'";';
   	
   	//die($url."\n".$output);
    $output .= '</script>';
	echo $output;
	
	exit;
} // go()

function alert($alert_message = '')
{
	if (empty($alert_message))
		return;
	
	echo '<script type="text/javascript">alert("'.$alert_message.'");</script>';
}
/**
 * Muestra la imagen
 *
 * @param string $img
 * @param int $width
 * @param int $height
 * @param string $mode
 * @param array $param
 */
function show_img($img, $width, $height, $mode='', $param=array()){
	global $config, $_GET;
	
//	echo '<b>img:</b> ',$img,'<hr>';
	
	if (empty($img))
		return;
	
	$file = PK_UPLOAD_DIR.str_replace('-','/', $img);
	$ext = substr(fexists($file, array('.jpg', '.png', '.gif'), false), -4);
	
//	echo '<b>ext:</b> ',$ext,'<hr>';
//	echo '<b>file:</b> ',$file,'<hr>';
	
	if (!in_array(strtolower($ext), array('.jpg', '.gif', '.png')))
		return;
		
	if ($config['friendly_url'] == 'enabled') {
		$link = SERVER_URL.'images/'.$img.'/'.$width.'/'.$height.((isset($param['alt']))?'/'.strip_special_chars($param['alt']):'').$ext;	
	} else {
		$link = SERVER_URL.'index.php?plugin=PK&controller=images&id='.$img.'&width='.$width.'&height='.$height.'&uri='.strip_special_chars($param['alt']);
	}
	
//	echo '<b>link:</b> ',$link,'<hr>';
		
	if ($mode == 'return')
		return $link;
	
	if ($mode != 'link') {
		$link = '<img src="'.$link.'"';
		
		if (is_array($param)) {
			foreach ($param as $index=>$value) {
				$link .= ' '.$index.'="'.strip_tags($value).'"';
			}
		}
		
		$link .= '>';
	}
	
//	echo '<b>HTML:</b> ';
	
	echo $link;
}
/**
 * Muestra la imagen
 *
 * @param string $img
 * @param int $width
 * @param int $height
 * @param string $mode
 * @param array $param
 */
function show_file($file, $return=false){
	global $config;
	
	if (empty($file))
		return;
	
	if ($config['friendly_url'] == 'enabled') {
		$link = SERVER_URL.'files/'.$file;	
	} else {
		$link = SERVER_URL.'index.php?plugin=PK&controller=files&id='.$file;
	}
		
	if ($return === true)
		return $link;
	
	echo $link;
}
/**
 * Verifica si existe un archivo (con las extensiones permitidas)
 *
 * @param string $file
 * @param array $ext
 * @return string
 */
function fexists($file, $ext = array('.jpg','.gif','.png','.bmp','.swf','.doc','.xls','.pdf', '.doc', '.zip', '.avi'), $def_img=true){
	if (!is_array($ext)) {
		if (empty($ext))
			$ext = array('.jpg','.gif','.png','.bmp','.swf','.doc','.xls','.pdf', '.doc', '.zip', '.avi');
		else
			$ext[0] = $ext;
	}
	
	$is_image = array('.jpg','.gif','.png','.bmp');
		
	for ($i=0; $i<count($ext); ++$i):
//		echo $file.strtolower($ext[$i]).'<br>';
		$ext_replace = strtr($ext[$i], array('.'=>''));
//		echo $file.' ['.$ext_replace.']<br>';
				
		if (@file_exists($file.strtolower($ext[$i])) || @fopen($file.strtolower($ext[$i]), 'r'))
			return $file.strtolower($ext[$i]);
		elseif (@file_exists($file.strtoupper($ext[$i])) || @file_exists($file.strtoupper($ext[$i]), 'r'))
			return $file.strtoupper($ext[$i]);
		elseif (@file_exists(strtr($file, array($ext_replace=>'')).strtolower($ext[$i])) || @fopen(strtr($file, array($ext_replace=>'')).strtolower($ext[$i]), 'r'))
			return strtr($file, array($ext_replace=>'')).strtolower($ext[$i]);
		elseif (@file_exists(strtr($file, array($ext_replace=>'')).strtoupper($ext[$i])) || @fopen(strtr($file, array($ext_replace=>'')).strtoupper($ext[$i]), 'r'))
			return strtr($file, array($ext_replace=>'')).strtoupper($ext[$i]);
		/*---------------------------------------------------------
		Buscamos la imagen por defecto
		---------------------------------------------------------*/
//		elseif(in_array($ext[$i], $is_image) && $def_img === true) {
//			$files = array(
//				dirname($file).DEF_IMG,
//				dirname(dirname(dirname($file))).DEF_IMG,
//			);
//			
//			foreach ($files as $file): 
//				$ext_replace = strtr($ext[$i], array('.'=>''));
////				echo $file.' ['.$ext_replace.']<br>';
//			
//				if (@file_exists($file.strtolower($ext[$i])) || @fopen($file.strtolower($ext[$i]), 'r'))
//					return $file.strtolower($ext[$i]);
//				elseif (@file_exists($file.strtoupper($ext[$i])) || @fopen($file.strtoupper($ext[$i]), 'r'))
//					return $file.strtoupper($ext[$i]);
//				elseif (@file_exists(strtr($file, array($ext_replace=>'')).strtolower($ext[$i])) || @fopen(strtr($file, array($ext_replace=>'')).strtolower($ext[$i]), 'r'))
//					return strtr($file, array($ext_replace=>'')).strtolower($ext[$i]);
//				elseif (@file_exists(strtr($file, array($ext_replace=>'')).strtoupper($ext[$i])) || @fopen(strtr($file, array($ext_replace=>'')).strtoupper($ext[$i]), 'r'))
//					return strtr($file, array($ext_replace=>'')).strtoupper($ext[$i]);
//			endforeach;
//		}
//		echo 'no existe<hr>';
	endfor;
	
	return false;
}

function paginador() {
	global $row_Template, $_GET;
//	echo '<pre>'.print_r($row_Template, true).'</pre>';
	if ($row_Template['pag']['pags'] < 2)
		return false;

	$url = '?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action='.$_GET['action'].'&id='.$_GET['id'].'&slug='.$_GET['slug'].'&p=page_%s&extra='.$_GET['extra'];
	?>
<br>
<ul class="paginador">
<?php if ($_GET['page'] > 1) { ?>
	<li><a href="<?php url(sprintf($url, 1)) ?>">|&lt;</a></li>
	<li><a href="<?php url(sprintf($url, ($_GET['page']-1))) ?>">&lt;&lt;</a></li>
<?php
}
if ($row_Template['pag']['ini'] > 1) {
?>
	<li><a href="javascript:;">...</a></li>
<?php
}

			for ($i=$row_Template['pag']['ini']; $i<=$row_Template['pag']['fin']; $i++) {
?>	<li<?php echo ($_GET['url']['p'] == $i)?' class="active"':'' ?>><a href="<?php url(sprintf($url, $i)) ?>"><?php echo $i ?></a></li>
<?php
}

if ($row_Template['pag']['fin'] < $row_Template['pag']['pags']) { ?>
	<li><a href="javascript:;">...</a></li>
<?php
}
if ($_GET['url']['p'] < $row_Template['pag']['pags']) {
?>
	<li><a href="<?php url(sprintf($url, ($_GET['page']+1))) ?>">&gt;&gt;</a></li>
	<li><a href="<?php url(sprintf($url, $row_Template['pag']['pags'])) ?>">&gt;|</a></li>
<?php } ?>
</ul>
	<?php
}

function registros() {
	global $row_Template, $_GET, $config;
	
	if ((int)$row_Template['pag']['reg']['total'] == 0)
		return;
	
	?><div class="registros"> <?
	echo sprintf(TXT_RESULT_MESSAGE, $row_Template['pag']['reg']['from'], $row_Template['pag']['reg']['to'], $row_Template['pag']['reg']['total']);
	
	if (!empty($_GET['url']['id']) && $config['search'] === true) {
	?> de <strong><?php echo str_replace('_', ' ', urldecode($_GET['url']['id'])) ?></strong><?php
	} elseif (!empty($_GET['search'])) {
	?> de <strong><?php echo str_replace('_', ' ', urldecode($_GET['search'])) ?></strong><?php
	} ?></div><?php
}

/*-------------------------------------------------------------
Funcion para rellenar los combobox (select)
-------------------------------------------------------------*/

function fill_select($data, $selected='', $return=false) {
	if (!is_array($data))
		return;
		
	foreach ($data as $value) {
		if (empty($value[0]))
			$value[0] = $value[1];
		
		if (empty($value[0]))
			continue;
			
		if (empty($value[1]))
			$value[1] = $value[0];
		
		$select = '';
			
		if (!empty($selected)) {
			if ($selected == $value[0])
				$select = ' selected="selected"';
		}
			
		$output .= '<option value="'.$value[0].'"'.$select.'>'.$value[1].'</option>';
	}
	
	if ($return == true)
		return $output;
	
	echo $output;
}

function fecha($formato, $timestamp='') {
	if (empty($timestamp))
		$timestamp = time();
	
	$fecha = date($formato, $timestamp);
	
	$tr = array(
		"Monday"=>"Lunes",
		"Tuesday"=>"Martes",
		"Wednesday"=>"Mi&eacute;rcoles",
		"Thursday"=>"Jueves",
		"Friday"=>"Viernes",
		"Saturday"=>"S&aacute;bado",
		"Sunday"=>"Domingo",
	
		"Mon"=>"Lunes",
		"Tue"=>"Martes",
		"Wed"=>"Mi&eacute;rcoles",
		"Thu"=>"Jueves",
		"Fri"=>"Viernes",
		"Sat"=>"S&aacute;bado",
		"Sun"=>"Domingo",
	
		"January"=>"Enero",
		"February"=>"Febrero",
		"March"=>"Marzo",
		"April"=>"Abril",
		"May"=>"Mayo",
		"June"=>"Junio",
		"July"=>"Julio",
		"August"=>"Agosto",
		"September"=>"Setiembre",
		"October"=>"Octubre",
		"November"=>"Noviembre",
		"December"=>"Diciembre",
	
		"Jan"=>"Enero",
		"Feb"=>"Febrero",
		"Mar"=>"Marzo",
		"Apr"=>"Abril",
		"May"=>"Mayo",
		"Jun"=>"Junio",
		"Jul"=>"Julio",
		"Aug"=>"Agosto",
		"Sep"=>"Setiembre",
		"Oct"=>"Octubre",
		"Nov"=>"Noviembre",
		"Dec"=>"Diciembre"
	);
	
	
	$fecha = strtr($fecha, $tr);
	
	return $fecha;
}

function is_mail($email){
    $mail_correcto = 0;
    //compruebo unas cosas primeras
    if ((strlen($email) >= 6) && (substr_count($email,"@") == 1) && (substr($email,0,1) != "@") && (substr($email,strlen($email)-1,1) != "@")){
       if ((!strstr($email,"'")) && (!strstr($email,"\"")) && (!strstr($email,"\\")) && (!strstr($email,"\$")) && (!strstr($email," "))) {
          //miro si tiene caracter .
          if (substr_count($email,".")>= 1){
             //obtengo la terminacion del dominio
             $term_dom = substr(strrchr ($email, '.'),1);
             //compruebo que la terminaci�n del dominio sea correcta
             if (strlen($term_dom)>1 && strlen($term_dom)<5 && (!strstr($term_dom,"@")) ){
                //compruebo que lo de antes del dominio sea correcto
                $antes_dom = substr($email,0,strlen($email) - strlen($term_dom) - 1);
                $caracter_ult = substr($antes_dom,strlen($antes_dom)-1,1);
                if ($caracter_ult != "@" && $caracter_ult != "."){
                   $mail_correcto = 1;
                }
             }
          }
       }
    }
    
    if ($mail_correcto)
       return 1;
    else
       return 0;
}
function app_get($folder = 'controller', $app = '') {
	switch ($app):
	case 'lang':
	case 'langs':
		$roots[0] = PK_ROOT_DIR.'PK/lang/';
		break;
	case 'plugin':
	case 'plugins':
		$roots[0] = PK_PLUGIN_DIR;
		$roots[1] = PK_GLOBAL_PLUGIN_DIR;
		break;
	default:
		$roots[0] = PK_APP_DIR.((substr($app, -1) != '/' && !empty($app))?'/':'');
		break;
	endswitch;
	
	if (false !== strpos($folder, '/')) {
       	$replace = split('/', $folder);
       	$replace = $replace[count($replace)-1]; 
    } else {
       	$replace = $folder;
    }
    
    PK_debug('app_get', "roots[0]: ".$roots[0]."\nroots[1]: ".$roots[1]);
    $files = array();
	
	foreach ($roots as $root):
		$root = str_replace('//', '/', $root);
		
		if (!is_dir($root.$folder) || substr($folder, 0, 1) == '.') {
			continue;
		}
		
		if ($gestor = opendir($root.$folder)) {
		    while (false !== ($archivo = readdir($gestor))) {
		        if (substr($archivo, 0, 1) == '.') {
		        	continue;
		        }
		        
		        $archivo = str_replace('_'.$folder, '', basename($archivo, '.php'));
				$files[count($files)] = str_replace('_'.$replace, '', basename($archivo, '.php'));
		    }
		    closedir($gestor);
		}
	endforeach;
	
	return $files;
}

function array_extract(&$arr, $k)
{
  unset($arr[$k]);
}

function request_uri()
{
	global $_SERVER;
	
	$protocol = substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/')).'://';
	$host = $_SERVER['HTTP_HOST'];
	$request_uri = $_SERVER['REQUEST_URI'];
	//$query_string = $_SERVER['QUERY_STRING'];
	
	return strtolower($protocol).$host.$request_uri;
}
/**
 * Internal use only, generally to translate the names of the fields of the DB
 * 
 * @param $text_to_translate
 * @return $translated_text
 */
function i18n($text_to_translate)
{
	global $_SESSION, $i18n, $i18n_def, $strtr;
	
	$translated_text = $text_to_translate;
	
	if ($_SESSION['lang'] == 'en')
		return $text_to_translate;
	
	if ((count($i18n) + count($i18n_def)) <> count($i18n)) {
		$c = count($i18n['en']);
		$strtr = array();
		
		for ($i=0; $i<$c; ++$i):
			$strtr[$i18n['en'][$i]] = $i18n[$_SESSION['lang']][$i];
		endfor;
		
		$c = count($i18n_def['en']);
		
		for ($i=0; $i<$c; ++$i):
			$strtr[$i18n_def['en'][$i]] = $i18n_def[$_SESSION['lang']][$i];
		endfor;
	}
	
//	$translated_text = str_replace($i18n['en'], $i18n[$_SESSION['lang']], $text_to_translate);
//	$translated_text = str_replace($i18n_def['en'], $i18n_def[$_SESSION['lang']], $translated_text);
	
	$translated_text = strtr($translated_text, $strtr);
	
//	$translated_text = strtr($translated_text, $i18n['en'], $i18n[$_SESSION['lang']]);
//	$translated_text = strtr($translated_text, $i18n_def['en'], $i18n_def[$_SESSION['lang']]);
	
//	echo $text_to_translate, ' - ', $translated_text ,'<pre>', print_r($strtr),'</pre><br />';
	
	return $translated_text;
}

function get_contents($url){
	// Se crea un manejador CURL
	$ch = curl_init();
	
	// Se establece la URL y algunas opciones
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	// Se obtiene la URL indicada
	$output = curl_exec($ch);
	
	// Se cierra el recurso CURL y se liberan los recursos del sistema
	curl_close($ch);
	
	return $output;
}

function bbcode ($entry) {
	$entry = eregi_replace("\n","<br>",$entry);
	$entry = eregi_replace("\[b\]([^\[]+)\[/b\]","<b>\\1</b>",$entry);
	$entry = eregi_replace("\[i\]([^\[]+)\[/i\]","<i>\\1</i>",$entry);
	$entry = eregi_replace("\[u\]([^\[]+)\[/u\]","<u>\\1</u>",$entry);
	$entry = eregi_replace("\[mail\]([^\[]+)\[/mail\]","<a href=\"mailto:\\1\">\\1</a>",$entry);
	$entry = preg_replace("#\[size=([1-2]?[0-9])\](.*?)\[/size\]#si", "<FONT SIZE=\\1>\\2</FONT>", $entry);
	$entry = eregi_replace("\[url\]([^\[]+)\[/url\]","<a href=\"\\1\" >\\1</a>",$entry);
	$entry = eregi_replace("\[url=\"([^\"]+)\"]([^\[]+)\[/url\]","<a href=\"\\1\" >\\2</a>",$entry);
	
	// Simple Link
	$entry = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "<a href=\"\\2\">\\2</a>", $entry);
	
	// [IMG]source.jpg[/IMG] 
	$entry = eregi_replace("\[img\]([^\[]+)\[/img\]","<img USEMAP=\"\" onload=\"javascript:if(this.width>577 ){this.width=577;}\" src=\"\\1\" border=\"0\">",$entry);
	
	// [mp3] 
	// new bbcode [STREAM]source.mp3[/STREAM]
	$entry = eregi_replace("\[stream\]([^\[]+)\[/stream\]","<object type=\"application/x-shockwave-flash\" data=\"http://www.1pixelout.net/wp-content/plugins/audio-player/player.swf\" width=\"290\" height=\"24\" id=\"audioplayer1\"> <param name=\"movie\" value=\"http://www.1pixelout.net/wp-content/plugins/audio-player/player.swf\"/><param name=\"FlashVars\" value=\"playerID=1&amp;autostart=no&amp;leftbg=0xcccccc&amp;&amp;lefticon=0x767676&amp;rightbg=0xB4B2B4&amp;rightbghover=0x999999&amp;righticon=0xFFFFFF&amp;righticonhover=0xFFFFFF&amp;text=0x666666&amp;slider=0x666666&amp;track=0xFFFFFF&amp;border=0x666666&amp;loader=0xE4E4E4&amp;soundFile=\\1\" /> <param name=\"quality\" value=\"high\"/><param name=wmode value=transparent><param name=\"menu\" value=\"false\"/></object>",$entry);
	
	// [FLASH WIDTH=x HEIGHT=y]source.swf[/FLASH]
	$entry = preg_replace("#\[flash width=([0-9]?[0-9]?[0-9]) height=([0-9]?[0-9]?[0-9])\](([a-z]+?)://([^, \n\r]+))\[\/flash\]#si","<OBJECT WIDTH=\\1 HEIGHT=\\2><PARAM NAME=movie VALUE=\\3><PARAM NAME=quality VALUE=high><PARAM NAME=wmode VALUE=transparent><EMBED src=\\3 quality=high scale=noborder wmode=transparent WIDTH=\\1 HEIGHT=\\2 TYPE=\"application/x-shockwave-flash\"></EMBED></OBJECT>", $entry);
	
	// [FLASH HEIGHT=y WIDTH=x]source.swf[/FLASH]
	$entry = preg_replace("#\[flash height=([0-9]?[0-9]?[0-9]) width=([0-9]?[0-9]?[0-9])\](([a-z]+?)://([^, \n\r]+))\[\/flash\]#si","<OBJECT WIDTH=\\1 HEIGHT=\\2><PARAM NAME=movie VALUE=\\3><PARAM NAME=quality VALUE=high><PARAM NAME=wmode VALUE=transparent><EMBED src=\\3 quality=high scale=noborder wmode=transparent WIDTH=\\1 HEIGHT=\\2 TYPE=\"application/x-shockwave-flash\"></EMBED></OBJECT>", $entry);
	
	// [FLASH]source.swf[/FLASH]
	$entry = preg_replace("#\[flash](([a-z]+?)://([^, \n\r]+))\[\/flash\]#si","<OBJECT WIDTH=512 HEIGHT=512><PARAM NAME=movie VALUE=\\1><PARAM NAME=quality VALUE=high><PARAM NAME=wmode VALUE=transparent><EMBED src=\\1 quality=high scale=noborder wmode=transparent WIDTH=512 HEIGHT=512 TYPE=\"application/x-shockwave-flash\"></EMBED></OBJECT>", $entry);
	

	// [youtube]link de youtube[/youtube]
	$entry = eregi_replace("\[youtube\]http://www.youtube.com/watch\?v=([^\[]+)\[/youtube\]",'<object width="512" height="340"><param name="movie" value="http://www.youtube.com/v/\\1&hl=es&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/\\1&hl=es&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="512" height="340"></embed></object>',$entry);
	
	return $entry;
}
?>
