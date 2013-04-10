<?php
$PK['PK_Model_Driver_Postgres']['version'] = '0.0.1';
$PK['PK_Model_Driver_Postgres']['date'] = '2008-11-06'; 
/**
 * Driver para PostgreSQL
 *
 * @category   PK
 * @package    PK_Model_Driver_Postgres
 * 
 * 0.0.1		2008-11-06
 * + var _magic_param
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
class PK_Model_Driver_Postgres extends PK_Model_Driver {
	/**
	 * Indica el numero para el magic_param
	 */
	public $_magic_param = 0;
	/**
	 * Puerto de la base de datos
	 *
	 * @var integer
	 */
	public $_dbport = 5432;
	/**
	 * Realiza la conexion con la base de datos
	 */
	public function connect() {
		$cnn_string = 'host='.$this->_dbhost.' port='.$this->_dbport.' user='.$this->_dbuser.' password='.$this->_dbpass.' dbname='.$this->_dbname;
		
		$this->_instance = pg_connect($cnn_string) or PK_debug(__FUNCTION__, "No se ha podido conectar con la DB\n\n".pg_errormessage(), array('class'=>'error'));
		
		if (!$this->_instance) {
			echo '<h1>Error en la aplicacion</h1><p>No se ha podido conectar con la base de datos</p>';
			PK_debug('', '', 'output');
			exit();
		}
		
		return $this->_instance;
	} // connect
	/**
	 * Realiza la consulta
	 *
	 * @return resource
	 */
	public function query($file, $line, $sql, $param = array()) {
		PK_debug(__FUNCTION__, "File: $file\nLine:$line\n\n$sql\nparam: ".print_r($param, true));
		
		if (is_array($param) && count($param) > 0) {
			$q = pg_query_params($this->_instance, $sql, $param);
		} else {
			$q = pg_query($this->_instance, $sql);
		}
		
		return $q;
	} // query
	/**
	 * Retorna en un array los datos de la fila
	 *
	 * @return array
	 */
	public function __fetch_array($resource) {
		return pg_fetch_array($resource);
	} // __fetch_array
	/**
	 * Devuelve el parametro respectivo para el bind_param
	 *
	 * @return string
	 */
	public function magic_param($mode='') {
		if ($mode == 'reset')
			$this->_magic_param = 0;
		
		$this->_magic_param++;
		
		return '$'.$this->_magic_param;
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
    		
    		$r = $this->query(__FILE__, __LINE__, 'SELECT * FROM '.$this->_dbprefix.$table);
    		
    		for ($i=0; $i<pg_num_fields($r); $i++):
    			$q[$i]['Field'] = pg_field_name($r, $i);
    			
    			if ($q[$i]['Field'] == 'id') {
    				$q[$i]['Extra'] = 'auto_increment';
    				$q[$i]['Key'] = 'PRI';
    			}
    			
    			$q[$i]['Name'] = pg_field_name($r, $i);
    			$q[$i]['Type'] = pg_field_type($r, $i);
    			//echo $q[$i]['Type'].' ('.pg_field_size($r, $i).')<br>';
    		endfor;
    		
    		break;
    	case 'tables':
    	case 'table':
    		if (empty($this->_dbname))
    			return array();
    		
    		$sql = "SELECT relname AS table FROM pg_class WHERE (relkind = 'r' OR relkind = 'v') AND relname NOT LIKE 'pg_%' AND relowner = 16410";
    		
    		$q = $this->query(__FILE__, __LINE__, $sql);
    		$q = $this->fetchAll(__FILE__, __LINE__, $q);
    		break;
    	default:
    		return array();
    	} // switch
    	
    	return $q;
    }
	/**
	 * Escapa la cadena
	 *
	 * @param string $cadena_a_ser_escapada
	 * @return string
	 */
	function escape_string($cadena_a_ser_escapada) {
		return pg_escape_string($cadena_a_ser_escapada);
	}
}
?>