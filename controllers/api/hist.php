<?php
/**
 *	History controller for phpuush
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
 *	 - URL: /api/hist
 *
 *	 - Request:
 *		+ k = apikey
 *
 *	 - Response (history, success):
 *		> 0
 *		> {id},{YYYY-MM-DD HH:MM:SS},{http://pointer/url},{filename.jpg},{views},{unknown}
 *
 *	 - Response (failure):
 *		> -1
 */


$pFunctions->requireRequest("k");


$pUser = new User();
$pUpload = new Upload();

if(!$pUser->loadAPIKey($_REQUEST["k"]))
{
	throw new Exception("Invalid API key.");
}


/**
 *	We'll just browse our uploaded files, casually display things.
 */
$aArguments = array
(
	"params" => array
	(
		"users_id = '{$pUser->id}'",
		"is_deleted = '0'",
	),
	
	"sort" => array
	(
		"timestamp" => "DESC",
	),
	
	"p" => 0,
	"rpp" => 10,
);

$aUploads = $pUpload->find($aArguments);

echo "0\r\n";

foreach($aUploads as $pItem)
{
	echo "{$pItem->id},".date("Y-m-d H:i:s", $pItem->timestamp).",{$pItem->web_url},{$pItem->file_name},{$pItem->views},1\r\n";
}