<?php
class PK_ErrorHandling extends PK_Base
{
	private static $_errors = array();
	private static $_show_errors = false;
	private static $_show_all = false;
	private static $_send_errors = true;
	private static $_send_to = '';
	private static $_site_title = '';
	private static $_site_url = '';
	
	function __construct()
	{
		$this->ini();
	}
	
	function init()
	{
		global $config;
		
		if (true !== $config['debug']['enabled']) {
			error_reporting(0);
			ini_set('display_errors', 'off');
		} else {
			error_reporting(E_ALL ^ E_NOTICE);
			ini_set('display_errors', 'on');
		}
		
		self::$_show_errors = (boolean)$config['debug']['enabled'];
		self::$_show_all = (boolean)$config['debug']['show_all'];
		
		if (false === self::$_show_errors) {
			self::$_send_errors = true;
		} else {
			self::$_send_errors = false;
		}
			
		self::$_send_to = MAIL_SITEADMIN;
		self::$_site_url = SITE_TITLE;
		self::$_site_url = SITE_URL;
	}
}
?>