<?php
$PK['PK']['version'] = '0.0.1';
$PK['PK']['date'] = '2008-11-12';

/**
 * PK Framework
 *
 * @category   PK
 * @package    PK_Form
 *
 * Leyenda
 * + agregado
 * ? obsoleto
 * - eliminado
 * / modificado
 */
class PK_Form extends PK_Base {

/*--------------------------------------------------------------
Create
--------------------------------------------------------------*/
	
	public function create($el) {
		global $img_count, $file_count;
		
		$has_label = true;
		
//		print_r($el);
		
		$img_count = (int)$img_count;
		
		$len[0] = strpos($el['Type'], '(');
		$len[1] = strpos($el['Type'], ')'); 
		
		if ($len[0] && $len[1])
			$el['Len'] = substr($el['Type'], ($len[0]+1), -1);
			
		if (!isset($el['Name']))
			$el['Name'] = $el['Field'];
		
		$el['Len'] = (int)$el['Len'];
		
		if ($el['Key'] == 'PRI') {
			$has_label = false;
			$el['Type'] = 'hidden';
			
			if(empty($el['value']))
				return;
		} elseif ($el['Name'] == 'password' || $el['Field'] == 'password') {
			$el['Type'] = 'password';
			$el['value'] = '';
		}
		
		switch ($el['Type']) {
		case 'title':
			$input = '';
			break;
		case 'textarea':
		case 'text':
			$input = self::textarea($el['Field'], $el['value'], $el['param']);
			break;
		case 'image':
		case 'img':
		/*
			$img_count++;
			$has_label = false;
			$input = self::image($el['Field'], $el['value'], $el['param']);
			break;
		*/
		case 'files':
			$file_count++;
			$has_label = false;
			$input = self::file($el['Field'], $el['value'], $el['param']);
			break;
		case 'button':
		case 'submit':
			$has_label = false;
			$el['param'] = array('class'=>'button');
		case 'checkbox':
		case 'radio':
		case 'radiobutton':
			$has_label = false;
		default:
			if (is_array($el['value'])) {
				$input = self::select($el['Field'], $el['value'], $el['param']);
				break;
			}
			$input = self::input($el['Name'], $el['Field'], $el['Type'], $el['value'], $el['param'], $el['Len']);
			break;
		}
		
		PK_debug('PK_Form :: $el', $el);
		
		if ($has_label === true) {
			$output = '<div class="clear"></div>';
			$output .= '<label>'.ucfirst(str_replace(array('_','[',']',' id'), array(' ','','',''), i18n($el['Name'])))."</label>\n";
//			$output .= "\n<br />\n";
			if ($el['Type'] == 'title')
				$output .= '<div class="clear"></div>';
		}
		
		$output .= $input;
		
		if ($img_count == 2) {
			$output .= '<div class="clear"></div>';
			$img_count = 0; 
		}
		
		echo $output;
	}

/*--------------------------------------------------------------
Input
--------------------------------------------------------------*/
	
	public function input($name, $id, $type = 'text', $value = '', $param=array(), $len = 0) {
		$allowed = split(',', 'text,submit,button,checkbox,radio,password,hidden,file');
		
		if (!in_array($type, $allowed))
			$type = $allowed[0];
		
		$output = '<input name="'.$id.'" id="'.$id.'" type="'.$type.'"';
		
		if (!empty($value))
			$output .= ' value="'.$value.'"';
		
		if ($len <> 0) {
			$output .= ' maxlength="'.$len.'" ';
		}
		
		if (!is_array($param))
			$param = array();
		
		foreach ($param as $i=>$v) {
			$output .= ' '.$i.'="'.$v.'"';
		}
		
		if ((false !== strpos(strtolower($name), 'date') || false !== strpos(strtolower($id), 'date')) && $param['class'] != 'date') {
			
		}
		
		$output .= ' />';
		
		if ($type == 'checkbox' || $type == 'radio' || $type == 'radiobutton') {
			$output .= '<label>'.ucfirst(str_replace(array('_','[',']',' id'), array(' ','','',''), i18n($name)))."</label>\n";
			$output .= '<div class="clear"></div>';
		}
		
		$output .= "\n<br />\n";
		
		return $output;
	}
	
/*--------------------------------------------------------------
Image
--------------------------------------------------------------*/
	
