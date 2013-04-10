<?php

// REMOVE START
// Those lines are for development proporses only, it will be removed when bin has build
require_once 'Commando/Util/Terminal.php';
require_once 'Commando/Option.php';
require_once 'Commando/Command.php';

require_once 'lib.php';

$files = get_files(array("config", "controller", "layouts", "model", "public/js", "public/css", "view/home"), dirname(realpath(__FILE__)) . '/app');
// REMOVE END

$pk = new Command();

$pk->option('n')
	->aka('new')
	->describeAs('Create new application')
	->must(function($app_name){
		return ctype_alnum($app_name);
	})
	->map(function($app_name){
		global $files;
		
		$path = "./{$app_name}/";
		
		// Create the app folders
		mkdir($path);
		mkdir($path . "config");
		mkdir($path . "controller");
		mkdir($path . "layouts");
		mkdir($path . "model");
		mkdir($path . "plugins");
		mkdir($path . "public");
		mkdir($path . "public/images");
		mkdir($path . "public/css");
		mkdir($path . "public/js");
		mkdir($path . "view");
		mkdir($path . "view/home");
		
		file_put_contents($path . "/PK_project", "");
		
		foreach ($files as $folder=>$_files):
			foreach($_files as $file=>$content):
				$file = $path . $folder . "/{$file}";
				file_put_contents($file, base64_decode($content));
			endforeach;
		endforeach;
	});
?>