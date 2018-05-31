<?php
/**
 *	Upload controller for phpuush
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
 *	 - URL: /api/up
 *
 *	 - Request:
 *		+ k = apikey
 *		+ c = hash of uploaded file
 *		+ z = poop (what the...)
 *		+ f = file
 *
 *	 - Response (upload, success):
 *		> 0,{http://pointer/url},{id},{size}
 *
 *	 - Response (failure):
 *		> -1
 */


$pFunctions->requireRequest("k", "c", "z");

$pUser = new User();
$pUpload = new Upload();


/**
 *	Does this user exist?
 */
if(!$pUser->loadAPIKey($_REQUEST["k"]))
{
	throw new Exception("Invalid API key.");
}


/**
 *	Now we need to do some funny things involving moving things.
 */
$aFileReference = $_FILES["f"];

$sTargetScope = $pUser->email_address;
$sTargetDirectory = $aGlobalConfiguration["files"]["upload"];
$sFileLocation = "{$sTargetScope}/".uniqid();

$sExtension = pathinfo($aFileReference["name"], PATHINFO_EXTENSION);

if($sExtension)
{
	$sFileLocation .= ".".$sExtension;
}

if(!empty($_REQUEST["c"]))
{
	$sHash = md5_file($aFileReference["tmp_name"]);
	
	if($sHash != $_REQUEST["c"])
	{
		throw new Exception("Invalid hash comparison");
	}
}

if(!is_dir("{$sTargetDirectory}/{$sTargetScope}"))
{
	mkdir("{$sTargetDirectory}/{$sTargetScope}");
}

move_uploaded_file($aFileReference["tmp_name"], "{$sTargetDirectory}/{$sFileLocation}");


/**
 *	Now we've successfully moved things, we need to get all sorts of meta data for it.
 */
$aDatabaseEntry = array
(
	"users_id" => $pUser->id,
	"alias" => $pUpload->generateAlias(),
	"file_name" => $aFileReference["name"],
	"file_location" => $sFileLocation,
	"file_size" => filesize("{$sTargetDirectory}/{$sFileLocation}"),
	"file_hash" => $sHash,
	"mime_type" => $pFunctions->getMimeType("{$sTargetDirectory}/{$sFileLocation}"),
	"timestamp" => time(),
	"ip_address" => $_SERVER["REMOTE_ADDR"],
	"views" => 0,
);

$iIdentifier = $pDatabase->insert("uploads", $aDatabaseEntry);


/**
 *	Now we have our database entry, we'll just re-load it back. If it worked, it shouldn't error.
 *	Hah!
 */
$pUpload->load($iIdentifier);

$aOutput = array
(
	"1",
	$pUpload->web_url,
	$pUpload->id,
	$pUpload->file_size,
);

echo implode(",", $aOutput);
return;