	function image($name, $value = '', $param=array()) {
		global $img_count;
		
		if (empty($value))
			return;
			
		++$img_count;
			
		$value = (substr($value, -4, 1) == '.')?substr($value, 0, -4):$value;
		
		$output = '<div class="admin_img">';
		$output .= '<input name="'.$name.'" id="'.$name.'" type="checkbox" value="'.$value.'" /> Eliminar Archivo<br>';
		$output .= '<div class="clear"></div>';
		$output .= '<div class="img">';
		$output .= '<img src="'.show_img($value, 200, 150, 'return').'">';
		$output .= '</div>';
		$output .= '<div class="clear"></div>';
		$output .= 'Ruta del archivo:<br>';
		$output .= '<input type="text" value="'.show_img($value, 1024, 512, 'return').'" class="text">';
		$output .= '</div>';

		return $output;
	}
	
/*--------------------------------------------------------------
File
--------------------------------------------------------------*/
	
	function file($name, $value = '', $param=array()) {
		if (empty($value))
			return;
		
		$ext = strtolower(substr($value,-3));
		
//		echo $ext.' - '.$value.'<br>';
		
		if (in_array($ext, array('jpg', 'png', 'gif')))
			return self::image($name, $value, $param);
			
		$output = '<div class="admin_img" style="height:250px;">';
		$output .= '<div class="clear"></div>';
		$output .= '<input name="'.$name.'" id="'.$name.'" type="checkbox" style="width:50px" value="'.$value.'" /> Eliminar Archivo';
		$output .= '<span class="ext_'.$ext.'">&nbsp;</span>';
		$output .= '<div class="clear"></div>';
		$output .= '<label>Ruta del archivo:</label><br>';
		$output .= '<input type="text" value="'.show_file($value, true).'" class="text">';
		$output .= '</div>';
		
		return $output;
	}
	
/*--------------------------------------------------------------
Textarea
--------------------------------------------------------------*/
	
	function textarea($name, $value = '', $param=array()) {
		global $row_Template;
		
		switch ($row_Template['scaffolding']['lib']):
		case 'bbcode':
			$value = str_replace(array("\r\n","\n",'&amp;'),array('\n','\n','&'), $value);
			$value = html_entity_decode($value, ENT_NOQUOTES);
			$output = '<script>Init(\''.$name.'\',50,7,\''.$value.'\'); </script>';
			break;
		case 'wymeditor':
			$eval = 'if (if ($i == \'class\') continue;';
			$class = 'class="wymeditor"';
		default:
			$output = '<textarea name="'.$name.'" id="'.$name.'"'.$class;
		
			if (!is_array($param))
				$param = array();
			
			foreach ($param as $i=>$v) {
				if (!empty($eval))
					eval($eval);
				$output .= ' '.$i.'="'.$v.'"';
			}
			
			$output .= '>'.$value.'</textarea>';
			break;
		endswitch;
		
		return $output;
	}
	
/*--------------------------------------------------------------
Select
--------------------------------------------------------------*/
	
	public function select($name, $value = array(), $param=array()) {
		
		$output = '<select name="'.$name.'" id="'.$name.'"';
		
		if (!is_array($param))
			$param = array();
		
		foreach ($param as $i=>$v) {
			$output .= ' '.$i.'="'.$v.'"';
		}
		
		$output .= '>';
		
		if (!is_array($value)) {
			$t = $value;
			$value = array();
			$value['selected'] = $t;
		}
		
		$output .= '<option value="0">'.TXT_NONE.'</option>';
		
		foreach ($value as $i=>$v) {
			if (!is_array($v))
				continue;
				
			$key = array_keys($v);
				
			$v[0] = $v[$key[0]];
			$v[1] = $v[$key[1]];
			
			if (empty($v[0]) && empty($v[1]))
				continue;
			
			$output .= '<option';
			if (!empty($v[0]))
				$output .= ' value="'.$v[0].'"';
			
			if (($v[0] == $value['selected'] && !empty($v[0])) || (!empty($v[1]) && $v[1] == $value['selected']))
				$output .= ' selected="selected"';
			
			$output .= '>';
			
			if (!empty($v[1]))
				$output .= $v[1];
			else
				$output .= $v[0];
			
			$output .= '</option>';
		}
		
		$output .= '</select>';
		
		return $output;
	}
}
?>