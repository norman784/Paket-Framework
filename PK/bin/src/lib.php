<?php
defined("EOL") or define("EOL", "\n");

function get_files($folders, $base_path = '.') {
	$files = array();
	
	foreach ($folders as $folder):
		$path = "{$base_path}/{$folder}";
		
		if ($handle = opendir($path)) {
			
			while (false !== ($entry = readdir($handle))):
				if (is_dir($entry)) continue;
				
				$tmp = base64_encode(file_get_contents("{$path}/{$entry}"));
				
				$files[$folder][$entry] = $tmp;
			endwhile;
			
			closedir($handle);
		}
	endforeach;
	
	return $files;
}
?>