<?
/*****************************************************************
* Awesome Image Upload class developed by MineS Chan (mineschan@gmail.com);
* Twitter,Forrst,LinkedIn,Github:mineschan
*
* Version 1.2.1  7/27/2011

-----------Way to Use AwesomeImageUpload class-----------

No.1

  $uploader = new AwesomeImageUpload("/path/to/image");
	$uploader->upload("frmFileFieldName");

No.2
	$uploader = new AwesomeImageUpload();
	$uploader->setSource("frmFileFieldName")
	$uploader->setDestination("/path/to/image");
	$uploader->upload();
	
No.3
	$uploader = new AwesomeImageUpload();
	$uploader->upload("frmFileFieldName","/path/to/image");

-----------Ways to resize image-----------

	$uploader->setSize($width,$height); //fit in size
	
	or
	
	$uploader->setWidth($width); //max width
	$uploader->setHeight($width); //max height

-----------Way to rename image-----------
	
	$uploader->setName("nameOfImage");
	
	//randomString and name
	$uploader->randStr(10,$rand); //$rand returned
	
	$filename = "something".$rand;
	$uploader->setName($filename); //filename = "somethingFGDASD#@23S"
	
	
----------Way to handle multi select upload field

html:
<input type="file" name="photo[]" multiple />

php:

$uploader = new AwesomeImageUpload();
$uploader->setDestination("/path/to/image");
$uploader->setSource("photo");

for($i = 0; $i<sizeof($_FILES["photo"]["name"]);$i++){
	$uploader->setIndex($i);
	$uploader->upload()
}


***************************************************************************
***************************************************************************
********************Change Log*********************************************

v1.2.1 7/27/2011
- fix the error that upload image with capital extension will return wrong filename sometimes
- fix the error that sometime getFullName() doesn't return the extension name

v1.2 5/23/2011 
- add support for uploading multiple image in one file input (applying index for file field)


*/


class AwesomeImageUpload{

	var $maxWidth,$maxHeight,$outputWidth,$outputHeight;
	var $src;
	var $dest;
	var $filename;
	var $tmpFilePath;
	var $ext;
	var $index = NULL;
	
	
	function __construct($dest){
		$this->setDestination($dest);
	}
	
	function setSource($name){
		$this->success = false;
		$this->src = $name;
		$this->setExtension(pathinfo($_FILES[$src]["name"], PATHINFO_EXTENSION));			
	}
	
	function setIndex($index){
		$this->index = $index;
	}
	
	function setDestination($path){
		$this->success = false;
		if($path!=NULL){
			$tmpPath = str_replace($_SERVER["DOCUMENT_ROOT"],"",$path);
			$tmpPath = $_SERVER['DOCUMENT_ROOT'].$tmpPath."/";
			$this->dest = str_replace("//","/",$tmpPath);
		}
	}
	
	function setName($name){
		$checkExt = pathinfo($name, PATHINFO_EXTENSION);
		
		if($checkExt!=NULL)
			$name = str_replace(".".$checkExt,"",$name);
	
		$this->filename = $name;
	}
	
	function setWidth($maxWidth){
		$this->maxWidth = $maxWidth;
	}

	function setHeight($maxHeight){
		$this->maxHeight = $maxHeight;
	}
	
	function setExtension($ext){
	
		switch(strtolower($ext)){
			case "jpg":
			case "jpeg":
				$this->ext = "jpg";
				break;
			case "png":
				$this->ext = "png";
				break;
			case "gif":
				$this->ext = "gif";
				break;
			default:
				$this->ext = $ext;
				break;
		}
	
	}
	
	function setSize($maxWidth,$maxHeight){
		$this->maxWidth = $maxWidth;
		$this->maxHeight = $maxHeight;
	}

		
	function getFullName(){
		return $this->filename.".".$this->ext;
	}
	
	
	/*
	function getFullName(){
		$file = ($this->index===NULL)? $_FILES[$this->src]["name"]: $_FILES[$this->src]["name"][$this->index];
		
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		return $this->filename.".".strtolower($ext);
	}
	*/
	
	
	/**
	 * Function for calculate the value of resize using the maxWidth and maxHeight if provided.
	 * 
	 * @return array [0]=width [1]=height [2]=fileExtension
	 */
	function calculateSize(){
	
		if (file_exists($this->tmpFilePath)) { 
   			$srcSize   = getimagesize($this->tmpFilePath);
   			$destW = $this->maxWidth;
   			$destH = $this->maxHeight;
  		 	$srcExtension = $srcSize[2];
  		 	  			
  			$newW = $srcSize[0];
  			$newH = $srcSize[1];

			while($newW > $destW || $newH > $destH){
			//okay! need to scale down
   				//width over destW
   				if(isset($destW) && $newW>$destW){
   					
   					$ratioW = $destW /$newW;
   				
   					$newH = $newH * $ratioW;
   					$newW = $destW;
   				}
   				//height over destH
   				if(isset($destH) && $newH > $destH){
   				   	$ratioH = $destH /$newH;
   				 	$newW = $newW * $ratioH;
   				 	$newH = $destH;
   				}
   				
   				if(!(isset($destW) && isset($destH)))
   					break;
			}
  		}
  		$newSize = array();
  		$newSize[]=$newW;
  		$newSize[]=$newH;
  		$newSize[]=$srcExtension;

  		return $newSize;
	}



