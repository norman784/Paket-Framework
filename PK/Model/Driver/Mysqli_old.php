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
class PK_Model_Driver_Mysqli extends PK_Model_Driver {
	private $_poo;
	private $_insertId;
	/**
	 * Realiza la conexion con la base de datos
	 */
	public function connect() {
		$this->_instance = mysqli_connect($this->_dbhost, $this->_dbuser, $this->_dbpass, $this->_dbname) or PK_debug(__FUNCTION__, "No se ha podido conectar con la DB\n\n".mysqli_connect_error(), array('class'=>'error'));
		$this->_poo = new mysqli($this->_dbhost, $this->_dbuser, $this->_dbpass, $this->_dbname) or PK_debug(__FUNCTION__, "No se ha podido conectar con la DB\n\n".mysqli_connect_error(), array('class'=>'error'));
		
		if (!$this->_instance) {
			echo '<h1>Error en la aplicacion</h1><p>No se ha podido conectar con la base de datos</p>';
			PK_debug('', '', 'output');
			exit();
		}
	}
	/**
	 * Realiza una consulta
	 *
	 * @param __FILE__ $file
	 * @param __LINE__ $line
	 * @param string $sql
	 * @return resource
	 */
	public function query($file, $line, $sql, $param = array()) {
		PK_debug(__FUNCTION__, "File: $file\nLine:$line\n\n$sql\n\n".print_r($param, true)/*, array('class'=>'error')*/);
		
		if (is_array($param) && count($param) > 0) {
			$sql = str_replace(array('%s', '%d', '%e', '%f', '%F', '%u', '%b', '%c', '%o', '%s', '%x', '%X'), '?', $sql);
			
			$mysqli = $this->_poo->prepare($sql);
			$comma = '';
			foreach ($param as $i=>$val) {
				if (is_numeric($val)) {
					$type .= 'i';
				} else {
					$type .= 's';
				}
				
				$values .= $comma.'$param['.$i.']';
				$comma = ',';
			}
			
//			echo 'mysqli_bind_param($mysqli, "'.$type.'", '.$values.');';
			eval('$mysqli->bind_param("'.$type.'", '.$values.');');
			
			$q = $mysqli->execute();
			$this->_insertId = $mysqli->insert_id;
		} else {
			$q = mysqli_query($this->_instance, $sql) or PK_debug('mysql_query', $sql.'<br>'.mysqli_error($this->_instance), array('class'=>'error'));
			$this->_insertId = mysqli_insert_id($this->_instance);
		}
		
//		echo $file.'<br>'.$line.'<br>'.$sql.'<hr>';
		
		return $q;
	}
	/**
	 * Retorna en un array los datos de la fila
	 *
	 * @param resource $resource
	 * @return array
	 */
	public function __fetch_array($resource, $mode=NULL) {
		if (!in_array($mode, array(MYSQLI_BOTH, MYSQLI_ASSOC, MYSQLI_NUM))) {
			$mode = MYSQL_ASSOC;
			$mode = MYSQL_BOTH;
		}
		
		if (is_object($resource)) {
			$array = $resource->fetch_array($mode);
		} else  {
			if (is_bool($resource))
				return array();
			
			$array = mysqli_fetch_array($resource, $mode);
		}
		
//		print_r($array);
		
		if (is_array($array)) {
			foreach ($array as $i=>$v):
				if (is_array($v)) {
					foreach ($v as $i2=>$v2):
						while (false !== strpos($v2, '\\'))
							$v[$i2] = stripcslashes($v2);
					endforeach;
				} else {
					while (false !== strpos($v, '\\'))
						$v = stripcslashes($v);
				}
				$array[$i] = $v;
			endforeach;
		}
//		echo '<hr><pre>',print_r($array, true), '</pre>';
		
		return $array;
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
			$cadena_a_ser_escapada = mysqli_real_escape_string($this->_instance, $cadena_a_ser_escapada);
		else
			$cadena_a_ser_escapada = mysqli_escape_string($this->_instance, $cadena_a_ser_escapada);
		
		return $cadena_a_ser_escapada;
	}
	
	public function insertId()
	{
		return $this->_insertId;
	}
}
?>
