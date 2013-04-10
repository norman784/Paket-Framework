<?php
$PK['PK_Acl']['version'] = '0.0.2';
$PK['PK_Acl']['date'] = '2008-12-29'; 
/**
 * Lista de control de acceso
 *
 * @category   PK
 * @package    PK_Acl
 * 
 * 0.0.2		2008-11-29
 * + fun isAllowed()
 * 
 * 0.0.1		2008-10-24
 * + fun addRole()
 * + fun allow()
 * 
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */
/*
PK_ACL
deny('role')
access('role')
removeDeny('someRole', module, action)
*/
class PK_Acl {
	/**
	 * De uso interno
	 *
	 * @var array
	 */
	public static $_role = array();
	
	function __construct() {
		self::init();
	}
	
	public function init()
	{
		global $_SESSION;
		
		self::$_role[$_SESSION['user']['name']] = $_SESSION['permison'];
	}
	/**
	 * Agrega roles
	 *
	 * @param string / array $resource
	 */
	public function addRole($resource) {
		if (!is_array($resource)) {
			$resource[0] = $resource;
		}
		
		$exists = false;
		$add = '';
		
		foreach ($resource as $add_role) {
			$exists = false;
			$add = $add_role;
			foreach (self::$_role as $cur_role=>$data) {
				if ($add_role == $cur_role) {
					$exists = true;
					break 2;
				}
			}
			
			if ($exists == false && !empty($add)) {
				self::$_role[$add] = array();
			}
		}
	}
	
	public function allow($resource, $module = '', $action = '') {
		if (!is_array($resource)) {
			$resource = array($resource, $module, $action);
		}
		
		//print_r($resource);
		
		if (!array_key_exists($resource[0], self::$_role)) {
			return false;
		}
		
		if (empty($resource[1])) {
			return;
		}
		
		if (!array_key_exists($resource[1], self::$_role[$resource[0]])) {
			self::$_role[$resource[0]][$resource[1]] = array();
		}
		
		if (empty($resource[2])) {
			return;
		}
		
		foreach (self::$_role[$resource[0]][$resource[1]] as $v) {
			$exists = false;
			
			if ($v == $resource[2]) {
				$exists = true;
				break;
			}
		}
		
		if ($exists == false) {
			self::$_role[$resource[0]][$resource[1]][] = $resource[2];
		}
		
		return true;
	}
	/**
	 * Verifica que el usuario tenga los permisos necesarios
	 *
	 * @param string $someUser
	 * @param array $someResource
	 * @return boolean
	 */
	function isAllowed($someUser = '', $someResource = '') {
		global $_GET, $_SESSION, $row_Template;
		
		if (!is_array(self::$_role)) {
			self::init();
		}
		
		if (empty($someUser)) {
			$someUser = $_SESSION['user']['name'];
		}
		
		if (empty($someResource[0])) {
			$someResource = array($_GET['url']['controller'], $_GET['url']['action']);
		}
		
//		echo '<pre>'.$someUser.'<br>'.print_r($someResource, true).'<br><!-- '.print_r(self::$_role, true).' --></pre>';
		
		if (is_array($someResource) && is_array(self::$_role[$someUser][$someResource[0]])) {
//			echo '<pre>'.print_r(self::$_role[$someUser][$someResource[0]], true).'</pre>';
			
//			echo '<hr>'.in_array($someResource[1], self::$_role[$someUser][$someResource[0]]).'<hr>';
			
			if (in_array($someResource[1], self::$_role[$someUser][$someResource[0]]) != false) {
				$row_Template['access'][$someResource[0]][$someResource[1]] = true;
				return true;
			}
		}
		
		$row_Template['access'] = false;
		return false;
	}
}
?>