	function imagesResize() { 
		
		$size = $this->calculateSize();
		$this->outputWidth = $size[0];
		$this->outputHeight = $size[1];
		$src = $this->tmpFilePath;
		$dest = $this->tmpFilePath;
		
		if (file_exists($this->tmpFilePath)) { 
		
	 		$destImage = imagecreatetruecolor($size[0],$size[1]);  
	  		switch ($size[2]) { 
	   			case 1: $srcImage = imagecreatefromgif($src); break; 
	   			case 2: $srcImage = imagecreatefromjpeg($src); break; 
	   			case 3: $srcImage = imagecreatefrompng($src); break; 
	 		}
	
		  	imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $size[0], $size[1], imagesx($srcImage), imagesy($srcImage)); 
		
		  	switch ($size[2]) { 
		   		case 1: imagegif($destImage,$dest); break; 
		   		case 2: imagejpeg($destImage,$dest,90); break;
		   		case 3: imagepng($destImage,$dest); break;
		
		  		imagedestroy($destImage);
		  	}
  		} 
	}
	
	function randStr( $length, &$string )
	{
		// RANDOM TO CHOOSE 1. UPPERCASE / 2. LOWERCASE / 3. NUMBER
		$which = mt_rand( 1, 3 );
		switch( $which )
		{
		case 1:
			$min = 65; $max = 90;
			break;
		case 2:
			$min = 97; $max = 122;
			break;
		case 3:
			$min = 48; $max = 57;
			break;
		}

		// GET RANDOM NUMBER FOR CHARACTER
		$rand = mt_rand( $min, $max );

		// CONVERT ASCII TO CHAR AND APPEND TO STRING
		$string .= chr( $rand );

		// IF EQUAL TO OR GREATER THAN LENGTH THEN RETURN
		if( strlen($string) >= $length ) return;

		// RECURSION - BUILD THE STRING
		$this->randStr( $length, $string );
	}
	
	
		
	function upload($src,$dest){
		$this->success = false;
		
		$src  = (isset($this->src))? $this->src : $src;
		if(!isset($this->dest) && $dest != NULL) $this->setDestination($dest);
		$tmp_name = ($this->index===NULL)? $_FILES[$src]["tmp_name"]:$_FILES[$src]["tmp_name"][$this->index];		
		$name = ($this->index===NULL)? $_FILES[$src]["name"]:$_FILES[$src]["name"][$this->index];

		
		if($tmp_name){
			
			if($this->ext == "")
			{
				$detectExt = pathinfo($name, PATHINFO_EXTENSION);
				$this->setExtension($detectExt);
			}
			
			if(isset($this->filename)){
				$fileFullPath = $this->dest.$this->filename.".".$this->ext;
				$fileFullPath = str_replace("..",".",$fileFullPath);
			}else{
				$this->filename = str_replace(".".$this->ext,"",$name);
				$fileFullPath = $this->dest.$name;
			}
			
			if(copy($tmp_name, $fileFullPath))		
			{	
				$this->success = true;
				$this->tmpFilePath = $fileFullPath;
				if(isset($this->maxWidth)||isset($this->maxHeight)){
					$this->imagesResize();
					return true;
				}else{
   					$srcSize   = getimagesize($this->tmpFilePath);
					$this->outputWidth = $size[0];
					$this->outputHeight = $size[1];				
				}
			}
		}
		
		return false;
	}
	
	function debug(){
		
		echo "<br><b>Awesome Image Upload Debug Log:</b><br>";
		echo "Field name:$this->src with index $this->index<br>";
		echo "Destination:$this->dest<br>";
		echo "Filename:".$this->getFullName()."<br>";
		echo "Final Path:".$this->tmpFilePath."<br>";
		echo "Width:$this->outputWidth px<br>";
		echo "Height:$this->outputHeight px<br>";
		
		if($this->success)
			echo "<span style='color:green'><b>Upload Complete!</b></span><br>";
		else
			echo "<span style='color:red'><b>Upload Fail!</b></span><br>";
		
		
		echo "<br><br>";
	}





}
?>
