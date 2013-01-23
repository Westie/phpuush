<?php
/**
 *	Functions class for phpuush - things that couldn't be fit
 *	anywhere else.
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


class Functions
{
	/**
	 *	Singleton method
	 */
	public static function getInstance()
	{
		static $pInstance = null;
		
		if($pInstance === null)
		{
			$pInstance = new Functions();
		}
		
		return $pInstance;
	}
	
	
	/**
	 *	Convert to something, by means of intermediaries.
	 */
	public function convert($sNumber, $iFrom = 62, $iTo = 10)
	{
		if($iFrom != 10)
		{
			$iResult = $this->convertToBase10($sNumber, $iFrom);
		}
		else
		{
			$iResult = (integer) $sNumber;
		}
		
		if($iTo != 10)
		{
			return $this->convertToBaseN($iResult, $iTo);
		}
		
		return $iResult;
	}
	
	
	/**
	 *	Intermediary methods for converting numerical bases.
	 */
	public function convertToBaseN($iNumber, $iBase = 62)
	{
		$sDictionary = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		
		$iRemainder = $iNumber % $iBase;
		$sResult = $sDictionary[$iRemainder];
		
		$iPointer = floor($iNumber / $iBase);
		
		while($iPointer)
		{
			$iRemainder = $iPointer % $iBase;
			$iPointer = floor($iPointer / $iBase);
			
			$sResult = $sDictionary[$iRemainder].$sResult;
		}
		
		return $sResult;
	}
	
	public function convertToBase10($sHash, $iBase = 62)
	{
		$sDictionary = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		
		$sHash = (string) $sHash;
		$iLimit = strlen($sHash);
		
		$iResult = strpos($sDictionary, $sHash[0]);
		
		for($iPointer = 1; $iPointer < $iLimit; ++$iPointer)
		{
			$iResult = $iBase * $iResult + strpos($sDictionary, $sHash[$iPointer]);
		}
		
		return $iResult;
	}
	
	
	/**
	 *	Dump REQUEST (lazy!), FILES and SERVER information into a file.
	 */
	public function dumpAnalysis($sFileLocation, Exception $pException = null)
	{
		$aAnalysis = array
		(
			"server" => $_SERVER,
			"request" => $_REQUEST,
			"files" => $_FILES,
		);
		
		if($pException instanceof Exception)
		{
			$aAnalysis["exception"] = $pException;
		}
		
		file_put_contents("{$_SERVER["DOCUMENT_ROOT"]}/{$sFileLocation}", print_r($aAnalysis, true));
	}
	
	
	/**
	 *	Method to translate the request URI into something useful.
	 */
	public function translateRequestURI()
	{
		$aRequest = explode("/", parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
		
		$aKeys = array_filter($aRequest);
		return array_values($aKeys);
	}
	
	
	/**
	 *	Display a so-called template! :)
	 */
	public function template($sTemplateName, array $aTemplateArguments = array())
	{
		extract($aTemplateArguments);
		unset($aTemplateArguments);
		
		require "{$_SERVER["DOCUMENT_ROOT"]}/templates/{$sTemplateName}.php";
	}
	
	
	/**
	 *	If the GET request doesn't carry these, throw an error.
	 */
	public function requireRequest()
	{
		foreach(func_get_args() as $sGetItem)
		{
			if(!isset($_REQUEST[$sGetItem]))
			{
				throw new Exception("You need this to run this particular component: \$_REQUEST[{$sGetItem}]");
			}
		}
		
		return true;
	}
	
	
	/**
	 *	Returns the MIME type of a file. Why did PHP have to depreciate a decent function?
	 */
	public function getMimeType($sFileName)
	{
		global $aGlobalConfiguration;
		
		$sFileExtension = substr(strrchr($sFileName, '.'), 1);
		
		if(!$sFileExtension)
		{
			return "application/octet-stream";
		}
		
		$sPattern = "/^([\w\+\-\.\/]+)\s+(\w+\s)*({$sFileExtension}\s)/i";
		$aDictionary = file($aGlobalConfiguration["databases"]["mime"]);
		
		foreach($aDictionary as $sItem)
		{
			if(substr($sItem, 0, 1) == "#")
			{
				continue;
			}
			
			$sItem = rtrim($sItem)." ";
			$aMatches = array();
			
			if(!preg_match($sPattern, $sItem, $aMatches))
			{
				continue;
			}
			
			return $aMatches[1];
		}
		
		return "application/octet-stream";
	}
	
	
	/**
	 *	Returns a list of headers that were supplied to the server.
	 */
	public function getHeaders()
	{
		if(function_exists("apache_request_headers"))
		{
			return apache_request_headers();
		}
		
		$aReturn = array();
		
		foreach($_SERVER as $sKey => $sValue)
		{
			if(substr($sKey, 0, 5) == "HTTP_")
			{
				# this is just ugly.
				$sKey = str_replace("- ", "-", ucwords(str_replace("_", "- ", strtolower(substr($sKey, 5)))));
				$aReturn[$sKey] = $sValue;
			}
		}
		
		return $aReturn;
	}
	
	
	/**
	 *	Quotes a string ready for safe transmission.
	 */
	public function quote($sSource)
	{
		return '"'.str_replace('"', '\"', $sSource).'"';
	}
}