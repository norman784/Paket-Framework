<?php
class Files_controller
{
	function __construct()
	{
		global $_GET;
		
		$allowed_ext = array (
			// archives
			'zip' => 'application/zip',
			
			// documents
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			  
			// executables
			'exe' => 'application/octet-stream',
			
			// images
			'gif' => 'image/gif',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			
			// audio
			'mp3' => 'audio/mpeg',
			'wav' => 'audio/x-wav',
			
			  // video
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'avi' => 'video/x-msvideo'
		);
		
		set_time_limit(0);
		
		$file_path = PK_UPLOAD_DIR . strtr($_GET['slug'], array('-'=>'/'));
		$fext = strtolower(substr(strrchr($file_path,"."),1));
		
		if (!file_exists($file_path)) {
			die(TXT_FILE_NOT_FOUND);
		}
		
		if (!isset($allowed_ext[$fext])) {
			if  (function_exists('finfo_file')) {
				$handler = finfo_open(FILEINFO_MIME);
				$mtype = finfo_file($handler, $file_path);
				finfo_close($handler);
			} elseif (function_exists('mime_content_type')) {
				$mtype = mime_content_type($file_path);
			}
		} else {
			$mtype = $allowed_ext[$fext];
		}
		
		if ($mtype == '') {
			$mtype = 'application/force-download';
		}
		
		if (isset($_GET['slug'])) {
		  $asfname = $_GET['slug'];
		} else {
		  // remove some bad chars
		  $asfname = str_replace(array('"',"'",'\\','/'), '', $_GET['slug']);
		  if ($asfname === '') $asfname = $_GET['id'];
		}
		
		// set headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: $mtype");
		header("Content-Disposition: attachment; filename=\"$asfname\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($file_path));
		
		// download
		// @readfile($file_path);
		$file = @fopen($file_path,"rb");
		if ($file) {
		  while(!feof($file)) {
		    print(fread($file, 1024*8));
		    flush();
		    if (connection_status()!=0) {
		      @fclose($file);
		      exit();
		    }
		  }
		  @fclose($file);
		}
		
//		print_r($_GET);
		exit();
	}
}

function mime_type($file) {

    $os = strtolower(php_uname());
        $mime_type = '';

        // use PECL fileinfo to determine mime type
        if( function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME);
                $mime_type = finfo_file($finfo, $file);
                finfo_close($finfo);
        }

        // try to determine mime type by using unix file command
        // this should not be executed on windows
    if(!valid_src_mime_type($mime_type) && !(eregi('windows', $os))) {
                if(preg_match("/freebsd|linux/", $os)) {
                        $mime_type = trim(@shell_exec('file -bi $file'));
                }
        }

        // use file's extension to determine mime type
        if(!valid_src_mime_type($mime_type)) {

                // set defaults
                $mime_type = 'image/jpeg';
                // file details
                $fileDetails = pathinfo($file);
                $ext = strtolower($fileDetails["extension"]);
                // mime types
                $types = array(
                        'jpg'  => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png'  => 'image/png',
                        'gif'  => 'image/gif'
                );
                
                if(strlen($ext) && strlen($types[$ext])) {
                        $mime_type = $types[$ext];
                }
                
        }
        
        return $mime_type;

}
?>