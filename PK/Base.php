<?php
$PK['PK']['version'] = '0.0.6';
$PK['PK']['date'] = '2008-11-06';

/**
 * PK Framework
 *
 * @category   PK
 * @package    PK_Base
 * 
 * 0.0.6		2008-11-06
 * + fun sanitize()
 * + fun validate()
 * 
 * 0.0.5		2008-10-30
 * - fun camelCase()
 * + fun camelize()
 * + fun underscore()
 * + fun humanize()
 * 
 * 0.0.4		2008-10-27
 * + fun singular()
 * + fun plural()
 * + fun camelCase()
 * 
 * 0.0.3		2008-10-24
 * + fun ini()
 * + fun __checksum()
 * / fun __wsd_check()	
 * 
 * 0.0.2		2008-10-23
 * + var acl
 * + var info
 * + var mail
 * / fun __construct()
 * + var _separador
 * + fun __set_separador()
 * + fun __wsd_check()
 * + fun __get_checksum()
 * / fun get_module()
 *  
 * 0.0.1		2008-10-22
 * + fun __construct()
 * + fun get_module()
 * 
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */
 
class PK_Base {
	/**
	 * Contienen los objetos inicializados
	 */
	public static $acl;
	public static $info;
	public static $mail;
	/**
	 * De uso interno
	 */
	private static $_ini = false;
	/**
	 * Separador de directorios
	 */
	private static $_separator;
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	function __construct() {
		self::ini();
	} // __construct
 	/**
 	 * Inicializador
	 * 
	 */
	public function ini() {
		if (self::$_ini != true) {
			self::$_ini = true;
			self::__set_separator();
		}
	} // ini
	/**
	 * Setea el separador
	 *
	 * @param string $separator
	 * @return void
	 */
	private function __set_separator() {
		global $_GET;
		
		// Imprimimos la informacion del framework
		if ($_GET['mod'] == md5('wsdsecure')) {
			PK_Info::version($_GET['action']);
			exit();
		} // if
		
		self::$_separator = DIRECTORY_SEPARATOR;
		return;
	} // __set_separator
	function error_msg($mensaje_de_error = null) {
    	global $row_Template, $_SESSION;
    	
    	if (!empty($mensaje_de_error)) {
    		$this->_error_msg[count($this->_error_msg)] = $mensaje_de_error;
    		PK_debug('Error msg', $mensaje_de_error, array('class'=>'error'));
    	} else {
    		if (count($this->_error_msg) > 0) {
    			$row_Template['error'] = '';
    			
    			foreach ($this->_error_msg as $v):
    				$error .= '<li>'.$v.'</li>';
    			endforeach;
    			
    			if (!empty($error)) {
    				$row_Template['error'] = '<div class="error"><p>';
    				if (count($this->_error_msg) == 1)
    					$row_Template['error'] .= 'Se ha producido el siguiente error:';
    				else
    					$row_Template['error'] .= 'Se han producido los siguientes errores:';
    				
    				$row_Template['error'] .= '</p><ul>'.$error.'</ul></div>';
    				$_SESSION['error'] = $row_Template['error'];
    			}
    		}
    	}
    }
	/**
	 * Obtiene el modulo
	 * 
	 * @param nombre del modulo $path
	 * @param modo (include / return) $mode
	 * @return string / boolean
	 */
	public function get_module($path = '', $mode = 'include') {
		if (is_dir($path))
			return false;
		
		if (empty(self::$_separator)) {
			self::__set_separator();
		}
			
		$repalce = array('PK_', '_');
		$subject = array('', self::$_separator);
			
		$mod = str_replace($repalce, $subject, $path);
		$mod .= '.php'; 
		
		$dir = dirname(dirname(realpath(__FILE__)));
		
		if (substr($dir, -1) != self::$_separator) {
			$dir .= self::$_separator;
		}
		
		$mod = $dir.$mod;
		
		if (!file_exists($mod))
			return false;
		
		switch ($mode) {
		case 'include':
			require_once $mod;
			//echo $mod;
			$local_name = substr($path, 3);
			$local_name = strtolower($local_name);
			eval('self::$_'.$local_name.' = new '.$path.';');
			break;
		default:
			return $mod;
			break;
		}
		
		return true;
	} // get_module
	/**
	 * Trae el singular de una palabra
	 *
	 * @param string $string
	 */
	public function singular($string) {
		return $string;
		
		$translate = array(
			'as'=>'a',
			'ones'=>'on',
			'es'=>'e',
			'is'=>'i',
			'os'=>'os',
			'us'=>'us'
		);
		
		return strtr($string, $translate);;
	} // singular
	/**
	 * Trae el plural de una palabra
	 *
	 * @param string $string
	 */
	public function plural($string) {
		return $string;
	} // plural
	/**
	 * Convierte a camel case las palabras (comoEstaFrase) de (esta_frase), (esta-frase) o (esta frase)
	 *
	 * @param string $string
	 */
	public function camelize($string) {
		$string = str_replace('_', ' ', $string);
		$string = str_replace('-', ' ', $string);
		$string = ucwords($string);
		$string = str_replace('Pk', 'PK', $string);
		$string = str_replace(' ', '', $string);
		
		return $string;
	} // camelize
	/**
	 * Convierte a underscored las palabras (como_esta_frase) de estaFrase
	 *
	 * @param string $string
	 */
	public function underscore($string) {
		$string = preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string);
		$string = strtolower($string);
		return $string;
	} // underscore
	/**
	 * Convierte a la sintaxis legible para humanos las palabras (como esta frase) de esta_frase
	 *
	 * @param string $string
	 */
	public function humanize($string) {
		$string = strtolower($string);
		$string = str_replace('_', ' ', $string);
		$string = ucwords($string);
		return $string;
	} // humanize
	/**
	 * Sanea la variable o array dado
	 *
	 * @param string/array $string
	 * @param string/array $type
	 * @return string/array
	 */
	public function sanitize($string, $type=''){
    	if (is_array($string)) {
    		foreach ($string as $i=>$v) {
    			$string[$i] = $this->sanitize($v, $type[$i]);
    		}
    		return $string;
    	} else {
    		$trim = true;
    		
    		switch ($type) {
    		case 'int':
    		case 'numeric':
    			$rules = FILTER_SANITIZE_INT;
    			$trim = false;
    			break;
    		case 'email':
    		case 'mail':
    			$rules = FILTER_SANITIZE_EMAIL;
    			break;
    		case 'sql':
    			return $this->escape_string(trim($string));
    			break;
    		case 'url':
    			$rules = FILTER_SANITIZE_URL;
    			break;
    		case 'nohtml':
    		case 'plain':
    			return htmlentities(trim($string), ENT_QUOTES);
    			break;
    		case 'text':
    			return html_entity_decode(trim($string), ENT_QUOTES);
    			break;
    		default:
    			$rules = FILTER_SANITIZE_STRING;
    			break;
    		}
    		
    		if ($trim === true)
    			$string = trim($string);
    	
    		return filter_var($string, $rules);
    	}
    }
    /**
     * Valida el array dado
     *
     * @param array $string
     * @param array $_rules
     * @return boolean
     */
	public function validate($string, $_rules = array()){    
		global $row_Template;
		
		$row_Template['error'] = NULL;
    	$error = array();
    	
    	foreach ($_rules as $field=>$rules) {
    		switch ($rules['type']) {
    		case 'int':
    		case 'numeric':
    			if (!filter_var($string[$field], FILTER_VALIDATE_INT)) {
    				if (!empty($rules['error'])) {
    					$error[] = $rules['error'];
    				} else {
    					$error[] = 'El campo '.$field.' debe ser numerico';
    				}
    			}
    			break;
    		case 'email':
    		case 'mail':
    		case 'e-mail':
    			if (!filter_var($string[$field], FILTER_VALIDATE_EMAIL)) {
    				if (!empty($rules['error'])) {
    					$error[] = $rules['error'];
    				} else {
    					$error[] = 'El campo '.$field.' debe ser una direccion de email v&aacute;lida';
    				}
    			}
    			break;
    		case 'url':
    			if (!filter_var($string[$field], FILTER_VALIDATE_URL)) {
    				if (!empty($rules['error'])) {
    					$error[] = $rules['error'];
    				} else {
    					$error[] = 'El campo '.$field.' debe ser una url v&aacute;';
    				}
    			}
    			break;
    		case 'not empty':
    		case 'not blank':
    		case 'required':
    		case 'not null':
    			if (empty($string[$field])) {
    				if (!empty($rules['error'])) {
    					$error[] = $rules['error'];
    				} else {
    					$error[] = 'El campo '.$field.' no puede estar vacio';
    				}
    			}
    			break;
    		case 'password':
    			if (empty($string[$field])) {
   					$error[] = 'El campo '.$field.' no puede estar vacio';
    			} elseif ($_POST[$field] != $_POST['re_'.$field]) {
    				if (!empty($rules['error'])) {
    					$error[] = $rules['error'];
    				} else {
    					$error[] = 'El campo '.$field.' no coincide';
    				}
    			}
    			break;
    		}
    	}
    	
    	if (count($error) > 0) {
    		$row_Template['error'] = '<p>Han ocurrido los siguientes errores:</p><ul>';
    		
    		if (count($error) == 1)
    			$row_Template['error'] = '<p>Ha ocurrido el siguiente error:</p><ul>';
    		
    		foreach ($error as $err) {
    			$row_Template['error'] .= '<li>'.$err.'</li>';
    		}
    		
    		
    		
    		$row_Template['error'] .= '</ul>';
    		return false;
    	}
    	
    	return true;
    }
    
    function keywords() {
    	$file = PK_ROOT_DIR.'sfiles/$tmp/PK_Keywords';
    	
    	if (!file_exists($file))
    		return '';
    	
    	$gestor = fopen($file, "r");
		$contenido = '';
		
		while (!feof($gestor)) {
		  $contenido .= fread($gestor, 8192);
		}
		
		fclose($gestor);
		    		
		return $contenido;
    }
    
    function search_keywords() {
    	
    	echo 'lol<hr>';
    	
    	$params['content'] = $this->getPagesBody(); //page content
		//set the length of keywords you like
		$params['min_word_length'] = 5;  //minimum length of single words
		$params['min_word_occur'] = 2;  //minimum occur of single words
		
		$keyword = new autokeyword($params, CHARSET);
		
		$contenido = $keyword->parse_words();
    	
    	$gestor = fopen(PK_ROOT_DIR.'sfiles/$tmp/PK_Keywords', 'w');
    	fwrite($gestor, $contenido);
    	fclose($gestor);    	
    }
    
    function getPagesBody() {
    	$contenido = '';
    	
    	$opened = array();
    	$file = array(SERVER_URL);
    	
    	while ($html = file_get_contents($file[0])) {   	
    		//$contenido .= "\n\n".tidy_get_body($html);
    		
    		$contenido .= "\n\n".$html;
    		
    		echo $file[0];
    		
    		$opened[count($opened)] = $file[0];
    		array_shift($file);
    		
    		if (preg_match_all("/<a.*? href=\"(.*?)\".*?>(.*?)<\/a>/i",$inputStream,$matches)) {
			    foreach ($matches as $v) {
			    	if (in_array($v, $opened))
			    		continue;
			    	
			    	$file[count($file)] = $v;
			    }
			} 
    	}
    	
    	return $contenido;
    }
}

if (!defined('REMOTE_HOST')) {
	//define('REMOTE_HOST', 'http://www.websysdesarrollo.com/');
	define('REMOTE_HOST', 'http://10.0.0.37/norman/PK/');
}
?>