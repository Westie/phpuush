<?php
/**
 *	Upload object for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


class Upload extends Element
{
	/**
	 *	Virtual property delegator.
	 */
	public function __get($sProperty)
	{
		global
			$pFunctions,
			$aGlobalConfiguration;
		
		switch($sProperty)
		{
			case "web_url":
			{
				return $this->web_url = $aGlobalConfiguration["files"]["domain"]."/".$this->alias;
			}
			
			case "local_path":
			{
				return $this->local_path = $aGlobalConfiguration["files"]["upload"]."/".$this->file_location;
			}
			
			default:
			{
				return parent::__get($sProperty);
			}
		}
		
		return null;
	}
	
	
	/**
	 *	Load the element by means of the base62-hash.
	 */
	public function loadHash($sHash)
	{
		global
			$pFunctions;
		
		return $this->load($pFunctions->convertToBase10($sHash));
	}
	
	
	/**
	 *	Load the element by means of the alias.
	 */
	public function loadAlias($sHash)
	{
		$aConditions = array
		(
			"alias" => $sHash,
		);
		
		return $this->load($aConditions);
	}
	
	
	/**
	 *	Retrieves a file resource of this file.
	 */
	public function getFile()
	{
		return fopen($this->local_path, "r");
	}
	
	
	/**
	 *	Increment the amount of views this has had.
	 */
	public function incrementViews()
	{
		if($this->pDatabase->busyTimeout(250))
		{
			return $this->pDatabase->exec("UPDATE [{$this->db_table}] SET [views] = [views] + 1 WHERE [rowid] = ?", array($this->id));
		}
	}
	
	
	/**
	 *	Generates a new, unused alias.
	 */
	public function generateAlias()
	{
		$aDictionary = preg_split("//", "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", -1, PREG_SPLIT_NO_EMPTY);
		
		$iIndex = 0;
		$iDistance = 4;
		
		$sString = "";
		
		while($iIndex != $iDistance)
		{
			$sString .= $aDictionary[mt_rand(0, count($aDictionary) - 1)];
			
			++$iIndex;
		}
		
		$aResults = $this->pDatabase->fetch("SELECT count(1) AS item_count FROM [uploads] WHERE [alias] = ?", array($sString));
		
		if($aResults[0]->item_count)
		{
			return $this->generateAlias();
		}
		
		return $sString;
	}
}