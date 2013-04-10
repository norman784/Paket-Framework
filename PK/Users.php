<?php
$PK['PK_Info']['version'] = '0.0.1';
$PK['PK_Info']['date'] = '2008-11-24';

/**
 * Muestra la informacion hacerca de las versiones 
 * de los componentes del framework
 *
 * @category   PK
 * @package    PK_Users
 * 
 * 0.0.1		2008-11-24
 * 
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */

//die(sha1(SALT.'123'.SALT));

class PK_Users extends PK_Base {
	private $_model_name;
	private $model;
	
	function PK_Users() {
//		$this->init();
	}
	
	function __construct() {
		$this->init();
	}
	
	function init() {
		global $config;
		get('model', $config['user_model']['file']);
		
		if (class_exists($config['user_model']['class'])) {
			$this->model = new $config['user_model']['class'];
			$this->getPermisons();
		}
	}
	
	public function getPermisons($hash_login = false, $force_login = false) {
		global $_SESSION, $_POST, $_GET, $config, $row_Template;
		
		if (!is_object($this->model))
			return;
		
		$_SESSION['error'] = NULL;
		
		if ($force_login === true)
			$_POST['action'] = 'login';
			
		if (false !== strpos($_GET['url'], 'logout')){
		    
			$_SESSION['user'] = array();
			$_SESSION['permison'] = array();
			
			go(url(LOGIN_URL, 'return'));
		} elseif ($_POST['action'] != 'login' && $hash_login !== true) {
			return;
		}
		
		$this->model->reset();
		
		$query = false;
		$guest = true;
		
		if (!empty($_POST['submit'])) {
			if (!empty($_POST['username']) && !empty($_POST['password'])) {
				$this->model->where('email', $this->model->_driver->escape_string($_POST['username']));
				$this->model->where('password', $this->passwordCreate($_POST['password']));
				
				$query = true;
				$guest = false;
			}
			
			if (empty($_POST['username']))
				$this->error_msg('No puede dejar el e-mail en blanco');
			if (empty($_POST['password']))
				$this->error_msg('No puede dejar la contrase&ntilde;a en blanco');
		} elseif ($hash_login === true) {
			$this->model->where('people.hash', $_GET['url']['id']);
			$guest = false;
			$query = true;
		}
		
		if ((!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] == 0) && $guest === true) {
			$this->model->where('people.role_id', 4);
			$query = true;
		}
		
		$this->model->limit(1);
		$this->model->join('roles ON roles.id=people.role_id');
		$this->model->fields('people.id, people.name, people.email, roles.description AS rol, people.role_id');
		$usuario = $this->model->select();
		
		if (count($usuario) == 0) {
			$this->model->reset();
			$this->model->where('people.role_id', 4);
			$this->model->limit(1);
			$this->model->join('roles ON roles.id=people.role_id');
			$this->model->fields('people.id, people.name, people.email, roles.description AS rol, people.role_id');
			$usuario = $this->model->select();
			
			$query = true;
		}
		
		$usuario = $usuario[0];
		
		if ($query === false)
			return;
		
		$_SESSION['user'] = array();
		
//		PK_debug('', '', 'output');
//		die();
		
		if (count($usuario) == 0) {
			$this->error_msg('Usuario o contrase&ntilde;a incorrectos');
			$this->error_msg();
			return;
		}
		
		foreach ($usuario as $k=>$v):
			if (is_numeric($k))
				continue;
			
			$_SESSION['user'][$k] = $v;
		endforeach;
		
		get('model', $config['role_model']['file']);
		
		if (!class_exists($config['role_model']['class'])) {
			$this->error_msg();
			return;
		}
		
		eval('$model = new '.$config['role_model']['class'].'();');
		
		$model->reset();
		
		$model->fields('modules.description AS module, permisons_actions.value AS action');
		$model->join('modules ON modules.id=permisons.module_id');
		$model->join('permisons_actions ON permisons_actions.id=permisons.permison_action_id');
		$model->where('permisons.role_id', (int)$usuario['role_id']);
		
		$permisos = $model->select();
		
		$_SESSION['permison'] = array();
		
		foreach ($permisos as $row):
			$row['module'] = strtolower($row['module']);
			$row['module'] = strip_special_chars($row['module'], 'spaces');
			$row['module'] = str_replace(' ', '_', $row['module']);
			
			$row['action'] = strtolower($row['action']);
			$row['action'] = strip_special_chars($row['action'], 'spaces');
			$row['action'] = str_replace(' ', '_', $row['action']);
			
			$_SESSION['permison'][$row['module']][count($_SESSION['permison'][$row['module']])] = $row['action'];
		endforeach;
		
		$this->error_msg();
		
		if (empty($row_Template['error'])) {
			go(url('?plugin='.$_GET['plugin'], 'return'));
			exit;
		}
	}
	
	public function isLoged() {
		global $_SESSION;
		
		if ((int)$_SESSION['user']['id'] > 0)
			return true;
		
		return false;
	}
	
	public function name() {
		global $_SESSION;
		
		return $_SESSION['user']['name'];
	}
	
	public function username() {
		global $_SESSION;
		
		return $_SESSION['user']['name'];
	}
	
	public function rol() {
		global $_SESSION;
		
		return $_SESSION['user']['rol'];
	}
	
	public function role() {
		global $_SESSION;
		
		return $_SESSION['user']['rol'];
	}
	
	public function role_id() {
		global $_SESSION;
		
		return $_SESSION['user']['role_id'];
	}
	
	public function id() {
		global $_SESSION;
		
		return (int)$_SESSION['user']['id'];
	}
	
	public function email() {
		global $_SESSION;
		
		return $_SESSION['user']['email'];
	}
	
	public function passwordCreate($password)
	{
		if (strlen($password) > 20)
			return $password;
		return sha1(SALT.$password.SALT);
	}
}
?>