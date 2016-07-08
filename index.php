<?php
if(isset($_FILES["file"])){
	require("srtFile.php");
	try{
		foreach($_FILES as $file){
			// Upload file on the server
			$upload = move_uploaded_file($file['tmp_name'],"temp/".$file["name"]);
			if($upload){
				$script_name = $_SERVER['SCRIPT_NAME'];
				$script_name = explode("/",$script_name);
				array_pop($script_name);
				$script_dir = implode("/",$script_name);
				$files_uploaded[] =  $_SERVER['HTTP_ORIGIN']."/".$script_dir."/temp".$file["name"];
			}else{
				echo 'Error on uploading file';
			}
			$name = explode(".", $file["name"]);
			array_pop($name);
			$name = implode(".",$name);
			$srt = new \SrtParser\srtFile("temp/".$file["name"]);
			$srt->setWebVTT(true);
			$srt->build(true);
			$srt->save("build/".$name.".vtt", true);
			//unlink temporary file
			unlink("temp/".$file["name"]);
			//download builded file
			$file = "build/".$name.".vtt";
			if(is_file($file)) {	
				// required for IE
				if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
				// get the file mime type using the file extension
				switch(strtolower(substr(strrchr($file, '.'), 1))) {
					case 'pdf': 
						$mime = 'application/pdf'; 
						break;
					case 'zip': 
						$mime = 'application/zip'; 
						break;
					case 'jpeg':
					case 'jpg': 
						$mime = 'image/jpg'; 
						break;
					case 'vtt' :
						$mime = 'application/octet-stream';
						break;
					default: $mime = 'application/force-download';
				}
				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($file)).' GMT');
				header('Cache-Control: private',false);
				header('Content-Type: '.$mime);
				header('Content-Disposition: attachment; filename="'.basename($file).'"');
				header('Content-Transfer-Encoding: binary');
				header('Content-Length: '.filesize($file));
				header('Connection: close');
				readfile($file);
				unlink($file);
				exit();
			}
		}
	}
	catch(Exeption $e){
		echo "Error: ".$e->getMessage()."\n";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>SRT to VTT</title>
	</head>
	<body>
		<form enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
			<label for="file">srt file : </label>
			<input type="file" id="file" name="file"/>
			<input type="submit" value="Convert">
		</form>
	</body>
</html>