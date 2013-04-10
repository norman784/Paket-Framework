<?php
/**
 * PK Framework
 *
 * @category   PK
 * @package    PK
 * 
 * Aqui el sistema llama a los modulos compatibles segun sea la
 * version del php que tengamos
 * 
 * Implementaciones
 * ----------------
 * 
 * - Quitamos la compatibilidad con PHP4
 * - Quitamos SEO
 * - Optimizamos:
 *   - PK.php
 *   - lib.php
 *   - PK5 *
 * - Mejoramos Model.php
 *   - Añadimos la variable cache (para los querys y datos)
 *   - Si no existe intenta crear Stored Proc (insert, update, delete) y Trigers (validacion)
 * - Excepciones
 * - Cambiar $_GET['url']['id'] por $_GET['url']['slug']
 * - Cambiar $_GET['url']['uid'] por $_GET['url']['extras']
 * try {
 * 
 * } catch (Exception $e) {
 *   $e->getCode();
 *   $e->getMessage();
 *   $e->getFile();
 *   $e->getLine();
 *   $e->getTrace();
 *   $e->getTraceAsString();
 *   __toString();
 * } 
 */

function __autoload($class_name) {
	global $_GET;
	
	$class = $class_name;
	
	if (false !== strpos($class, '.php')) {
		$class = substr($class, 0, -4);
	}
	
//	echo '<br>', $class,' - ',(int)class_exists($class);
	
	if (class_exists($class))
		return;
	
	if (false === strpos($class_name, '.php'))
		$class_name .= '.php';
	
	if (false !== strpos($class_name, 'PK_')) {
		if (file_exists(PK_ROOT_DIR.strtr($class_name, array('_'=>'/')))) {
			include_once PK_ROOT_DIR.strtr($class_name, array('_'=>'/'));
		}
	} elseif (false !== strpos($class_name, '_controller')) {
		if (!empty($_GET['plugin'])) {
			$dirs[] = PK_PLUGIN_DIR.$_GET['plugin'].'/controller/';
			$dirs[] = PK_GLOBAL_PLUGIN_DIR.$_GET['plugin'].'/controller/';
		} else {
			$dirs[] = PK_APP_DIR.'controller/';
		}
		
		foreach ($dirs as $dir):
			if (!file_exists($dir.$class_name)) 
				continue;
			
			include_once($dir.$class_name);	
			break;
		endforeach;
	} else {
		$dirs[] = PK_APP_DIR.'model/';
		
		if (!empty($_GET['plugin'])) {
			$dirs[] = PK_PLUGIN_DIR.$_GET['plugin'].'/model/';
			$dirs[] = PK_GLOBAL_PLUGIN_DIR.$_GET['plugin'].'/model/';
		}
		
		foreach ($dirs as $dir):
			if (!file_exists($dir.$class_name)) {
				if (!file_exists($dir.strtolower($class_name)))
					continue;
				else
					$class_name = strtolower($class_name);
			}

			include_once($dir.$class_name);	
			break;
		endforeach;
	}
}

/* Set all the global variables needed, if are not set */
class PK {
	private static $_def_mail = 'normanpaniagua@gmail.com';
	public static $errorHandling = '';
	
	public function init()
	{
		self::init_debug();
		self::init_path();
		self::init_mail();
		self::init_security();
		self::init_seo();
		self::init_site();
		self::init_url();
	}
	
	private function init_debug()
	{
//		PK_ErrorHandling::init();
	}
	
	private function init_mail()
	{
		defined('MAIL_SITEADMIN') or define('MAIL_SITEADMIN', self::$_def_mail);
		defined('MAIL_CONTACT') or define('MAIL_CONTACT', self::$_def_mail);
	}
	
	private function init_path()
	{
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
		
		define('PK_DEF_VIEW_DIR', PK_ROOT_DIR.'PK/view/');
		define('PK_DEF_LAYOUT_DIR', PK_DEF_VIEW_DIR.'layouts/');
		define('PK_SCAFFOLDING_DIR', PK_DEF_VIEW_DIR.'scaffolding/');
		
		defined('PK_ADMIN_FOLDER') or define('PK_ADMIN_FOLDER', 'pk-admin');
		
		defined('PK_PLUGIN_DIR') or define('PK_PLUGIN_DIR', PK_APP_DIR.'plugins/');
		defined('PK_GLOBAL_PLUGIN_DIR') or define('PK_GLOBAL_PLUGIN_DIR', PK_ROOT_DIR.'plugins/');
		defined('PK_UPLOAD_DIR') or define('PK_UPLOAD_DIR', PK_APP_DIR.'uploads/');
		
		define('ACTION_SUFIX', 'Action');
	}
	
