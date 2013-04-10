<?php
class Home_controller extends PK_Controller
{
	public function __construct() {
		$this->init();
	} // __construct
	
	function indexAction()
	{
		$this->view->render();
	}
}