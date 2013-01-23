<?php
/**
 *	File handler for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


/**
 *	We'll just load our file.
 */
$pUpload = new Upload();
$pUpload->loadAlias($_SEO[0]);

if(!isset($pUpload->id) || $pUpload->is_deleted)
{
	header("Location: /");
	return;
}


$aHeaders = $pFunctions->getHeaders();

if(isset($aHeaders["If-Modified-Since"]))
{
	$iCachedModificationDate = strtotime($aHeaders["If-Modified-Since"]);

	if($iCachedModificationDate == filemtime($pUpload->local_path))
	{
		$pUpload->incrementViews();
		
		header("Not Modified: Use browser cache", true, 304);
		return;
	}
}


/**
 *	Return things to the server...
 */
if((isset($_GET["height"]) || isset($_GET["width"])) && substr($pUpload->mime_type, 0, 6) != "image/")
{
	return;
}
elseif(isset($_SEO[1]))
{
	$sCacheItem = $aGlobalConfiguration["files"]["upload"]."/cache/geshi-".strtolower($_SEO[1])."-".$pUpload->file_hash.".html";	
	$sRender = null;	
	
	if(!file_exists($sCacheItem))
	{
		$sContents = file_get_contents($pUpload->local_path);
		
		$pGeshi = new Geshi($sContents, $_SEO[1]);
		$sRender = $pGeshi->parse_code();
		
		file_put_contents($sCacheItem, $sRender);
	}
	else
	{
		$sRender = file_get_contents($sCacheItem);
	}
	
	header("Cache-Control: public");
	header("Last-Modified: ".date("r", filemtime($sCacheItem)));
	header("Content-Length: ".filesize($sCacheItem));
	header("Content-Type: text/html");
	header("Content-Transfer-Encoding: binary");
	header("Content-MD5: ".md5_file($sCacheItem));
	header("Content-Disposition: inline; filename=".$pFunctions->quote($pUpload->file_name));
	
	echo $sRender;
}
else
{
	header("Cache-Control: public");
	header("Last-Modified: ".date("r", filemtime($pUpload->local_path)));
	header("Content-Length: {$pUpload->file_size}");
	header("Content-Type: {$pUpload->mime_type}");
	header("Content-Transfer-Encoding: binary");
	header("Content-MD5: {$pUpload->file_hash}");
	header("Content-Disposition: inline; filename=".$pFunctions->quote($pUpload->file_name));
	
	$rPointer = $pUpload->getFile();
	$sContents = "";
	
	while(($sContents = fread($rPointer, 1024)))
	{
		echo $sContents;
		flush();
	}
	
	fclose($rPointer);
}

$pUpload->incrementViews();
return;
