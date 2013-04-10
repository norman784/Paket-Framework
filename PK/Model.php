<?php
$PK['PK_Model']['version'] = '0.0.1';
$PK['PK_Model']['date'] = '2008-10-22';
/**
 * Muestra la informacion hacerca de las versiones 
 * de los componentes del framework
 *
 * @category   PK
 * @package    PK_Model
 * 
 *  
 * 0.0.1		2008-10-22
 * 
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */ 
class PK_Model extends PK_Base {
	public $name;
	public $_query = array();
	private $_validation = array();
    public $_driver;
    public $_relations = array();
    public $_struct = array();
    static $_instance = '';
    
/*--------------------------------------------------------------
Ini
--------------------------------------------------------------*/

	function __construct() {
    	$this->ini();
    }
    
    function ini() {
    	global $config;
    	
//    	if (file_exists(PK_ROOT_DIR.'PK/db.'.$config['dbengine'].'.php')) {
//    		include_once PK_ROOT_DIR.'PK/db.'.$config['dbengine'].'.php';
//    	}
    	
//    	$this->_driver = new PK_Model_Driver_PDO();
    	
    	switch ($config['dbengine']) {
    		case 'mysql':
    			$this->_driver = new PK_Model_Driver_Mysqli();
//    			$this->_driver = new PK_Model_Driver_Mysql();
    			break;
    		case 'pg':
    		case 'postgres':
    			$this->_driver = new PK_Model_Driver_Postgres();
    			break;
    	}
    	
    	if (empty($this->name)) {
    		$this->name = get_class($this);
    		//$this->name = $this->singular($this->name);
    	}
    	
    	$this->name = strtolower($this->name);
    	
//    	echo 'ini('.$this->name.')<br>';
    	
    	$this->__createRelations();
    }

/*--------------------------------------------------------------
Autoquery
--------------------------------------------------------------*/

    public function autoquery($file, $line, $post, $mode, $index, $value){
    	$validation = $this->validate($post);
    	
    	if ($validation === false)
    		return;
    	
        $q = $this->_driver->autoquery($file, $line, $post, $this->name, $mode, $index, $value);
        
        if ($mode == 'insert') {
        	$q = $this->_driver->insertId();
        }
        
//        if (is_object($q)) {
//        	print_r($q);
//        	$q = $this->_driver->fetchAll(__FILE__, __LINE__, $q);
//        }
        
        if (is_array($q)) {
        	foreach ($q as $value)
            	return $value;
        }
        
        return $q;
    }

/*--------------------------------------------------------------
Select
--------------------------------------------------------------*/

    public function select(){
        if (empty($this->_query['fields'])) {
        	$this->_query['fields'] = '*';
        }
        
//        print_r($this->_query);
    	
        $sql = 'SELECT';
        /*--------------------------------------------------------------
		MsSQL
		--------------------------------------------------------------*/
    	if (!empty($this->_query['TOP'])) {
    		$sql .= ' TOP '.$this->_query['TOP'];
    	}
        
    	$sql .= ' '.$this->_query['fields'] ."\n FROM ". $this->_driver->_dbprefix.$this->name;
    	
    	$join = array('LEFT JOIN', 'INNER JOIN', 'OUTER JOIN', 'RIGHT JOIN', 'CROSS JOIN');
    	
    	foreach ($join as $key):
    		if (is_array($this->_query[$key])) {
	    		foreach ($this->_query[$key] as $v):
	    			if (empty($v)) {
	    				continue;
	    			}
	    			
	    			$sql .= "\n ".$key.' '.$v;
	    		endforeach;
    		}
    	endforeach;
    	
    	$where = ' WHERE ';
    	
    	if (is_array($this->_query['WHERE'])) {
	    	foreach ($this->_query['WHERE'] as $w_field=>$w_value):
	    		if (empty($w_field))
	    			continue;
	    		
	    		$w_value = $this->sanitize($w_value, 'sql');
	    		
	    		if (!is_numeric($w_value)) {
	    			if (substr($w_value, 0, 1) != "'" && substr($w_value, -1, 1) != "'" )
	    				$w_value = "'".$w_value."'";
	    		}
	    			
	    		$sql .= "\n".$where.$w_field."=".$w_value;
	    		$where = ' AND ';
	    	endforeach;
    	}

		if (!empty($this->_query['WHERE_PLAIN'])) {
			if (false === strpos($sql, 'WHERE')) {
				$sql .= ' WHERE ';
			} else {
				$sql .= ' AND ';
			}
			$sql .= $this->_query['WHERE_PLAIN'];
	    }
	    
    	if (!empty($this->_query['GROUP BY'])) {
    		$sql .= "\n GROUP BY ".$this->_query['GROUP BY'];
    	}
    	
    	if (!empty($this->_query['ORDER BY'])) {
    		$sql .= "\n ORDER BY ".$this->_query['ORDER BY'];
    	}
    	/*--------------------------------------------------------------
		MySQL
		--------------------------------------------------------------*/
    	if (!empty($this->_query['LIMIT'])) {
    		$sql .= "\n LIMIT ".$this->_query['LIMIT'];
    	}
    	
//    	echo "<!--\n\n",$sql,"\n\n-->";
    	
        $q = $this->_driver->query(__FILE__, __LINE__, $sql);
        PK_debug('select', $sql, '', __FILE__, __LINE__);
//        PK_debug('select', $q, '', __FILE__, __LINE__);
        
        $q = $this->_driver->fetchAll(__FILE__, __LINE__, $q);
//        PK_debug('select', $q, '', __FILE__, __LINE__);	
		
		if (!is_array($q))
			$q = array();
        
        return $q;
    }
    
/*--------------------------------------------------------------
Fields
--------------------------------------------------------------*/
    
