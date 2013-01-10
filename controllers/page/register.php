<?php
/**
 *	Registration controller for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


/**
 *	If there's no POST data sent, we'll display to everyone a nice and somewhat
 *	ugly form.
 */
if(!count($_POST))
{
	$pFunctions->template("register-form");
}
else
{
	if(isset($_POST["email"]) && $_POST["email"])
	{
		$sEmailAddress = (string) $_POST["email"];
		$sPassword = sha1($_POST["password"]);
		
		$aPersonalInformation = array
		(
			"email_address" => $sEmailAddress,
			"password" => $sPassword,
			"api_key" => sha1($_POST["email"].$_POST["password"].uniqid()),
		);
		
		$pDatabase->insert("users", $aPersonalInformation);
		
		$pFunctions->template("register-success");
	}
	else
	{
		$pFunctions->template("register-failure");
	}
}