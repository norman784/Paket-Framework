#!/usr/bin/env php

<?php
require_once "src/lib.php";

$files = get_files(array("config", "controller", "layouts", "model", "public/js", "public/css", "view/home"), "./src/app");
$commando = get_files(array("Commando", "Commando/Util"), "./src");

$content = '#! /usr/bin/env php'.EOL.'<?php'.EOL;

foreach ($files as $folder=>$_files):
	foreach($_files as $file=>$file_content):
		$content .= sprintf('$files["%s"]["%s"] = "%s";'.EOL, $folder, $file, $file_content);
	endforeach;
endforeach;

$content .= '?>'.EOL;

foreach ($commando as $folder=>$_files):
	foreach($_files as $file=>$file_content):
		$content .= base64_decode($file_content);
	endforeach;
endforeach;

$bin = explode(EOL, file_get_contents("./src/bin.php"));
$remove = false;

foreach ($bin as $line):
	if ($line == "// REMOVE START") $remove = true;
	elseif ($line == "// REMOVE END") $remove = false;
	
	if ($remove) continue;
	
	$content .= $line.EOL;
endforeach;

file_put_contents("./pk", $content);