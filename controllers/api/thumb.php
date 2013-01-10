<?php
/**
 *	Deletion controller for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


/**
 *	Specification:
 *	--------------
 *	
 *	 - URL: /api/thumb
 *
 *	 - Request:
 *		+ k = apikey
 *		+ i = file identifier - on puush.me, is base10 of file hash
 *
 *	 - Response (success):
 *		image, resized
 *
 *	 - Response (failure):
 *		> -1
 */


$pFunctions->requireRequest("k", "i"); 


/**
 *	Prepare our objects first...
 */
$pUser = new User();
$pUpload = new Upload();

if(!$pUser->loadAPIKey($_REQUEST["k"]))
{
	throw new Exception("Invalid API key.");
}

if(!$pUpload->load($_REQUEST["i"]))
{
	throw new Exception("Invalid upload identifier.");
}

if($pUpload->users_id != $pUser->id)
{
	throw new Exception("The requesting API key does not have permission to use this call.");
}


/**
 *	Now, we just resize it.
 */
if(substr($pUpload->mime_type, 0, 6) != "image/")
{
	throw new Exception("Invalid MIME type - not an image.");
}

$sCache = $aGlobalConfiguration["files"]["upload"]."/cache/".$pUpload->file_hash.".".pathinfo($pUpload->file_name, PATHINFO_EXTENSION);

if(!is_dir($aGlobalConfiguration["files"]["upload"]."/cache/"))
{
	mkdir($aGlobalConfiguration["files"]["upload"]."/cache/");
}

header("Cache-Control: public");
header("Content-Type: {$pUpload->mime_type}");
header("Content-Transfer-Encoding: binary");
header("Content-Disposition: inline; filename='{$pUpload->file_name}'");


/**
 *	If the image exists, we'll just read it from the cache.
 */
if(file_exists($sCache))
{
	header("Content-MD5: ".md5_file($sCache));
	header("Last-Modified: ".date("r", filemtime($sCache)));
	
	$rResource = fopen($sCache, "r");
	$sContents = "";
	
	while(($sContents = fread($rResource, 1024)))
	{
		echo $sContents;
		flush();
	}
	
	fclose($rResource);	
	return;
}


/**
 *	If not, I guess we have to create it. Create our canvas!
 */
$rImage = null;

list($iSourceWidth, $iSourceHeight, $iSourceFileType) = getimagesize($pUpload->local_path);
list($iImageWidth, $iImageHeight) = getimagesize($pUpload->local_path);

switch($iSourceFileType)
{
	case IMAGETYPE_GIF:
	{
		$rImage = imagecreatefromgif($pUpload->local_path);
		
		$rTransparency = imagecolorallocate($rImage, 255, 255, 255);
		imagecolortransparent($rImage, $rTransparency);
		
		break;
	}
	
	case IMAGETYPE_JPEG:
	{
		$rImage = imagecreatefromjpeg($pUpload->local_path);
		break;
	}
	
	case IMAGETYPE_PNG:
	{
		$rImage = imagecreatefrompng($pUpload->local_path);
		break;
	}
	
	default:
	{
		throw new Exception("Resizing of this image isn't supported.");
	}
}


/**
 *	Then, we assign some boundaries. This is the max boundaries of our thumbnail.
 */
$aThumbBoundaries = array
(
	"width" => 100,
	"height" => 100,
);

if($iSourceWidth > $aThumbBoundaries["width"] || $iSourceHeight > $aThumbBoundaries["height"])
{
	$iScaler = 1;
	
	if($iSourceWidth > $iSourceHeight)
	{
		$iScaler = $aThumbBoundaries["width"] / $iSourceWidth;
	}
	else
	{
		$iScaler = $aThumbBoundaries["height"] / $iSourceHeight;
	}
	
	$iSourceWidth *= $iScaler;
	$iSourceHeight *= $iScaler;
}

$rDestination = imagecreatetruecolor($iSourceWidth, $iSourceHeight);

imagecopyresized($rDestination, $rImage, 0, 0, 0, 0, $iSourceWidth, $iSourceHeight, $iImageWidth, $iImageHeight);


/**
 *	And now we save it in the cache.
 */
switch($iSourceFileType)
{
	case IMAGETYPE_GIF:
	{
		imagegif($rDestination, $sCache);
		break;
	}
	
	case IMAGETYPE_JPEG:
	{
		imagejpeg($rDestination, $sCache);
		break;
	}
	
	case IMAGETYPE_PNG:
	{
		imagepng($rDestination, $sCache);
		break;
	}
}

if(file_exists($sCache))
{
	header("Content-MD5: ".md5_file($sCache));
	header("Last-Modified: ".date("r", filemtime($sCache)));
	
	$rResource = fopen($sCache, "r");
	$sContents = "";
	
	while(($sContents = fread($rResource, 1024)))
	{
		echo $sContents;
		flush();
	}
	
	return;
}

throw new Exception("Something bad happened - cache file didn't save.");