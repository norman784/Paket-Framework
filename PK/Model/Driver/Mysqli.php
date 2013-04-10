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
	public $_instance;
	private $_insertId;
	/**
	 * Realiza la conexion con la base de datos
	 */
	public function connect() {
		$this->_instance = new mysqli($this->_dbhost, $this->_dbuser, $this->_dbpass, $this->_dbname) or PK_debug(__FUNCTION__, "No se ha podido conectar con la DB\n\n".mysqli_connect_error(), array('class'=>'error'));
		
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
	public function query($file, $line, $sql, $params = array()) {
//		echo (int)is_array($params),' ', count($params),'<hr>';
		if (is_array($params) && count($params) == 0) {
//			echo "<!-- query:\n\n", $sql, "\n\n-->\n";
//			echo $sql,"<hr>\n\n";
			$t = $this->_instance->query($sql);
			
//			echo (int)method_exists($t, 'fetch_array').'<hr>';
			
			if (method_exists($t, 'fetch_array')) {
				$q = array();
				
				$tmp = '';
				
				while ($row = $t->fetch_array()):
					foreach ($row as $i=>$v):
						if (is_numeric($i))
							$tmp = $v;
						else {
							$row[$i] = $tmp;
							$tmp = '';
							continue;
						}
						
						$row[$i] = $v;
					endforeach;
					
					$q[] = $row;
				endwhile;
				
//				$q = $this->stripslashes($q);
				
//				echo $sql,
//				     '<br >', print_r($q, true),
//				     '<hr>';
			} else {
				$q = $t;
			}
		} else {
			$q = $this->_instance->prepare($sql);
//			echo $sql.'<hr>';
			
			$eval = '';
			$comma = ', ';
			$eval_type = "'";
			
			foreach ($params as $i=>$param):
				$eval_type .= ((is_numeric($param))?'i':'s');
				$eval .= $comma.'$params['.$i.']';
				$comma = ', ';
			endforeach;
			
			if (!empty($eval)) {
				$eval_type .= "'";
				$eval = '$q->bind_param('.$eval_type.$eval.');';
//				echo $eval.'<hr>';
				
				eval($eval);
			}
			
			$q->execute();
			
			if (false !== strpos($sql, 'INSERT ')) {
				$this->_insertId =  $q->insert_id;
			}
		}
		
		return $q;
	}
	/**
	 * Retorna en un array los datos de la fila
	 *
	 * @param resource $resource
	 * @return array
	 */
	public function __fetch_array($resource, $mode=NULL) {
//	public function fetchAll($file, $line, $resource, $mode=NULL){
//		if (!in_array($mode, array(MYSQLI_BOTH, MYSQLI_ASSOC, MYSQLI_NUM))) {
//			$mode = MYSQL_ASSOC;
//		}
		
		if (is_array($resource))
			return $resource;
		
		if (!is_object($resource))
			return false;
		
		echo print_r($resource, true), '<br />', json_encode($resource), '<hr />';
		
		if (is_callable($resource, 'fetch_array'))
			return $resource->fetch_array($mode);
		else {
			return array(0=>$this->insertId());
		}
//		$array = $resource->fetch_all($mode);
	}
	/**
	 * Devuelve los datos de la tabla
	 *
	 * @return array
	 */
	public function get($mode = 'fields', $table = '') {
		$table = trim($table);
		
//		die('table: '.$table);

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
    	
    	if (is_object($q))
    		$q = $this->fetchAll(__FILE__, __LINE__, $q);
    	
    	return $q;
    }
	/**
	 * Devuelve el parametro respectivo para el bind_param
	 *
	 * @return string
	 */
	public function magic_param() {
		return '?';
	}
	/**
	 * Escapa la cadena
	 *
	 * @param string $cadena_a_ser_escapada
	 * @return string
	 */
	public function escape_string($cadena_a_ser_escapada) {
		$cadena_a_ser_escapada = trim($cadena_a_ser_escapada);
		
		if (empty($cadena_a_ser_escapada))
			return $cadena_a_ser_escapada;
		
			$cadena_a_ser_escapada = $this->_instance->escape_string($cadena_a_ser_escapada);
		
		return $cadena_a_ser_escapada;
	}
	
	public function insertId()
	{
		return $this->_insertId;
	}
}
?>
