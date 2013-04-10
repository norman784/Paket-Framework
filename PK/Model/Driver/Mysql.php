<?php
$PK['PK_Model_Driver_Mysql']['version'] = '0.0.2';
$PK['PK_Model_Driver_Mysql']['date'] = '2008-11-07'; 
/**
 * Driver para MySQL
 *
 * @category   PK
 * @package    PK_Model_Driver_Mysql
 * 
 * 0.0.2		2008-11-07
 * / fun __fetch_array
 * / fun connect
 * + fun get
 * / fun query
 * 
 * 0.0.1		2008-11-06
 * + fun __fetch_array
 * + fun connect
 * + fun magic_param
 * + fun query
 * 
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */
class PK_Model_Driver_Mysql extends PK_Model_Driver {
	private $_poo;
	/**
	 * Realiza la conexion con la base de datos
	 */
	public function connect() {
		$this->_instance = mysql_connect($this->_dbhost, $this->_dbuser, $this->_dbpass) or die("<pre>No se ha podido conectar con la DB\n\n".mysqli_connect_error()."</pre>");
		mysql_select_db($this->_dbname, $this->_instance);
	}
	/**
	 * Realiza una consulta
	 *
	 * @param __FILE__ $file
	 * @param __LINE__ $line
	 * @param string $sql
	 * @return resource
	 */
	public function query($file, $line, $sql) {
		PK_debug(__FUNCTION__, "File: $file\nLine:$line\n\n$sql");
		
		$q = mysql_query($sql, $this->_instance);
		
		return $q;
	}
	/**
	 * Retorna en un array los datos de la fila
	 *
	 * @param resource $resource
	 * @return array
	 */
	public function __fetch_array($resource) {
		return mysql_fetch_array($resource);
	}
	/**
	 * Devuelve los datos de la tabla
	 *
	 * @return array
	 */
	public function get($mode = 'fields',$table = '') {
		$table = trim($table);
		
		switch ($mode) {
    	case 'fields':
    		if (empty($table))
    			return array();
    		
    		$sql = 'SHOW FIELDS FROM `'.$this->_dbprefix.$table.'`';
    		break;
    	case 'tables':
    	case 'table':
    		if (empty($this->_dbname))
    			return array();
    		
    		$sql = 'SHOW TABLE STATUS FROM `'.$this->_dbname.'`';
    		break;
    	default:
    		return array();
    	} // switch
    	
    	$q = $this->query(__FILE__, __LINE__, $sql);
    	$q = $this->fetchAll(__FILE__, __LINE__, $q);
    	
    	return $q;
    }
	/**
	 * Devuelve el parametro respectivo para el bind_param
	 *
	 * @return string
	 */
	public function magic_param() {
		return '%s';
	}
	/**
	 * Escapa la cadena
	 *
	 * @param string $cadena_a_ser_escapada
	 * @return string
	 */
	public function escape_string($cadena_a_ser_escapada) {
		if (function_exists('mysqli_real_escape_string'))
			$cadena_a_ser_escapada = mysql_real_escape_string($this->_instance, $cadena_a_ser_escapada);
		else
			$cadena_a_ser_escapada = mysql_escape_string($this->_instance, $cadena_a_ser_escapada);
		
		return $cadena_a_ser_escapada;
	}
}
?>