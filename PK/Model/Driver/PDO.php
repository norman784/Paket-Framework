<?php
class PK_Model_Driver_PDO extends PK_Model_Driver {	
	public function connect()
	{
		/* Connect to an ODBC database using driver invocation */
		$dsn = $this->_dbengine.':dbname='.$this->_dbname.';host='.$this->_dbhost;
		
//		echo $dsn, ' - ', $this->_dbuser, ' - ', $this->_dbpass;
		
		try {
			$this->_instance = new PDO( $dsn, $this->_dbuser, $this->_dbpass );
		} catch (Exception $e) {
			PK::debug($e->getFile(), $e->getLine(), 'Error', 'No se ha podido conectar con la base de datos');
			exit;
		}
	}
	
	public function query($file, $line, $sql)
	{
		$args = func_get_args();
		
		for ($i=0; $i<3; $i++) {
			array_shift($args);
		}
		
		PK_debug(__FUNCTION__, $sql."<br />".print_r($args, true), array('file'=>$file, 'line'=>$line));
		
		if (is_array($args)) {
			$result = $this->_instance->prepare($sql);
			$result->execute($args);
			$result->fetchAll();
		} else {
			$result = $this->_instance->query($sql);
		}
		
		return $result;
	}
	
	public function __fetch_array($resource)
	{
		return $resource;
	}
	
	public function getName()
	{
		return $this->_name;
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
}
?>