	private function init_security()
	{
		global $config;
		
		defined('LOGIN_URL') or define('LOGIN_URL', '?plugin=pk-admin&controller=people&action=login');
		defined('LOGIN_SUCCESS_URL') or define('LOGIN_SUCCESS_URL', '?plugin=pk-admin');
		defined('SALT') or define('SALT', 'This is a salt test text');
		
		if (!isset($config['user_model']['file'])) 
			$config['user_model'] = array('file'=>'people', 'class'=>'People');
		if (!isset($config['role_model']['file'])) 
			$config['role_model'] = array('file'=>'permisons', 'class'=>'Permisons');
	}
	
	private function init_seo()
	{
		defined('KEYWORDS') or define('KEYWORDS', 'pk framework php norman paniagua websysdesarrollo wsd');
		defined('DESCRIPTION') or define('DESCRIPTION', 'PK Framework es un Framework ligero para php para proyectos ligeros');
	}
	
	private function init_site()
	{
		$url = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://'. $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/';
		
		if (substr($url, -2) == '//')
			$url = substr($url, 0, -1);
		
		defined('SERVER_URL') or define('SERVER_URL', $url);
		defined('SITE_TITLE') or define('SITE_TITLE', strtr(PK_APP_DIR, array(dirname(PK_APP_DIR)=>'')));
		defined('DEF_LANG') or define('DEF_LANG', 'en');
		defined('LIMIT') or define('LIMIT', 10);
	}
	
	private function init_url()
	{
		global $_GET, $controller;
		$vars = array('lang', 'plugin', 'controller','action', 'page', 'slug', 'id', 'extra');
		
		$langs = app_get('', 'langs');
		
		$plugins = app_get('', 'plugins');
		$plugins = array_reverse($plugins);
		$plugins[] = '';
		$plugins = array_reverse($plugins);
		
		$controllers = array();
		
		if (false === strpos($_GET['url'], '/')) {
			$_GET['url'] .= '/';
		}
		
		$urls = explode('/', $_GET['url']);
		$plugin = app_get('', 'plugins');
		
		foreach ($urls as $url):
//			echo '<br>word: '.$url;
			foreach ($vars as $i=>$var):
//				echo '<br> - var: '.$var.' ('.$i.')';
				switch ($var):
				case 'lang':
					if (in_array('controller', $vars)) {
						if (in_array($url, $langs)) {
							$_GET['lang'] = $url;
							
							self::setLang();
							
							unset($vars[$i]);
							break 2;
						}
						
						self::setLang();
					
						break;
					} else {
						unset($vars[$i]);
					}
					
					self::setLang();
				case 'plugin':
					if (in_array('controller', $vars)) {
						if (in_array($url, $plugins)) {
							$_GET['plugin'] = $url;
							unset($vars[$i]);
							break 2;
						}
						
						break;
					} else {
						unset($vars[$i]);
					}
				case 'controller':
					if (in_array('action', $vars)) {
						if (!empty($_GET['plugin'])) {
							$controllers = app_get($_GET['plugin'].'/controller', 'plugins');
						} else {
							$controllers = app_get('controller');
						}
						
						if (!in_array($url, $controllers)) {
							$controllers = app_get('pk/controller', 'plugins');
							
							if (in_array($url, $controllers)) {
								$_GET['plugin'] = 'pk';
							}
						}
						
						if (in_array($url, $controllers)) {
							$_GET['controller'] = $url;
							
							$controller = $url.'_controller';
							
							$controller = new $controller;
							
							if (is_object($controller)) {
								unset($vars[$i]);
								break 2;
							}
						}
						
						break;
					} else {
						unset($vars[$i]);
					}
				case 'action':
					if (is_object($controller)) {
						if (method_exists($controller, $url.ACTION_SUFIX)) {
							$_GET['action'] = $url;
							
							if ($vars[($i-1)] == 'plugin') {
								unset($vars[($i-1)]);
							}
							
							break 2;
						} else {
							$_GET['action'] == VIEW_ACTION;
						}
						
						break;
					} else {
						unset($vars[$i]);
					}
				case 'page':
					if (false !== strpos($url, 'pag_')) {
						$_GET['page'] = (int)strtr($url, array('pag_'=>''));
						unset($vars[$i]);
						break 2;
					} elseif (false !== strpos($url, 'page_')) {
						$_GET['page'] = (int)strtr($url, array('page_'=>''));
						unset($vars[$i]);
						break 2;
					}

					if ((int)$_GET['page'] < 1) {
						$_GET['page'] = 1;
					}
					
					break;
				case 'id':
					if ((int)$url > 0) {
						$_GET['id'] = $url;
						unset($vars[$i]);
						break 2;
					}
					
					break;
				case 'slug':
					if (empty($_GET['slug']) && (int)$url  == 0) {
						$_GET['slug'] = $url;
						unset($vars[$i]);
						break 2;
					}
					
					break;
				default:
					$_GET['extra'] .= $separator.$url;
					$separator = '_';
					continue;
				endswitch;
			endforeach;
		endforeach;
		
//		echo "<!--\n\n", print_r($_GET, true), "\n\n-->";
//		echo '<pre>', print_r($_GET, true), '</pre>';
//		exit;
		
		$users = new PK_Users();
		
		if (!is_object($controller) && empty($_GET['controller'])) {
			$_GET['controller'] = VIEW_CONTROLLER;
			$controller = $_GET['controller'].'_controller';
			$controller = new $controller;
		} else {
			$controller = $_GET['controller'].'_controller';
			$controller = new $controller;
		}
		
		$users->getPermisons();
		
		$_GET['page'] = (int)$_GET['page'];
		
		if ($_GET['page'] < 1)
			$_GET['page'] = 1;
		
		if (is_object($controller)) {

			if (method_exists($controller, 'init')) {
				$controller->init();
			}
			
			defined('DEF_LIMIT') or define('DEF_LIMIT', 20);
			defined('LIMIT') or define('LIMIT', DEF_LIMIT);
			
			if (empty($_GET['action']))
				$_GET['action'] = VIEW_ACTION;
			
			$action = $_GET['action'].ACTION_SUFIX;
			
			if (method_exists($controller, $action)) {
				PK_debug('Controller::Action', $action.'()');
				$controller->$action();
			}
		}
		
//		echo '<pre>', print_r($_GET, true), '</pre>';
//		exit;
	}
	
