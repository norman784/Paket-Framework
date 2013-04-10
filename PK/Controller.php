<?php
$PK['PK']['version'] = '0.0.3';
$PK['PK']['date'] = '2008-11-12';

/**
 * PK Framework
 *
 * @category   PK
 * @package    PK_Controller
 *
 * 0.0.3		2008-11-12
 * + fun setName()
 * + fun enableScaffolding from isScaffolded
 * + fun disableScaffolding from noScaffolded
 * / fun isScaffolded
 * 
 * 0.0.2		2008-11-11
 * / extends PK_Base
 * + fun detalles()
 * + fun editar()
 * + fun nuevo()
 * + fun isScaffolded()
 * + fun noScaffolded()
 *  
 * 0.0.1		2008-11-05
 * + var $_scaffonding
 * + var $_name
 * + var $model
 * + fun __construct()
 * + fun ini()
 * 
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */
class PK_Controller extends PK_Base {
	/**
	 * Indica si estan o no disponible el scaffolding
	 *
	 * @var boolean
	 */
	public $_scaffolding = false;
	/**
	 * Indica si estan o no disponible el scaffolding
	 *
	 * @var boolean
	 */
	public $_scaffoldingLib = '';
	/**
	 * Nombre de la clase
	 *
	 * @var string
	 */
	public $_name;
	/**
	 * Instancia del modelo
	 *
	 * @var var
	 */
	public $model;
	/**
	 * Configuracion del formulario
	 *
	 * @var var
	 */
	public $form = array();
	/**
	 * Configuracion del formulario
	 *
	 * @var var
	 */
	public $filter = array();
	/**
	 * Upload Folder
	 *
	 */
	public $_uploadFolder = '';
	/**
	 * Owner
	 *
	 */
	public $_owner = array();
	/**
	 * View class
	 *
	 */
	public $view = '';
	/**
	 * file upload setings
	 *
	 */
	public $_fileUpload = true;
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		// Busca el model relacionado con la clase
		$this->_name = $this->singular(__CLASS__);
	} // __construct
	/**
	 * Inizializa la  
	 */
	public function init() {
		global $row_Template, $_GET;
		
		$this->view = new PK_View();
				
		if (empty($this->_name))
			$this->setName();
		
		$this->_name = str_replace('_controller', '', $this->_name);
		$this->_name = strtolower($this->_name);
		
		$row_Template['titulo'] = $this->_name;
		
		get('model', $this->_name);

		if (class_exists($this->_name)) {
			$this->model = new $this->_name;
			$this->owner();
		}
		
		if (!is_array($this->form['skip_fields']))
			$this->form['skip_fields'] = array();
			
		if (!is_array($this->form['def_values']))
			$this->form['def_values'] = array();
			
		if (!is_array($this->form['def_types']))
			$this->form['def_types'] = array();
	}
	/**
	 * Trae los datos para mostrar en la lista
	 *
	 * @return boolean
	 */
	function indexAction(){
    	global $row_Template, $_GET, $_SESSION;
    	
//    	if ((int)$_GET['id'] > 0) {
//    		$_GET['page'] = (int)$_GET['id'];
//    	}
        
    	if (!$this->isScaffolded()) {
    		return false;
    	}
    	
        /*
    	echo '<hr>lol<hr>';
    	print_r($this->model->_struct);
    		
    	die();
    	*/
        
        /*--------------------------------------------------------------
        List values
        --------------------------------------------------------------*/
        $this->model->reset();
        $this->model->fields($this->model->name.'.'.$this->model->firstField('int').', '.$this->model->name.'.'.$this->model->firstField());
        $this->model->limit($this->model->pagForLimit());
        
        if (is_array($this->_filter)) {
        	foreach ($this->_filter as $mode=>$v):
        		switch (strtolower($mode)):
        		case 'where':
        			foreach ($v as $i=>$w):
        				if (empty($w[1]))
        					continue;
        				$this->model->where($w[0], $w[1]);
        			endforeach;
        			break;
        		case 'order':
        		case 'orderby':
					$this->model->orderBy($v);
        			break;
        		endswitch;
        	endforeach;
        }
        
        if (!$ordered)
        	$this->model->orderBy($this->model->firstField());
        
        $row_Template['lista'] = $this->model->select();
        
        /*--------------------------------------------------------------
        Page values
        --------------------------------------------------------------*/
        
		$this->model->reset();
        $this->model->fields('COUNT(*)');
        
		if (is_array($this->_filter)) {
        	foreach ($this->_filter as $mode=>$v):
        		switch (strtolower($mode)):
        		case 'where':
        			foreach ($v as $i=>$w):
        				if (empty($w[1]))
        					continue;
        				$this->model->where($w[0], $w[1]);
        			endforeach;
        			break;
        		endswitch;
        	endforeach;
        }
        
        $row_Template['pag'] = $this->model->select();
        $row_Template['pag'] = $this->paginator($row_Template['pag'][0][0]);
        
        $this->view->data['row_Template'] = $row_Template;
    	$this->view->render(true);
    }
	/**
	 * Trae los datos para generar el formulario carga
	 * 
	 */
    function nuevoAction($redirect = true){
    	$this->newAction($redirect);
    }
    function newAction($redirect = true){
    	global $_GET, $_POST, $row_Template;
    	
    	if (!$this->isScaffolded())
    		return false;
    		
    	$this->scaffoldingForm();
    	
        /*--------------------------------------------------------------
        Save the data
        --------------------------------------------------------------*/
    		
    	if (empty($_POST['submit'])) {
    		$this->view->data['row_Template'] = $row_Template;
    		$this->view->render(true);
    		
    		return;
    	}
    	
    	foreach ($row_Template['form'] as $row_Form) {
    		if ($row_Form['Key'] != 'PRI')
    			continue;
    		
    		unset($_POST[$row_Form['Field']]);
    	}
    	
    	if (is_array($this->form['def_vaules'])) {
    		foreach ($this->form['def_vaules'] as $def_field=>$def_value):
    			$_POST[$def_field] = $def_value;
    		endforeach;
    	}
    	
    	$_POST['id'] = $this->model->insert(__FILE__, __LINE__, $_POST);
    	
    	if ((int)$_POST['id'] <> 0)
    		$this->fileUpload(strtolower($this->_name), (int)$_POST['id']  );
    	
//    	echo '<hr>',$id,'<hr>';
//    	PK_debug('','','output');
//    	die();
    	
    	$url = url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'],'return');
    		
    	if ($redirect === true)
    		go($url);
    	
    	$this->view->data['row_Template'] = $row_Template;
    	$this->view->render(true);
    }
	/**
	 * Trae los datos para generar el formulario de edicion
	 * 
	 */
    function editarAction($redirect = true){
    	$this->editAction($redirect);
    }
    function editAction($redirect = true){
    	global $row_Template, $_GET, $_POST;
    	
    	if (!$this->isScaffolded())
    		return false;
    	
        $this->scaffoldingForm();
        
    	
        /*--------------------------------------------------------------
        Save the data
        --------------------------------------------------------------*/
    	
    	if (empty($_POST['submit'])) {
    		$this->view->data['row_Template'] = $row_Template;
    		$this->view->render(true);
    		
    		return;
    	}
    		
    	$index_search = $this->model->_struct;
    	
    	foreach ($index_search as $row_Form) {
    		if ($row_Form['Key'] != 'PRI')
    			continue;
    			
    		$index = $row_Form['Field'];
    	}
    	
    	unset($_POST[$index]);
    	
    	if (is_array($this->form['def_vaules'])) {
    		foreach ($this->form['def_vaules'] as $def_field=>$def_value):
    			$_POST[$def_field] = $def_value;
    		endforeach;
    	}
    	
    	$this->model->update(__FILE__, __LINE__, $_POST, $index, $_GET['id']);
    	
    	//echo strtolower($this->_name).' - '.(int)$_GET['url']['id'].'<hr>';
    	
    	if ((int)$_GET['id'] > 0)
    		$this->fileUpload(strtolower($this->_name), (int)$_GET['id']);
    	
//    	PK_debug('','','output');
//    	die();
    	
    	$url = url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action='.$_GET['action'].'&id='.$_GET['id'].'&slug='.$_GET['slug'],'return');
    		
    	if ($redirect === true)
    		go($url);
    	
    	$this->view->data['row_Template'] = $row_Template;
    	$this->view->render(true);
    }
	/**
	 * Trae los datos para mostrar en los detalles
	 *
	 */
    function detallesAction(){
    	$this->detailsAction();
    }
    function detailsAction(){
    	global $row_Template, $_GET;
        
    	if (!$this->isScaffolded())
    		return false;
    	
    	$row_Template['form'] = $this->model->_struct;
    		
		foreach ($row_Template['form'] as $row_Form) {
    		if ($row_Form['Key'] != 'PRI')
    			continue;
    			
    		$index = $row_Form['Field'];
    	}
    		
    	$this->model->reset();
    	$this->model->where($this->model->name.'.'.$index, (int)$_GET['url']['id']);
    	$this->model->limit(1);
    	$row_Template['lista'] = $this->model->select();
    	$row_Template['lista'] = $row_Template['lista'][0];
    	
		foreach ($row_Template['form'] as $row_Form) {
    		if ($row_Form['Key'] != 'PRI')
    			continue;
    			
    		unset($row_Template['lista'][$row_Form['Field']]);
    	}
    	
    	if (!is_array($row_Template['lista']))
    		$row_Template['lista'] = array();
    	
    	foreach ($row_Template['lista'] as $i=>$v) {
    		if (!is_numeric($i))
    			continue;
    			
    		unset($row_Template['lista'][$i]);
    	}
    	
    	$this->view->data['row_Template'] = $row_Template;
    	$this->view->render(true);
    }
    /**
     * Borra la fila dada
     *
     */
    function eliminarAction(){
    	$this->deteleAction();
    }
    function deleteAction(){
    	global $row_Template, $_GET, $_POST;
    	
    	if (!$this->isScaffolded())
    		return false;
    		
		$row_Template['form'] = $this->model->_struct;
    	
    	foreach ($row_Template['form'] as $row_Form) {
    		if ($row_Form['Key'] != 'PRI')
    			continue;
    			
    		$index = $row_Form['Field'];
    	}
    	
        $this->model->_driver->query(__FILE__, __LINE__, 'DELETE FROM '.$this->_name.' WHERE '.$index.'='.(int)$_GET['id']);
    	
//    	PK_debug('','','output');
//    	die();
    	
    	go(url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'],'return'));
    }
	/**
	 * Habilita el scaffolding
	 *
	 */
	function enableScaffolding(){
    	global $config;
    	
    	$this->_scaffolding = true;
    	
		$config['scaffolding'] = $this->_scaffolding;
	}
	/**
	 * Deshabilita el scaffolding
	 *
	 */
	function disableScaffolding(){
    	global $config;
		
    	$this->_scaffolding = false;
    	
		$config['scaffolding'] = $this->_scaffolding;
	}
	/**
	 * Retorna el valor del 
	 *
	 */
	function isScaffolded() {
		if ($this->_scaffolding === false || !is_object($this->model))
    		return false;
		
    	return true;
	}
	/**
	 * Deshabilita para subir archivos
	 *
	 */
	function disableFileUpload(){
    	global $config;
		
    	$this->_fileUpload = false;
    	
		$config['fileUpload'] = $this->_fileUpload;
	}
	/**
	 * Habilita para subir archivos
	 *
	 */
	function enableFileUpload(){
    	global $config;
		
    	$this->_fileUpload = true;
    	
		$config['fileUpload'] = $this->_fileUpload;
	}
	/**
	 * Setea el nombre de la clase
	 *
	 * @param string $class_name
	 */
	function setName($class_name = null) {
		if (empty($class_name))
			$class_name = get_class($this);
			
		$this->_name = $class_name;
	}
   /**
     * Crea el paginador
     *
     * @param integer $cantidad_de_registros
     * @param integer $limite_de_registros_por_pagina
     * @return array
     */
    function paginator($cantidad_de_registros = 0, $limite_de_registros_por_pagina = 0) {
    	global $_GET;
    	
    	if ((int) $cantidad_de_registros < 1) {
    		if ((int) $_GET['reg']['total'] < 1) {
    			return;
    		}
    		$cantidad_de_registros = $_GET['reg']['total'];
    	}
    	
    	if ((int) $limite_de_registros_por_pagina == 0) {
    		if (!defined('LIMIT'))
    			return;
    		$limite_de_registros_por_pagina = LIMIT;
    	}
    	
    	$rango = 5;
    	
    	if ((int)$limite_de_registros_por_pagina < 1)
    		$limite_de_registros_por_pagina = LIMIT;
    	
    	$paginador['reg']['total'] = $cantidad_de_registros;
    	
    	$paginador['reg']['from'] = (ceil($_GET['page'] * $limite_de_registros_por_pagina) - $limite_de_registros_por_pagina) + 1;
    	$paginador['reg']['to'] = $paginador['reg']['from'] + $limite_de_registros_por_pagina - 1;
    	
    	if ($paginador['reg']['to'] > $paginador['reg']['total'])
    		$paginador['reg']['to']  = $paginador['reg']['total'];
    		
    	$paginador['pags'] = ceil($cantidad_de_registros / $limite_de_registros_por_pagina);
    	
    	$paginador['ini'] = (int)$_GET['page'] - $rango;
    	
    	if ($paginador['ini'] < 1) {
    		$paginador['ini'] = 1;
    	}
    		
    	$paginador['fin'] = $_GET['page'] + ($rango + $rango - $paginador['ini']);
    	
    	if ($paginador['fin'] > $paginador['pags'])
    		$paginador['fin'] = $paginador['pags'];
    	
    	return $paginador;
    }
    /**
     * Sube el archivo a la carpeta de destino
     *
     * @param string $carpeta_de_destino
     * @param string $nombre_del_archivo
     * @return boolean
     */
    function fileUpload($carpeta_de_destino, $nombre_del_archivo) {
    	global $_FILES, $_POST;
    	$is_folder = false;
		
		if ($this->_fileUpload !== true)
			return;
    	
//    	print_r($_POST['eliminar_archivo']);
    	
    	if (is_array($_POST['eliminar_archivo'])) {
	    	foreach ($_POST['eliminar_archivo'] as $v):
	    		$file = basename($v);
//	    		echo '<br>-'.$v,': ',$file;
	    		if (empty($file))
	    			continue;
//	    		echo ' : ',$carpeta_de_destino,'/',$v,' : ',str_replace('-', '/', $v);
	    		$this->fileDelete($carpeta_de_destino.'/'.$v);
	    		$this->fileDelete($v);
	    	endforeach;
    	}
    	
    	$dir = PK_UPLOAD_DIR.$carpeta_de_destino.'/'.$nombre_del_archivo;
    	
    	PK_debug('File Upload Dir 1', $dir);
	    	
    	if (is_dir($dir)) {
			// Abrir un directorio conocido, y proceder a leer sus contenidos
		    if ($gd = opendir($dir)) {
		    	$path = PK_UPLOAD_DIR.strtolower($this->_name.'/'.$nombre_del_archivo.'/');
		        while (($archivo = readdir($gd)) !== false) {
		        	if (filetype($path.$archivo) == 'dir')
		        		continue;
		        	
		            //rename($path.$archivo, $path.'_'.$archivo);
		            $x = (int)str_replace(substr($archivo, -4), '', $archivo); 
		        }
		        closedir($gd);
		    }
		}
		
		$x = (int)$x;
    	
    	$this->fileDelete($carpeta_de_destino.'/'.$nombre_del_archivo);
    	
    	if (substr($carpeta_de_destino, -1) != '/')
    		$carpeta_de_destino .= '/';
    	
    	$carpeta_de_destino = PK_UPLOAD_DIR.$carpeta_de_destino;
    	
    	PK_debug('File Upload Dir 2', $dir);
    	
    	if (!is_dir($carpeta_de_destino))
    		return false;
    		
    	if (!is_dir($carpeta_de_destino.$nombre_del_archivo))
    		mkdir($carpeta_de_destino.$nombre_del_archivo);
    	
    	foreach ($_FILES as $v) {
    		if (is_array($v['name']))    			
    			$is_folder = true;
    		
    		if ($is_folder === false) {
    			$t = array();
    			
    			foreach ($v as $i=>$val):
    				$t[$i][0] = $val;
    			endforeach;
    			
    			$v = $t;
    			
    			unset($t);
    			
    			reset($v);
    		}
    		
    		$folder = $carpeta_de_destino.$nombre_del_archivo.'/';
    		$x = (int)$x;
    		
    		for ($i=0; $i<count($v['name']); $i++):
    			$ext = $ext = strtolower(substr($v['name'][$i], -4));
    			
    			if (substr($ext, 0, 1) != '.')
    				continue;
    			
    			$file = fexists($folder.$x, '', false);
    			$top = 100;
    			
    			while (!empty($file)):
    				$x++;
    				$file = fexists($folder.$x, '', false);
    				
    				if ($x == $top)
    					break;
    			endwhile;
    			
    			copy($v['tmp_name'][$i], $folder.$x.$ext);
    			chmod($folder.$x.$ext, 0777);
    		endfor;
    	}
    	
    	return true;
    }
    /**
     * Borra el archivo o carpeta especificado
     *
     * @param string $archivo_para_borrar
     * @return boolean
     */
    function fileDelete($archivo_para_borrar, $delete='files') {
//    	echo '<hr>';
    	
    	if (false != strpos($archivo_para_borrar, '.'))
    		$archivo_para_borrar = strtr($archivo_para_borrar, array(strrchr($archivo_para_borrar, '.')=>''));
    	
    	$archivo_para_borrar = PK_UPLOAD_DIR.str_replace('-', '/', $archivo_para_borrar);
    	
//    	echo $archivo_para_borrar,'<br>';
    	
    	$archivo_para_borrar = fexists($archivo_para_borrar,'',false);
    	    	
//    	echo $archivo_para_borrar,'<br>';
		if (file_exists($archivo_para_borrar) || is_dir($archivo_para_borrar))
    	chmod($archivo_para_borrar, 0777);
    	
    	if (is_dir($archivo_para_borrar) && $delete == 'all') {
    		@rmdir($archivo_para_borrar);
    		
    		$archivo_para_borrar = str_replace(PK_UPLOAD_DIR, PK_UPLOAD_DIR.'$tmp/', $archivo_para_borrar);
    		chmod($archivo_para_borrar, 0777);
//    		echo $archivo_para_borrar.'<br>';
    		@rmdir($archivo_para_borrar);
    		return true;
    	}
    	
    	if (file_exists($archivo_para_borrar) || is_dir($archivo_para_borrar)) {
    		chmod($archivo_para_borrar, 0777);
//    		echo $archivo_para_borrar.'<br>';
   			unlink($archivo_para_borrar);
    	}
   		
   		$dir = dirname(dirname(dirname($archivo_para_borrar))).'/$tmp/'.basename(dirname(dirname($archivo_para_borrar))).'/'.basename(dirname($archivo_para_borrar)).'/';
	    	
    	if (is_dir($dir)) {
			// Abrir un directorio conocido, y proceder a leer sus contenidos
		    if ($gd = opendir($dir)) {
		        while (($archivo = readdir($gd)) !== false) {
		        	if (filetype($dir.$archivo) == 'dir')
		        		continue;
		        	
//		        	echo $dir.$archivo.'<br>';
		        	chmod($dir.$archivo, 0777);
		            @unlink($dir.$archivo);
		        }
		        closedir($gd);
		    }
		}
		
    	return true;
    }
	/**
     * Genera el formulario para el scaffolding
     *
     */
    function scaffoldingForm() {
    	global $row_Template, $_GET;
    	
    	$this->scaffoldingHead();
    	
    	if (strpos($_GET['id'], '_') !== false) {
    		$_GET['id'] = split('_', $_GET['id']);
    		$_GET['id'] = $_GET['id'][count($_GET['id'])-1];
    	}
    	
    	/*--------------------------------------------------------------
        Trae los datos de la tabla
        --------------------------------------------------------------*/
    	$form = $this->model->_struct;
    	
    	$tags_a_ser_inicializados = array();
    	
    	$j = 0;
    	
    	/*--------------------------------------------------------------
        Genera el formulario, si hay un ID trae los valores
        --------------------------------------------------------------*/
    	if ((int)$_GET['id'] > 0) { 
	    	$index_search = $this->model->_struct;
	    	
	    	foreach ($index_search as $row_Form) {
	    		if ($row_Form['Key'] != 'PRI')
	    			continue;
	    			
	    		$index = $row_Form['Field'];
	    		break;
	    	}
    		
	    	$this->model->reset();
	    	$this->model->fields($this->model->name.'.*');
	    	$this->model->where($this->model->name.'.'.$index, (int)$_GET['id']);
	    	$this->model->limit(1);
	    	$values = $this->model->select();
	    	$values = $values[0];
    	}
    		
    	foreach ($form as $i=>$v) {
//    		echo '<pre><b>', $v['Field'], '</b> in_array = ', (in_array($v['Field'], $this->form['skip_fields']))?' si':'no', "\n</pre>";
    		if (in_array($v['Field'], $this->form['skip_fields']))
    			continue;
	    		
    		$rel = $this->model->_relations[$v['Field']];
			
    		if (is_array($this->form['def_values'][$v['Field']])) {
    			$v['value'] = $this->form['def_values'][$v['Field']];
    			$v['value']['selected'] = $values[$v['Field']];
    		} elseif (!empty($this->form['def_values'][$v['Field']])) {
    			$v['value'] = $this->form['def_values'][$v['Field']];
    		} elseif (!empty($rel)) {
    			get('model', $rel['table']);
    			if (class_exists(ucfirst($rel['table']))) {
    				eval('$model = new '.$rel['table'].';');
    				$model->reset();
//    				$model->fields($rel['fk'].','.$rel['text']); 
					$model->fields($model->name.'.'.$model->firstField('int').', '.$model->name.'.'.$model->firstField());   				
    				$model->orderBy($rel['text']);
    				$val = $model->select();
    				foreach ($val as $j=>$v2) {
    					$x = 0;
    					foreach ($v2 as $v3) {
	    					$val[$j][$x] = $v3;
	    					++$x;
    					}
    				}
    				$val['selected'] = $values[$v['Field']];
    			}
    			$v['value'] = $val;
//    			print_r($v);
    		} else {
				$v['value'] = $values[$v['Field']];
    		}
    		
    		$row_Template['form'][$j] = $v;
    		++$j;
    	}
    	
//    	print_r($row_Template['form']);
    	
//    	echo '<pre>',print_r($row_Template['form'], true), "\n", $this->form['extra_fields'],'</pre>';
    	
    	if (is_array($this->form['extra_fields'])) {
    		$row_Template['form'] = array_merge($row_Template['form'], $this->form['extra_fields']);
    	}
    	
//    	echo '<pre><hr>',print_r($row_Template['form'], true),'</pre>';
    	/*--------------------------------------------------------------
        Trae los archivos *.js
        --------------------------------------------------------------*/
    	$this->scaffoldingHead();
    	
    	/*--------------------------------------------------------------
        Mostramos todas las fotos que tiene este registro
        --------------------------------------------------------------*/
//    	echo PK_UPLOAD_DIR.$this->_name.'/'.$_GET['url']['id'].'<br>';
    	
    	if ((int)$_GET['id'] > 0 && $this->_fileUpload === true) {
	    	if (is_dir(PK_UPLOAD_DIR.$this->_name.'/'.$_GET['id'])) {
				// Abrir un directorio conocido, y proceder a leer sus contenidos
				$dir = PK_UPLOAD_DIR.$this->_name.'/'.$_GET['id'].'/';
			    if ($gd = opendir($dir)) {
			        while (($archivo = readdir($gd)) !== false) {
//			        	echo $archivo.'<br>';
			        	if (filetype($dir . $archivo) == 'dir')
			        		continue;
			        		
			        	$row_Template['form'][] = array('Field'=>'eliminar_archivo[]','value'=>$this->_name.'-'.$_GET['id'].'-'.$archivo,'Type'=>'files');
//			            echo "nombre de archivo: $dir$archivo : tipo de archivo: " . filetype($dir . $archivo) . "<br>";
//			            print_r($row_Template['form'][$file_index_no]);
			        }
			        closedir($gd);
			    }
			}
    	}
    	
		if (is_dir(PK_UPLOAD_DIR.$this->_name) && $this->_fileUpload === true) {
			for ($i=0; $i<5; ++$i):
	    		$row_Template['form'][] = array('Field'=>'archivos[]','value'=>'','Type'=>'file');
	    	endfor;
		}
    	
    	$row_Template['form'][count($row_Template['form'])] = array('Field'=>'submit','value'=>'Guardar Registro','Type'=>'submit');
    	
    	$this->error_msg();
    }
    
   /**
     * Retorna el HTML del Scaffolding para el Head
     *
     * @param string / array $tags_a_ser_inicializados
     */
    function scaffoldingHead() {
    	global $row_Template;
    	
    	$this->scaffoldingLib('bbcode');
    	
    	switch ($this->_scaffoldingLib):
    	case 'wymeditor':
	    	$row_Template['scaffolding']['head'] = '<script type="text/javascript" src="'.SERVER_URL.'wymeditor/jquery.wymeditor.pack.js"></script>
	<script type="text/javascript">
	jQuery(function() {
		$("textarea.wymeditor").wymeditor();
	});
	</script>';
	    	break;
    	case 'bbcode':
    		$row_Template['scaffolding']['head'] = '<script type="text/javascript" src="bbeditor/ed.js"></script>';
    		break;
    	default:
    		return false;
    	endswitch;
    	
    	return true;
    }
    
    function scaffoldingLib($libreria = '') {
    	global $row_Template;
    	
    	if (!empty($this->_scaffoldingLib))
    		return;
    	
    	$this->_scaffoldingLib = '';
    	
    	$allowed = array('bbcode', 'wymeditor');
    	
    	if (!in_array($libreria, $allowed))
    		return false;
    		
    	$this->_scaffoldingLib = $libreria;
    	
    	$row_Template['scaffolding']['lib'] = $libreria;
    } // scaffoldingLib()
    
    function loginRequired() {
    	global $_SESSION;
    	
    	defined('LOGIN_URL') or define('LOGIN_URL', $_SERVER['HTTP_REFERER']);
    	
    	if (!PK_Users::isLoged()) {
    		go(url(LOGIN_URL, 'return'));
    	}
    } // loginRequired()
    
    function formDefValues($values = array())
    {
    	$this->form['def_values'] = $values;
    }
    
	function formDefTypes($types = array())
    {
    	$this->form['def_types'] = $types;
    }
    
    function formSkipFields($fields = array())
    {
    	$this->form['skip_fields'] = $fields;
    }
    
    function filter($filters)
    {
    	$this->_filter = $filters;
    }
    
	function formExtraFields($extraFields = array())
    {
    	$this->form['extra_fields'] = $extraFields;
    }
    
    function owner($var='select', $field_name='')
    {
    	if (!empty($field_name)) {
    		if ($var == 'where')
    			$this->_owner['field']['where'] = $field_name;
    		else
    			$this->_owner['field']['select'] = $field_name;
    	}
    	
    	if (empty($this->_owner['field']['where']) || empty($this->_owner['field']['select']))
    		return 0;
    	
    	if ((int)$this->_owner == 0) {
    		$this->model->reset();
			$this->model->fields($this->_owner['field']['select']);
			$this->model->where($this->_owner['field']['where'], (int)$_GET['id']);
			$this->_owner['id'] = $this->model->select();
			$keys = array_keys($this->_owner['id'][0]);
			$this->_owner['id'] = $this->_owner['id'][0][$keys[0]];
    	}
    	
    	return $this->_owner['id'];
    }
} // PK_Controller
?>