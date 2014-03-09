<?php
/**
 *	phpuush service for PHP
 *	re-written because all is better this way
 *	
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *	
 *	@version: 1.0-dev
 */


# first, set the autoloader - saves us from loading files that we
# don't really need to fulfil a request.
if(!function_exists("phpuush_autoload_helper"))
{
	function phpuush_autoload_helper($class)
	{
		if(file_exists("classes/phpuush/".$class.".php"))
			require "classes/phpuush/".$class.".php";
	}
	
	spl_autoload_register("phpuush_autoload_helper");
}


# now, we'll get our configuration.
# the format of the configuration has changed since the previous version
# so please look at this carefully!
require "configuration.php";


# and now to bootstrap some things...
$functions = Functions::getInstance();
$database = Database::getInstance();

if(!isset($_SEO))
	$_SEO = $functions->translateRequestURI();


# now for the magic wheel of fortune.
try
{
	if(!empty($_SEO[0]))
	{
		if($_SEO[0] == "api")
		{
			if(file_exists("controllers/api/".$_SEO[1].".php"))
				require "controllers/api/".$_SEO[1].".php";
			else
				throw new Exception("API method does not exist.");
		}
		elseif($_SEO[0] == "dl")
		{
			return "85";
		}
		elseif($_SEO[0] == "page")
		{
			if(file_exists("controllers/page/".$_SEO[1].".php"))
				require "controllers/page/".$_SEO[1].".php";
			else
				throw new Exception("API method does not exist.");
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
catch(Exception $exception)
{
	echo "-1";
}

# close database
exit;