	public function debug($file, $line, $title, $message)
	{
		echo '<h1>', $title , '</h1><pre>', $message , '</pre>';
//		PK_ErrorHandling::debug($file, $line, $message);
	}
	
	public function setLang()
	{
		global $_SESSION, $_GET;
		
		$langs = app_get('', 'langs');
		
		if (!empty($_GET['lang']) && in_array($_GET['lang'], $langs)) {
			$_SESSION = $_GET['lang'];
		} elseif (!in_array($_SESSION['lang'], $langs)) {
			$_SESSION['lang'] = DEF_LANG;
		}
		
		$lang_folders = array(
			PK_APP_DIR.'lang/',
			PK_PLUGIN_DIR.'lang/',
			PK_GLOBAL_PLUGIN_DIR.'lang/',
			PK_ROOT_DIR.'PK/lang/'
		);
		
		foreach ($lang_folders as $folder):
			$file = $folder.$_SESSION['lang'].'/'.$_SESSION['lang'].'.php';
			
			if (file_exists($file)) {
				include_once $file;
			}
		endforeach;
	}
}

include_once PK_ROOT_DIR.'PK/lib.php';

PK::init();

//$url_struct = array(
//	'lang' => array(),
//	'plugins' => array(),
//	'controller' => array(),
//);
//$langs = array('es', 'en');
//$plugins = app_get('', 'plugins');

