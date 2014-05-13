<?php
/**
   author: seek@youku.com
   last modified: 2007-03-21
   Usage:
	Image::convert("src.jpg","dest.jpg",300,400,Image::MODE_CUT,Image::SAVE_JPG);
 */
class Image{
	const SAVE_PNG = 0;
	const SAVE_JPG = 1;
	const SAVE_GIF = 2;

	const MODE_CUT = 0;
	const MODE_SCALE = 1;

	const TYPE_GIF = 1;
	const TYPE_JPG = 2;
	const TYPE_PNG = 3;

	const SAVE_QUALITY = 100;

	static public function getImageType($fileName=null){
		$sizeInfo = getImageSize($fileName);
		return $sizeInfo[2];
	}

	static public function saveFile($image=null,$destFile=null,$saveType=self::SAVE_JPG){
		switch( $saveType ) {
			case self::SAVE_GIF: 
				return @imageGif($image, $destFile);
			case self::SAVE_JPG:
				return @imageJpeg($image, $destFile, self::SAVE_QUALITY);
			case self::SAVE_PNG:
				return @imagePng($image, $destFile);
				
			default:
				return false;
		}
	}

	static public function convert($srcFile=null,$destFile=null,$width=0,$height=0,$mode=self::MODE_CUT){
		if(false === file_exists($srcFile) ){
			return false;
		}

		preg_match( '/\.([^\.]+)$/', $destFile, $matches );
		switch( strtolower($matches[1]) )
		{
			case 'jpg':
			case 'jpeg':
				$saveType = self::SAVE_JPG;
				break;
			case 'gif':
				$saveType = self::SAVE_GIF;
				break;
			case 'png':
				$saveType = self::SAVE_PNG;
				break;
			default:
				$saveType = self::SAVE_JPG;
		}

		$type = self::getImageType($srcFile);
		$srcImage = null;
		switch ($type){
			case self::TYPE_GIF:
				$srcImage = imageCreateFromGif($srcFile);
				break;
			case self::TYPE_JPG:
				$srcImage = imageCreateFromJpeg($srcFile);
				break;
			case self::TYPE_PNG:
				$srcImage = imageCreateFromPng($srcFile);
				break;
			default:
				return false;
		}

		$srcWidth = imageSX($srcImage);
		$srcHeight = imageSY($srcImage);

		if($width==0 && $height==0){
			$width = $srcWidth;
			$height = $srcHeight;
			$mode = self::MODE_SCALE;
		}else if($width>0 & $height==0){
			$useWidth = true;
			$mode = self::MODE_SCALE;
			if ( $srcWidth <= $width ) {
				return self::saveFile($srcImage, $destFile, $saveType);
			}
		}else if($width==0 && $height>0){
			$mode = self::MODE_SCALE;
		}
		
		if( $mode == self::MODE_SCALE){
			if($width>0 & $height>0){
				$useWidth = (($srcWidth*$height) > ($srcHeight*$width)) ? true:false;
			}
			if( isset($useWidth) && $useWidth==true ){
				$height = ($srcHeight*$width)/$srcWidth;
			}else{
				$width = ($srcWidth*$height)/$srcHeight;
			}
		}

		$destImage = imageCreateTrueColor($width, $height);
		if( $mode==self::MODE_CUT ){

			$useWidth = (($srcWidth*$height) > ($srcHeight*$width)) ? false : true; 
			if( $useWidth==true ){
				$tempWidth = $width;
				$tempHeight = ($srcHeight*$tempWidth)/$srcWidth;
			}else{
				$tempHeight = $height;
				$tempWidth = ($srcWidth*$tempHeight)/$srcHeight;
			}

			$tempImage = imageCreateTrueColor( $tempWidth, $tempHeight);
			$srcImage = imageCopyResampled( $tempImage, $srcImage,0,0,0,0,$tempWidth,$tempHeight,$srcWidth,$srcHeight);
			imageDestroy($srcImage);
			$srcImage = $tempImage;
			$srcWidth = $width;
			$srcHeight = $srcWidth*$width/$srcHeight;
		}
		
		if( $mode == self::MODE_SCALE ){
			imageCopyResampled( $destImage, $srcImage, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight );
		}else{
			imageCopyResampled( $destImage, $srcImage, 0, 0, 0, 0, $width, $height, $width, $height );
		}
		
		@imageDestroy($srcImage);
		return self::saveFile($destImage,$destFile,$saveType);
	}
}
?>