    public function fields($sql = '') {
    	if (empty($sql))
    		return $this->_query['fields'];
    	
    	$this->_query['fields'] = $sql; 
    }

/*--------------------------------------------------------------
JOIN
--------------------------------------------------------------*/
    
    public function join($sql = '', $join = 'LEFT JOIN') {
    	$allowed = array('LEFT JOIN', 'INNER JOIN', 'OUTER JOIN', 'RIGHT JOIN', 'CROSS JOIN');
    	
    	if (!in_array($join, $allowed))
    		$join = $allowed[0];
    	
    	if (empty($sql))
    		return $this->_query[$join];
    	
    	if (!is_array($this->_query[$join]))
    		$this->_query[$join] = array();
    		
    	$this->_query[$join][] = $sql;
    }
    
/*--------------------------------------------------------------
WHERE
--------------------------------------------------------------*/
    
    public function where($field = '', $value='', $plain=false) {
    	if (empty($field))
    		return $this->_query['WHERE'];
    	
		if ($plain !== true) {
			$key = (false === strpos($field, '.')?$this->name.'.'.$field:$field);
			$this->_query['WHERE'][$key] = $value;
		} else {
			$this->_query['WHERE_PLAIN'] = $field;
	    }
    }
/*--------------------------------------------------------------
GROUP BY
--------------------------------------------------------------*/
    
    public function groupBy($sql = '') {
    	if (empty($sql))
    		return $this->_query['GROUP BY'];
    	
    	$this->_query['GROUP BY'] = $sql; 
    }
    
/*--------------------------------------------------------------
ORDER BY
--------------------------------------------------------------*/
    public function order($sql = '') {
    	$this->orderBy($sql);
    }    
    
    public function orderBy($sql = '') {
    	if (empty($sql))
    		return $this->_query['ORDER BY'];
    	
    		
//    	$this->_query['ORDER BY'] = (false === strpos($sql, '.')?$this->name.'.'.$sql:$sql); 
		$this->_query['ORDER BY'] = $sql;
    }
    
/*--------------------------------------------------------------
LIMIT
--------------------------------------------------------------*/
    
    public function limit($sql = '') {
    	$engine = array('mysql', 'mssql');
    	$key = 'LIMIT';
    	
//    	if (!in_array($this->_driver->_engine, $engine)) 
//    		$key = $engine[0];
    	
    	if (empty($sql))
    		return $this->_query[$key];
    		
    	$this->_query[$key] = $sql;
    }
    
/*--------------------------------------------------------------
LIMIT
--------------------------------------------------------------*/
    
    public function reset($mode = 'all') {
    	if (is_array($mode)) {
    		foreach ($mode as $v) {
    			$this->reset($v);
    		}
    		return;
    	}
    	
    	switch ($mode) {
    	case 'TOP':
    	case 'LIMIT':
    		$this->_query['TOP'] = NULL;
    		$this->_query['LIMIT'] = NULL;
    		break;
    	case 'fields':
    		$this->_query['fields'] = '*';
    		break;
    	case 'LEFT JOIN':
    	case 'INNER JOIN':
    	case 'OUTER JOIN':
    	case 'RIGHT JOIN':
    	case 'CROSS JOIN':
    	case 'join':
    		$join = array('LEFT JOIN', 'INNER JOIN', 'OUTER JOIN', 'RIGHT JOIN', 'CROSS JOIN');
    		foreach ($join as $key) {
    			if ($mode != 'JOIN' && $key != $mode)
    				continue;
    			
				$this->_query[$key] = NULL;
    		}
    		break;
    	case 'WHERE':
    		$this->_query['WHERE'] = NULL;
    		break;
    	case 'GROUP BY':
    		$this->_query['GROUP BY'] = NULL;
    		break;
    	case 'ORDER BY':
    		$this->_query['ORDER BY'] = NULL;
    		break;
    	default:
    		$this->_query = array();
    		break;
    	}
    	
    	$this->__createRelations();
    }

/*--------------------------------------------------------------
Create Relations
--------------------------------------------------------------*/

