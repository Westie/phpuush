<?php
/**
 *	Authentication controller for phpuush
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
 *	 - URL: /api/auth
 *	
 *	 - Request:
 *		+ e = email address
 *		+ p = password
 *		+ k = api key
 *		+ z = poop (what the...)
 *
 *	 - Response (authenticated, success):
 *		> {premium},{apikey},[expire],{size-sum}
 *
 *	 - Response (failure):
 *		> -1
 */


$pFunctions->requireRequest("e");
$pUser = new User();


if(isset($_REQUEST["p"]))
{
	$pUser->loadAuth($_REQUEST["e"], $_REQUEST["p"]);
}
elseif(isset($_REQUEST["k"]))
{
	$pUser->loadAPIKey($_REQUEST["k"]);
}

if($pUser->id && $pUser->email_address == $_REQUEST["e"])
{
	$aOutput = array
	(
		"1",
		$pUser->api_key,
		"",
		$pUser->getTotalUploadedBytes(),
	);
	
	echo implode(",", $aOutput);
	return;
}


throw new Exception("Invalid authentication");