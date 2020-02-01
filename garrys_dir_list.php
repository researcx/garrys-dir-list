<?php
	$THUMBNAIL_FOLDER = "_thumbnails";
	$HIDDEN_FILES[] = "Thumbs.db";
	$HIDDEN_FILES[] = "index.php";
	$HIDDEN_FILES[] = $THUMBNAIL_FOLDER;
	$THUMBNAIL_WIDTH = 200;
	$THUMBNAIL_HEIGHT = 100;
	// Image Output;
	HandleImageOutput();
	?>
<html>
<head>
<title>Index Of.</title>
<style>
body { background-color: #eee; font-family: tahoma; font-size: 12px;}
#images, #folders, #files { border: 5px solid #00f; background-color: #fff; margin: 50px; padding: 25px; }
#folders { border-color: #fa0; }
#images { border-color: #0a0; }
#footer { text-align: center; }
DIV.image { width: <?php  echo $THUMBNAIL_WIDTH; ?>; height: <?php  echo $THUMBNAIL_HEIGHT; ?>; float: left; margin: 10px; text-align: center; border: 0px; }
IMG { border: 1px solid #444; background-color: #eee; }
</style>
</head>
<body>
<?php
	$Browse = $_GET["b"] ;
	
	if ( $Browse ){
		$Browse .= "/";
	}

	// Stop them accessing higher level folders
	
	if ( substr_count( $Browse, ".." ) > 0 || substr_count( $Browse, $THUMBNAIL_FOLDER ) > 0 ){
		echo "You don't have permission to access this folder!";
		exit();
	}

	$DIR = "./" . $Browse;
	$d = dir( $DIR );
	while (false !== ($entry = $d->read())){
		if ($entry[0] == '.') continue;
		if ( in_array( $entry, $HIDDEN_FILES) ) continue;
		
		// Don't list the folder if it has an index!
		if ($entry == 'index.html') exit();
		if ($entry == 'index.htm') exit();
		if ($entry == 'index.php') exit();
		if ($entry == 'index.asp') exit();
		
		if ( IsImage($entry) ) $images[] = array( filemtime( $DIR . "/" . $entry ), $entry ); else
		if ( is_dir( $DIR . "/" . $entry ) ) $folders[] = array( filemtime( $DIR . "/" . $entry ), $entry ); else $files[] = array( filemtime( $DIR . "/" . $entry ), $entry );
	}

	
	if ( count( $folders ) > 0 ){
		echo "<div id=\"folders\">";
		arsort($folders);
		foreach ( $folders as $fn ){
			echo "<a href=\"?b=$Browse$fn[1]\">$fn[1]</a><br>";
		}

		echo "</div>";
	}

	
	if ( count( $files ) > 0 ){
		echo "<div id=\"files\">";
		arsort($files);
		foreach ( $files as $fn ){
			echo "<a href=\"$Browse$fn[1]\">$fn[1]</a><br>";
		}

		echo "</div>";
	}

	
	if ( count( $images ) > 0 ){
		echo "<div id=\"images\"><div style=\"clear: both;\"></div>";
		arsort($images);
		foreach ( $images as $fn ){
			echo GetImageLink( $Browse . $fn[1], $fn[1] );
		}

		echo "<div style=\"clear: both;\"></div></div>";
	}

	?>
</body>
</html>
<?php
	////// FUNCTIONS /////
	function IsImage( $i ){
		$i = strtolower( $i );
		
		if ( substr_count( $i, ".jpg" ) > 0 ||substr_count( $i, ".jpeg" ) > 0 ||substr_count( $i, ".gif" ) > 0 ||substr_count( $i, ".png" ) > 0 ){
			return true;
		}

		return false;
	}

	

	// -- Function Name : GetImageLink
	// -- Params :  $imgfilename, $img 
	// -- Purpose : 
	function GetImageLink( $imgfilename, $img ){
		global $THUMBNAIL_WIDTH, $THUMBNAIL_HEIGHT;
		return "<div class=\"image\"><a href=\"$imgfilename\"><img src=\"?img=$imgfilename\" width=$THUMBNAIL_WIDTH height=$THUMBNAIL_HEIGHT></a><br>$img</div>";
	}

	

	// -- Function Name : HandleImageOutput
	// -- Params : 
	// -- Purpose : 
	function HandleImageOutput(){
		global $THUMBNAIL_FOLDER, $THUMBNAIL_WIDTH, $THUMBNAIL_HEIGHT;
		$image = $_GET['img'];
		
		if (!$image) return;
		$imagecache = $THUMBNAIL_FOLDER . "/" . md5( $THUMBNAIL_WIDTH . $THUMBNAIL_HEIGHT . $image ) . ".jpg";
		$im = @imagecreatefromjpeg( $imagecache );
		
		if ( $im ){
			header("Content-type: image/jpg");
			imagejpeg($im);
			imagedestroy($im);
			exit();
		}

		$imgsize = getimagesize ( $image );
		switch ($imgsize[2]){
			case 1:
				// GIF
				$im = imagecreatefromgif( $image );
				break;
			case 2:
				// JPG
				$im = imagecreatefromjpeg( $image );
				break;
			case 3:
				// PNG
				$im = imagecreatefrompng( $image );
				break;
			default:
				// UNKNOWM!
				echo "Unknown Image!";
				exit();
		}

		header("Content-type: image/jpg");
		$img_thumb = imagecreatetruecolor( $THUMBNAIL_WIDTH, $THUMBNAIL_HEIGHT );
		$dsth = ($THUMBNAIL_WIDTH / ImageSX($im)) * ImageSY($im);
		imagecopyresampled( $img_thumb, $im, 0,($THUMBNAIL_HEIGHT-$dsth)/2, 0,0, $THUMBNAIL_WIDTH, $dsth, ImageSX($im), ImageSY($im) );
		imagejpeg( $img_thumb );
		// This will fail if you haven't created and chmodded your thumbnails folder
		
		if ( $im && $img_thumb ){
			@imagejpeg( $img_thumb, $imagecache, 95 );
		}

		imagedestroy( $img_thumb );
		imagedestroy( $im );
		exit();
	}
?>