    private function __createRelations(){
    	$tables = $this->_driver->get('tables');
    	
//    	echo $this->name;
    	
    	$this->_struct = $this->_driver->get('fields', $this->name);
    	$fields = $this->_struct;
    	
    	$this->_relations = array();
    	
    	foreach ($tables as $i=>$table):
    		$table['Name'] = str_replace($this->_driver->_dbprefix, '', $table['Name']);
    		$id = '';
    		$text = '';
			
    		if (!is_array($fields))
    			continue;
    		
    		foreach ($fields as $field):
    			if ($table['Name'].'_id' == $field['Field'] && $field['Type'] == 'int(11)') {
    				$fk = $this->_driver->get('fields', $table['Name']);
    				
    				foreach ($fk as $v): 
    					if ($v['Key'] == 'PRI' && $v['Extra'] == 'auto_increment') {
    						$id = $v['Field'];
    					} elseif (strpos($v['Type'], 'varchar') == 0) {
    						$text = $v['Field'];
    						break;
    					}
    				endforeach;
    				
    				if (empty($id) || empty($text))
    					continue;
    				
    				$this->_relations[$field['Field']]['fk'] = $id; 
    				$this->_relations[$field['Field']]['text'] = $text;
    				$this->_relations[$field['Field']]['table'] = $tables[$i]['Name'];
    				
    				$field['Field'] = $this->sanitize($field['Field'], 'sql');
    				
    				if (!is_numeric($field['Field'])) {
    					$field['Field'] = $field['Field'];
    				}
    				
    				$alias = $tables[$i]['Name'];
    				
    				if ($tables[$i]['Name'] == $this->name) {
    					$alias = $tables[$i]['Name'].'_0';
    				}
    				
    				$this->join($tables[$i]['Name'].' '.$alias.' ON '.$alias.'.'.$id.'='.$this->name.'.'.$field['Field']);
    			}
    		endforeach;
    	endforeach;
    }

/*--------------------------------------------------------------
Insert
--------------------------------------------------------------*/

    public function insert($file, $line, $post){
    	$this->autoquery($file, $line, $post, 'insert', '', '');
    	$q = $this->_driver->insertId();
    	if (!$q)
    		die('<h1>Error al insertar registro</h1><p>No se pudo guardar el registro</p>');
    	return $q;
    }

/*--------------------------------------------------------------
Update
--------------------------------------------------------------*/

    public function update($file, $line, $post, $index, $value){
    	$this->autoquery($file, $line, $post, 'update', $index, $value);
    }

/*--------------------------------------------------------------
Delete
--------------------------------------------------------------*/

    public function delete($file, $line, $index, $value){
    	$this->autoquery($file, $line, array(), 'delete', $index, $value);
    }
    
/*--------------------------------------------------------------
Escape String
--------------------------------------------------------------*/

    public function query($file, $line, $sql){
    	return $this->_driver->query($file, $line, $sql);
    }    

/*--------------------------------------------------------------
Escape String
--------------------------------------------------------------*/

    public function escape_string($string){
    	$string = $this->_driver->escape_string($string);
//    	if (!get_magic_quotes_gpc())
//    		$string = addslashes($string);
    	
        return $string;
    }
    
/*--------------------------------------------------------------
Prefix
--------------------------------------------------------------*/

    public function prefix($mode='self'){
    	$prefix = $this->_driver->_prefix;
    	
    	if ($mode == 'self') {
    		$prefix .= $this->name;
    	}
    	
    	return $prefix;
    }

/*--------------------------------------------------------------
Validate
--------------------------------------------------------------*/

    public function validate($string = '', $_rules = ''){ 
    	global $POST;
    	
    	if (empty($string))
    		$string = $POST;
    	
    	if (!is_array($_rules)) {
    		$_rules = $this->_relations;
    	}
    }
    
/*--------------------------------------------------------------
First Field
--------------------------------------------------------------*/
    public function firstField($type='varchar') {
    	$fields = $this->_driver->get('fields', $this->name);
    	
    	foreach ($fields as $field) {
    		if (false === strpos($field['Type'], $type))
    			continue;
    			
    		return $field['Field'];
    	}
    	
    	return false;
    }

/*--------------------------------------------------------------
First Field
--------------------------------------------------------------*/
    public function pagForLimit($p = 0, $limit = 0) {
    	global $_GET;
    	
    	if ((int)$p < 1)
    		$p = $_GET['page'];
    	
    	if ((int)$limit < 1)
    		$limit = LIMIT;
    	
    	return (($limit * $p)-$limit).','.$limit;
    }
/*--------------------------------------------------------------
COUNT(*)
--------------------------------------------------------------*/
    public function count($where = '') {
    	if (empty($this->name))
    		return 0;
    	
    	if (!empty($where) && false === strpos($where, 'WHERE'))
    		$where = ' WHERE '.trim($where);
    		
    	$count = $this->_driver->query(__FILE__, __LINE__, trim('SELECT COUNT(*) FROM '.$this->name.' '.$where));
    	$count = $this->_driver->fetchAll(__FILE__, __LINE__, $count);
    	
    	return $count[0][0];
    }
}
?>
