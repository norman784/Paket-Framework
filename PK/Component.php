<?php
/**
 * User jQueryUI
 * */
class PK_Component extends PK_Base
{
	private $_return_mode = 'js';
	
	function is_js()
	{
		$this->_return_mode = 'js';
	}
	
	function is_html()
	{
		$this->_return_mode = 'html';
	}
	
	function create($id, $tab_options)
	{
		$output = '';
		
		if ($this->_return_mode == 'js') {
			$output = $this->create_js($id, $tab_options);
		} else {
			$output = $this->create_html($id, $tab_options);
		}
		
		return $output;
	}
	
	function create_js($id, $tab_options)
	{
		
	}
	
	function create_html($id, $tab_options)
	{
		
	}
}
?>