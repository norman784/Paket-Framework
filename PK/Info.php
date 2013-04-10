<?php
$PK['PK_Info']['version'] = '0.0.3';
$PK['PK_Info']['date'] = '2008-10-24';

/**
 * Muestra la informacion hacerca de las versiones 
 * de los componentes del framework
 *
 * @category   PK
 * @package    PK_Info
 * 
 * 0.0.3		2008-10-24
 * * cambiado dirname(__FILE__) por dirname(realpath(__FILE__))
 * 
 * 0.0.2		2008-10-23
 * + var _PK
 * + var _separator
 * / fun __construct()
 * + fun __set_separator
 * + fun __get_version()
 * + fun version()
 *  
 * 0.0.1		2008-10-22
 * + fun __construct()
 * 
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */ 
class PK_Info {
	/**
	 * Datos de la version
	 */
	private static $_PK;
	/**
	 * Separador de directorios
	 */
	private static $_separator;
	/**
	 * Contructor
	 * @return void
	 */
	function __construct() {
		self::version();
	}
	/**
	 * Setea el separador
	 *
	 * @param string $separator
	 * @return void
	 */
	private function __set_separator($separator = '') {
		global $_GET;
		
		if (in_array($separator, array('/', '\\'))) {
			self::$_separator = $separator;
			return;
		}
		
		if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'win')) {
			self::$_separator = '\\';
		} else {
			self::$_separator = '/';
		}
	}
	/**
	 * Obtiene las clases del Framework
	 */
	private function __get_version(){
		global $PK;
		
		self::__set_separator();
		
		self::__opendir();
		
		self::$_PK = $PK;
	}
	/**
	 * Abre la carpeta e incluye los archivos
	 *
	 * @param string $dir
	 */
	private function __opendir($dir = '') {
		global $PK;
		
		if (empty($dir)) {
			$dir = dirname(realpath(__FILE__));
		}
		
		$handler = opendir($dir);
		
		$dont_open = array('PK_checksum.php', 'PK_client_version.php');
		
		while (false !== ($file = readdir($handler))) {
			if ($file == '.' || $file == '..' || empty($file) || in_array($file, $dont_open))
				continue;
			
			if (is_dir($file)) {
				self::__opendir($file);
			} else {
				$type = 'file';
				
				if (is_dir(is_dir($file))) {
					$type = 'dir';
				}
				
				PK_debug('Get Version ['.$type.']',$dir.self::$_separator.$file);
				
				include_once $dir.self::$_separator.$file;
			}
		}
		
		closedir($handler);
	}
	/**
	 * Retorna las versiones en formato JSON o var_dump
	 */
	public function version($mode = '') {
		self::__get_version();
		
		$version = self::$_PK;
		
		switch (strtolower($mode)) {
		case 'json':
			die(json_encode($version));
			break;
		default:
			//var_dump($version);
			print_r($version);
			break;
		}
	}
}
?>