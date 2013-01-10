<?php
/**
 *	phpuush service for PHP
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


date_default_timezone_set("Europe/London");


/**
 *	Include our classes
 */
include "classes/functions.php";
include "classes/database.php";
include "classes/element.php";
include "classes/user.php";
include "classes/upload.php";
include "classes/geshi/geshi.php";


/**
 *	Include our configuration files
 */
include "configuration.php";


/**
 *	Do some defining stuff
 */
$pFunctions = Functions::getInstance();
$pDatabase = Database::getInstance();

$_SEO = $pFunctions->translateRequestURI();


/**
 *	Delegate to our controllers
 */
try
{	
	if(isset($_SEO[0]))
	{
		if($_SEO[0] == "api")
		{
			if(file_exists("controllers/api/{$_SEO[1]}.php"))
			{
				require "controllers/api/{$_SEO[1]}.php";
			}
			else
			{
				throw new Exception("API method does not exist.");
			}
		}
		elseif($_SEO[0] == "dl")
		{
			return "85";
		}
		elseif($_SEO[0] == "page")
		{
			if(file_exists("controllers/page/{$_SEO[1]}.php"))
			{
				require "controllers/page/{$_SEO[1]}.php";
			}
			else
			{
				throw new Exception("API method does not exist.");
			}
		}
		else
		{
			require "controllers/file-handler.php";
		}
	}
	else
	{
		echo "This is a phpuush endpoint.";
	}
}
catch(Exception $pException)
{
	echo "-1";
}


$pDatabase->close();
exit;