/*-------------------------------------------------------------
Definimos los parametros de la URL
-------------------------------------------------------------*/
//$controllers = app_get('controller');
//PK_debug('Controllers (app_get)', $controllers);
//
//$plugins = app_get('', 'plugins');
//PK_debug('Plugins (app_get)', $plugins);
//
//if (is_array($plugins)) {
//	foreach ($plugins as $plugin):
//		if ($plugin == PK_ADMIN_FOLDER)
//			continue;
//		$plugins_controllers[$plugin] = app_get($plugin.'/controller', 'plugins');
//	endforeach;
//} else {
//	$plugins_controllers = array();
//}
//
//PK_debug('Plugins Controllers (app_get)', $plugins_controllers);
//
//$langs = app_get('', 'lang');
//$langs = array('es');
//PK_debug('Langs (app_get)', $langs);
//
//if(!empty($_GET['url'])) {
//	$url = explode('/', $_GET['url']);
//
//	$_GET['url'] = array();
//	
//	$content_controller = false;
//	$image_controller = false;
//	$file_controller = false;
//	
//	foreach ($controllers as $controller):
//		if ($controller == 'content') {
//			$def_content_controller = $controller;
//			$def_plugin = '';
//			
//			$content_controller = true;
//		} elseif ($plugin_controller == 'images') {
//			$def_image_controller = $controller;
//			$def_image_plugin = '';
//			
//			$image_controller = true;
//		} elseif ($plugin_controller == 'files') {
//			$def_file_controller = $controller;
//			$def_file_plugin = '';
//			
//			$file_controller = true;
//		}
//		
//	endforeach;
//	
//	foreach ($plugins as $plugin):
//		if (!is_array($plugins_controllers[$plugin]))
//			continue;
//		
//		foreach ($plugins_controllers[$plugin] as $plugin_controller):
//			if ($plugin_controller == 'content' && $content_controller === false) {
//				$def_content_controller = $plugin_controller;
//				$def_content_plugin = $plugin;
//				
//				$content_controller = true;
//			} elseif ($plugin_controller == 'images' && $image_controller === false) {
//				$def_image_controller = $plugin_controller;
//				$def_image_plugin = $plugin;
//				
//				$image_controller = true;
//			} elseif ($plugin_controller == 'files' && $file_controller === false) {
//				$def_file_controller = $plugin_controller;
//				$def_file_plugin = $plugin;
//				
//				$file_controller = true;
//			}
//			
//		endforeach;
//		break;
//	endforeach;
//	
//	switch(count($url)):
//	case 1:
//		if (in_array($url[0], $controllers)) {
//			$_GET['url']['controller'] = $url[0];
//			break;
//		} elseif (in_array($url[0], $plugins)) {
//			$_GET['url']['plugin'] = $url[0];
//			break;
//		} elseif (is_numeric($url[0])) {
//			$_GET['url']['p'] = $url[0];
//			break;
//		} else {
//			foreach ($plugins as $plugin):
//				if (!is_array($plugins_controllers[$plugin]))
//					continue;
//				
//				if (!in_array($url[0], $plugins_controllers[$plugin]))
//					continue;
//					
//				$_GET['url']['plugin'] = $plugin;
//				$_GET['url']['controller'] = $url[0];
//				
//				break;
//			endforeach;
//		}
//		
//		$_GET['url'] = array();
//		$_GET['url']['controller'] = $def_content_controller;
//		$_GET['url']['id'] = $url[0];
//		
//		break;
//	case 2:
//		/*
//		if ($url[0] == 'lang') {
//			if (in_array($url[1], $langs))
//				$_SESSION['lang'] = $url[1];
//			go();
//		}
//		*/
//		
//		if (in_array($url[0], $controllers)) {
//			$_GET['url']['controller'] = $url[0];
//			
//			if (is_numeric($url[1])) {
//				$_GET['url']['p'] = $url[1];
//			} else {
//				$_GET['url']['action'] = $url[1];
//			}
//		} elseif (in_array($url[0], $plugins)) {
//			$_GET['url']['plugin'] = $url[0];
//			if (is_numeric($url[1])) {
//				$_GET['url']['p'] = $url[1];
//			} else {
//				$_GET['url']['controller'] = $url[1];
//			}
//		} else {
//			foreach ($plugins as $plugin):
//				if (!is_array($plugins_controllers[$plugin]))
//					continue;
//				
//				if (in_array($url[0], $plugins_controllers[$plugin])) {
//					$_GET['url']['plugin'] = $plugin;
//					$_GET['url']['controller'] = $url[0];
//					
//					if (is_numeric($url[1])) {
//						$_GET['url']['p'] = $url[1];
//					} else {
//						$_GET['url']['action'] = $url[1];
//					}
//					
//					break;
//				}
//			endforeach;
//		}
//		break;
//	case 3:
//		if (in_array($url[0], $controllers)) {
//			if (is_numeric($url[1])) {
//				$_GET['url']['controller'] = $url[0];
//				$_GET['url']['id'] = $url[1];
//				$_GET['url']['uid'] = $url[2];
//			} else {
//				$_GET['url']['controller'] = $url[0];
//				$_GET['url']['action'] = $url[1];
//				$_GET['url']['id'] = $url[2];
//			}
//		} elseif (in_array($url[0], $plugins)) {
//			$_GET['url']['plugin'] = $url[0];
//			$_GET['url']['controller'] = $url[1];
//			$_GET['url']['action'] = $url[2];
//		} else {
//			foreach ($plugins as $plugin):
//				if (!is_array($plugins_controllers[$plugin]))
//					continue;
//				
//				if (in_array($url[0], $plugins_controllers[$plugin])) {
//					$_GET['url']['plugin'] = $plugin;
//					$_GET['url']['controller'] = $url[0];
//					$_GET['url']['action'] = $url[1];
//					$_GET['url']['id'] = $url[2];
//					
//					break;
//				}
//			endforeach;
//		}
//		break;
//	case 4:
//		if (in_array($url[0], $controllers)) {
//			$_GET['url']['controller'] = $url[0];
//			$_GET['url']['action'] = $url[1];
//			$_GET['url']['id'] = $url[2];
//			$_GET['url']['uid'] = $url[3];
//		} elseif (in_array($url[0], $plugins)) {
//			$_GET['url']['plugin'] = $url[0];
//			$_GET['url']['controller'] = $url[1];
//			if (is_numeric($url[2]) && !is_numeric($url[3])) {
//				$_GET['url']['id'] = $url[2];
//				$_GET['url']['uid'] = $url[3];
//			} else {
//				$_GET['url']['action'] = $url[2];
//				$_GET['url']['id'] = $url[3];
//			}
//		} else {
//			foreach ($plugins as $plugin):
//				if (!is_array($plugins_controllers[$plugin]))
//					continue;
//				
//				if (in_array($url[0], $plugins_controllers[$plugin])) {
//					$_GET['url']['plugin'] = $plugin;
//					$_GET['url']['controller'] = $url[0];
//					$_GET['url']['action'] = $url[1];
//					$_GET['url']['id'] = $url[2];
//					$_GET['url']['uid'] = $url[3];
//					
//					break;
//				}
//			endforeach;
//		}
//		break;
//	case 5:
//		if ($url[0] == 'images') {
//			$_GET['url']['plugin'] = 'PK';
//			$_GET['url']['controller'] = $url[0];
//			$_GET['url']['action'] = 'index';
//			$_GET['url']['id'] = $url[1];
//			$_GET['url']['width'] = $url[2];
//			$_GET['url']['height'] = $url[3];
//			$_GET['url']['uid'] = $url[4];
//		} else {
//			$_GET['url']['plugin'] = $url[0];
//			$_GET['url']['controller'] = $url[1];
//			$_GET['url']['action'] = $url[2];
//			$_GET['url']['id'] = $url[3];
//			$_GET['url']['uid'] = $url[4];
//		}
//		break;
//	endswitch;
//	
//	if ($image_controller === true && $url[0] == $def_image_controller) {
//		$_GET['url'] = array();
//		//$_GET['url']['plugin'] = $def_image_plugin;
//		$_GET['url']['controller'] = $def_image_controller;
//		$_GET['url']['id'] = $url[1];
//		$_GET['url']['height'] = $url[2];
//		$_GET['url']['width'] = $url[3];
//	} elseif ($file_controller === true && $url[0] == $def_file_controller) {
//		$_GET['url']['id'] = $_GET['url']['action'];
//		$_GET['url']['action'] = '';
//	} elseif (empty($_GET['url']['plugin']) && empty($_GET['url']['controller']) && $content_controller === true) {
//		$_GET['url'] = array();
//		//$_GET['url']['plugin'] = $def_plugin;
//		$_GET['url']['controller'] = $def_content_controller;
//		$_GET['url']['id'] = $url[0];
//	}
//	
//	if (!isset($_GET['url']['action']))
//		$_GET['url']['action'] = VIEW_ACTION;
//}
//
//PK_debug('$_GET', $_GET);

//$row_Template['PK_Form'] = new PK_Form;

//get_extended();

$inc_files = get_included_files();

//echo '<pre>';
//print_r($inc_files);
//echo '</pre>';

sort($inc_files);
PK_debug('Included Files', $inc_files);
PK_debug('Included Files [UNSORTED]', get_included_files());

/*
echo '<pre>';
print_r($_GET);
//print_r($_SESSION);
echo '</pre>';
*/
?>