<?php
$PK['PK_Model_Driver']['version'] = '0.0.1';
$PK['PK_Model_Driver']['date'] = '2008-11-06'; 
/**
 * Base para los drivers
 *
 * @category   PK
 * @package    PK_Model_Driver
 * 
 * 0.0.1		2008-11-06
 * + var _dbengine
 * + var _dbhost
 * + var _dbname
 * + var _dbpass
 * + var _dbprefix
 * + var _dbport
 * + var _dbuser
 * + var _instance
 * + fun __construct
 * + fun __delete
 * + fun __insert
 * + fun __update
 * + fun autoquery
 * + fun connect
 * + fun query
 * + fun fetchAll
 * 
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */
//abstract class PK_Model_Driver {
class PK_Model_Driver extends PK_Base {
	/**
	 * Usuario de la base de datos
	 *
	 * @var string
	 */
	public $_dbuser = 'root';
	/**
	 * Password de la base de datos
	 *
	 * @var string
	 */
	public $_dbpass;
	/**
	 * Host de la base de datos
	 *
	 * @var string
	 */
	public $_dbhost = 'localhost';
	/**
	 * Nombre de la base de datos
	 *
	 * @var string
	 */
	public $_dbname = 'test';
	/**
	 * Motor de la base de datos
	 *
	 * @var string
	 */
	public $_dbengine = 'mysql';
	/**
	 * Puerto de la base de datos
	 *
	 * @var integer
	 */
	public $_dbport = 0;
	/**
	 * Prefijo de las tablas
	 *
	 * @var string
	 */
	public $_dbprefix = '';
	/**
	 * Instancia de la conexion de la base de datos
	 *
	 * @var resource
	 */
	public $_instance = '';
	/**
	 * Construye la clase
	 *
	 */
	function __construct() {
		global $config;
		
		$this->_dbuser = $config['dbuser'];
		$this->_dbpass = $config['dbpass'];
		$this->_dbhost = $config['dbhost'];
		$this->_dbname = $config['dbname'];
		$this->_dbengine = $config['dbengine'];
		$this->_dbprefix = $config['dbprefix'];
		
		if ((int)$config['dbport'] > 0)
			$this->_dbport = (int)$config['dbport'];
		
		$this->connect();
	}
	/**
	 * Realiza la conexion con la base de datos
	 */
	//abstract public function connect();
	/**
	 * Realiza la consulta
	 *
	 *  @return resource
	 */
	//abstract public function query();
	/**
	 * Realiza una consulta segura
	 *
	 * @param __FILE__ $file
	 * @param __LINE__ $line
	 * @param string $sql
	 * @return resource
	 */
	public function safeQuery($file, $line, $sql, $param = array()) {
		PK_debug(__FUNCTION__, "File: $file\nLine:$line\n\n$sql");
		
		$comma = ',';
		$eval = '';
		
		if (!is_array($param)) {
			if (!empty($param)) {
				$v = $param;
				$v = $this->escape_string($v);
				if (!is_numeric($v))
					$v = "'".$v."'";
				$eval = ','.$v;
			}
		} else {
			foreach ($param as $v) {
				$v = $this->escape_string($v);
				
				if (!is_numeric($v)) {
					$v = "\"'".$v."'\"";
				}
				
				$eval .= $comma.$v;
				$comma = ',';
			}
		}
		
		if (!empty($eval)) {
			$eval = '$q = sprintf("'.$sql.'"'.$eval.');';
			//echo '<br>'.$eval.'<br>';
			eval($eval);
		} else {
			$q = $sql;
		}
		
		$q = $this->query(__FILE__, __LINE__, $q);
		
		return $q;
	}
	/**
	 * Retorna en un array los datos de la fila
	 *
	 */
	//abstract function __fetch_array();
	/**
	 * Retorna en un array los datos de la consulta
	 *
	 */
	public function fetchAll($file, $line, $resource){
		PK_debug(__FUNCTION__, "File: $file\nLine:$line");
		
		if (is_array($resource)) {
			return $this->stripslashes($resource);
//			return $resource;
		}
		
		if (!is_object($resource))
			return false;
		
		$result = array();
		
		
		while($row = $this->__fetch_array($resource)) {
			$result[] = $row;
		} // while
		die('lol');
		return $this->stripslashes($result);
	} //fetchAll
	/**
	 * Genera y ejecuta un INSERT, UPDATE o DELETE
	 *
	 * @param __FILE__ $file
	 * @param __LINE__ $line
	 * @param array $data
	 * @param string $table
	 * @param string $mode
	 * @param string $index
	 * @param string $value
	 * @return resource
	 */
	public function autoquery($file, $line, $data, $table, $mode='insert', $index='', $value='') {
		PK_debug(__FUNCTION__, "File: $file\nLine:$line");
		
		switch($mode) {
		case 'delete':
			$sql = $this->__delete($table, $index, $value);
			break;
		case 'update':
			$sql = $this->__update($data, $table, $index, $value);
			break;
		default:
			$sql = $this->__insert($data, $table);
			break;
		} // switch
		
//		echo $sql['query'], '<pre>', print_r($sql['param']) , '</pre>';
//		die();
		
		return $this->query(__FILE__, __LINE__, $sql['query'], $sql['param']);
	} // autoquery
	/**
	 * Crea el SQL para el INSERT
	 *
	 * @return array()
	 */
	public function __insert($data, $table) {
		$fields = $this->get('fields', $table);
		$comma = '';
		$mode = 'reset';
		$field_data = array();
		
		foreach ($fields as $field):
			$safe_data = $this->sanitize($data[$field['Field']], $field['Type']);
			
			if ($field['Extra'] == 'auto_increment')
				continue;
			
			if ($field['Field'] == 'password' && empty($data[$field['Field']]))
				continue;
				
			$field_list .= $comma.$field['Field'];
			$data_list .= $comma.$this->magic_param($mode);
			$field_data[] = $safe_data;
			$mode = '';
			$comma = ', ';
		endforeach;
		
		$where = '';
		
		if (isset($index) && isset($value)) {
			$where = ' WHERE ';
			$where .= $index.'='.$this->magic_param($mode);
			$field_data[] = $value;
		} // if
		
		$sql['query'] = 'INSERT INTO '.$this->_dbprefix.$table.' ('.$field_list.') VALUES ('.$data_list.')';
		$sql['param'] = $field_data;
		
		return $sql;
	} // __insert
	/**
	 * Crea el SQL para el UPDATE
	 *
	 * @return array
	 */
	public function __update($data, $table, $index, $value) {
		$fields = $this->get('fields', $table);
		$comma = '';
		$mode = 'reset';
		$field_data = array();
		
		foreach ($fields as $field):
			$safe_data = $this->sanitize($data[$field['Field']], $field['Type']);
			
//			echo $field['Field'],': ',$data[$field['Field']],' ('.$safe_data.')<br>';
			
			if ($field['Extra'] == 'auto_increment')
				continue;
			
			if ($field['Field'] == 'password' && empty($data[$field['Field']]))
				continue;
				
			$field_list .= $comma.$field['Field'].'='.$this->magic_param($mode);
			$field_data[] = $safe_data;
			$mode = '';
			$comma = ', ';
		endforeach;
		
//		die();
		
		$where = '';
		
		if (isset($index) && isset($value)) {
			$where = ' WHERE ';
			$where .= $index.'='.$this->magic_param($mode);
			$field_data[] = $value;
		} // if
		
		$sql['query'] = 'UPDATE '.$this->_dbprefix.$table.' SET '.$field_list.$where;
		$sql['param'] = $field_data;
		
		return $sql;
	} // __update
	/**
	 * Crea el SQL para el DELETE
	 *
	 * @return array
	 */
	public function __delete($table, $index, $value) {
		if (!isset($index) || !isset($value))
			return false;
		
		$where = ' WHERE ';
		$where .= $index.'='.$this->magic_param('reset');
		
		$sql['query'] = 'DELETE FROM '.$this->_dbprefix.$table.$where;
		$sql['param'] = array($value); 
		
		return $sql;
	} // __detele
	/**
	 * Devuelve el parametro respectivo para el bind_param
	 *
	 * @return string
	 */
	//abstract function magic_param();
	/**
	 * Escapa la cadena
	 *
	 * @param string $cadena_a_ser_escapada
	 * @return string
	 */
	function escape_string($cadena_a_ser_escapada) {
		return $cadena_a_ser_escapada;
	}
	
	function insertId()
	{
		die('Its not implemented for this driver');
	}
	
	private function stripslashes($variable_to_strip_slashes)
	{
		if (is_array($variable_to_strip_slashes)) {
			foreach ($variable_to_strip_slashes as $i=>$v):
				$variable_without_slashes[$i] = $this->stripslashes($v);
			endforeach;
		} else {
			$variable_without_slashes = $variable_to_strip_slashes;
			
			while(false !== strpos($variable_without_slashes, '\\')):
				$variable_without_slashes = stripslashes($variable_without_slashes);
			endwhile;
		}
		
		return $variable_without_slashes;
	}
}
?>