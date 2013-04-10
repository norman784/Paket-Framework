<?php
class PK_View extends PK_Base
{
	private $layout = '';
	private $view = '';
	public $data = '';
	
	function __construct($layout = '', $view = '')
	{
		global $_GET;
		
		if (empty($layout))
			$layout = $_GET['action'];
		
		if (empty($view))
			$view = $_GET['action'];
		
		$this->setLayout($layout);
		$this->setView($view);
	}
	
	public function getView()
	{
		return $this->view;
	}
	
	public function getLayout()
	{
		return $this->layout;
	}
	
	public function setLayout($layout = '')
	{
		if (empty($layout)) {
			global $_GET;
			$layout = $_GET['controller'];
		}
		
		$this->layout = $layout;
	}
	
	public function setView($view = '')
	{
		if (empty($view)) {
			global $_GET;
			$view = $_GET['action'];
		}
		
		$this->view = $view;
	}
	
	public function render($mode='layout', $scaffolding = false)
	{
		switch ($mode):
		case 'layout':
			return $this->__layout_render();
		case 'view':
			return $this->__view_render($scaffolding);
		endswitch;
	}
	
	private function __layout_render()
	{
		global $_GET;
		
		$plugin_dirs = array();
		
		if (!empty($_GET['plugin'])) {
			$plugin_dirs = array(
				PK_PLUGIN_DIR.$_GET['plugin'].'/layouts/',
				PK_GLOBAL_PLUGIN_DIR.$_GET['plugin'].'/layouts/',
			);
		}
		
		$dirs = array(
			PK_LAYOUT_DIR,
			PK_DEF_LAYOUT_DIR,
		);
		
		$dirs = array_merge($plugin_dirs, $dirs);
		
		return $this->__render($dirs, $this->layout, 'default');
	}
	
	private function __view_render($scaffolding = false)
	{
		global $_GET;
		
		$plugin_dirs = array();
		
		if (!empty($_GET['plugin'])) {
			$plugin_dirs = array(
				PK_PLUGIN_DIR.$_GET['plugin'].'/view/'.$_GET['controller'].'/',
				PK_GLOBAL_PLUGIN_DIR.$_GET['plugin'].'/view/'.$_GET['controller'].'/',
			);
		}
		
		$dirs = array(
			PK_VIEW_DIR.$_GET['controller'].'/',
			PK_DEF_VIEW_DIR.'scaffolding/',
		);
		
		$dirs = array_merge($plugin_dirs, $dirs);
		
		if ($scaffolding === true) {
			die(PK_SCAFFOLDING_DIR.'view/');
			$dirs = array_merge($dirs, array(PK_SCAFFOLDING_DIR.'view/'));
		}
		
		return $this->__render($dirs, $this->view, VIEW_ACTION);
	}
	
	private function __render($dirs = array(), $file = '', $default_file)
	{
//		echo '<b>dirs:</b> <pre>'.print_r($dirs, true).'</pre><hr>';
		if (!is_array($dirs) || count($dirs) == 0)
			return false;
			
		if (empty($file))
			$file = $default_file;

//		echo '<b>file:</b> '.$file.'<hr>';	
			
		if (empty($file))
			return false;
			
		if (substr($file, -4) != '.php')
			$file .= '.php';
			
		if (substr($default_file, -4) != '.php')
			$default_file .= '.php';
		
		if (is_array($this->data)) {
			foreach ($this->data as $key=>$var):
				global $$key;
				$$key = $var;
			endforeach;
		}
		
		foreach ($dirs as $dir):
			if (substr($dir, -1) != '/')
				$dir .= '/';
			
//			echo $dir.$file.'<br>';
//			echo $dir.$default_file.'<hr>';
			
			if (!file_exists($dir.$file)) {
				if (!file_exists($dir.$default_file)) {
					continue;
				} else {
					$file = $default_file;
				}
			}
			
			include $dir.$file;
			return true;
		endforeach;
		
		return false;
	}
	
	public function resultSet()
	{
		echo 'Resultados de X hasta X de X';
	}
	
	public function paginator($show_pages_numbers = true, $first_text = '&lt;&lt;', $last_text = '&gt;&gt;', $prev_text = '&lt;', $next_text = '&gt;')
	{
		global $_GET;
		
		$paginator = PK_Controller::paginator();
		
		if ((int)$paginator['pags'] < 2)
			return;
		
		$url = '?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action='.$_GET['action'].'&slug='.$_GET['slug'].'&page=page___P__&extra='.$_GET['extra'];
		$url = url($url, 'return');
		$url = strtr($url, array('__P__'=>'%s'));
		
		$output = '<ul class="pag">';
		
		if ($_GET['page'] > 1) {
			if (!empty($first_text))
				$output .= '<li><a href="'.sprintf($url, 1).'">'.$first_text.'</a></li>';
			if (!empty($prev_text))
				$output .= '<li><a href="'.sprintf($url, ($_GET['page']-1)).'">'.$prev_text.'</a></li>';
		}
		
		if ($show_pages_numbers === true) {
			for ($i=$paginator['ini']; $i<=$paginator['fin']; ++$i):
				$output .= '<li><a href="'.sprintf($url, $i).'">'.$i.'</a></li>';
			endfor;
		}
		
		if ($_GET['page'] < $paginator['pags']) {
			if (!empty($next_text))
				$output .= '<li><a href="'.sprintf($url, ($_GET['page']+1)).'">'.$next_text.'</a></li>';
			if (!empty($last_text))
				$output .= '<li><a href="'.sprintf($url, $paginator['pags']).'">'.$last_text.'</a></li>';
		}
		
		echo $output;
	}
	
	public function url($value, $echo = true) {
		global $config;
		
		$value = html_entity_decode($value);
		
		$var = str_replace('?', '', $value);
		$var = explode('&', $var);
		$value = '';
		$amp = '?';
		$bar = '';
		
		foreach ($var AS $val):
			
			$val = explode('=', $val);
			$val[0] = strip_special_chars(trim($val[0]));
			$val[1] = strip_special_chars(trim($val[1]));
			
			if (empty($val[1]) || ($val[0] == 'action' && $val[1] == VIEW_ACTION))
				continue;
			
			if ($config['friendly_url'] == 'enabled') {
				$value .= $bar.$val[1];
				$bar = '/';
			} else {
				$value .= $amp.$val[0].'='.$val[1];
				$amp = '&';
			} // else
		endforeach;
		
		$server_url = SERVER_URL;
		
		if (substr($server_url, -1) != '/')
			$server_url .= '/';
		
		$value = $server_url.$value;
		
		if ($echo === true) {
			echo $value;
			return;
		}
		
		return $value;
	}
}
?>
