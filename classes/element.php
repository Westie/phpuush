<?php
/**
 *	Element interface for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


abstract class Element
{
	/**
	 *	Virtual property delegator
	 */
	public function __get($sProperty)
	{
		switch($sProperty)
		{
			case "pDatabase":
			{
				return $this->pDatabase = Database::getInstance();
			}
			
			case "db_table":
			{
				return $this->db_table = strtolower(get_class($this))."s";
			}
		}
		
		return null;
	}
	
	
	/**
	 *	Load an object based on a search predicate.
	 */
	public function load($mValues)
	{
		$aPredicate = array();
		
		$sQuery = "SELECT rowid AS id, * FROM [{$this->db_table}] WHERE ";
		
		if(is_array($mValues))
		{
			$aConditions = array();
			
			foreach($mValues as $sKey => $mValue)
			{
				$aConditions[] = "[{$sKey}] = :{$sKey}";
				$aPredicate[$sKey] = $mValue;
			}
			
			$sQuery .= implode(" AND ", $aConditions);
		}
		elseif(is_numeric($mValues))
		{
			$sQuery .= "[rowid] = :rowid";
			
			$aPredicate = array
			(
				"rowid" => (integer) $mValues,
			);
		}
		else
		{
			return false;
		}
		
		if($this->pDatabase->busyTimeout(2500))
		{
			$aResults = $this->pDatabase->fetch($sQuery." LIMIT 1", $aPredicate);
			
			if($aResults)
			{
				$pTarget = $aResults[0];
				
				foreach($pTarget as $sKey => $mValue)
				{
					$this->{$sKey} = $pTarget->{$sKey};
				}
				
				return $this->id;
			}
		}
		
		return false;
	}
	
	
	/**
	 *	Finds elements of this type.
	 */
	public function find(array $aArguments)
	{
		$aWhere = array();
		$aSort = array();
		
		$iCurrentPage = isset($aArguments["p"]) ? (integer) $aArguments["p"] : 0;
		$iResultsPerPage = isset($aArguments["rpp"]) ? (integer) $aArguments["rpp"] : null;
		
		if(isset($aArguments["params"]))
		{
			foreach($aArguments["params"] as $sParam)
			{
				$aWhere[] = $sParam;
			}
		}
		
		if(isset($aArguments["sort"]))
		{
			foreach($aArguments["sort"] as $sKey => $sParam)
			{
				$aSort[] = "[{$sKey}] {$sParam}";
			}
		}
		
		if(!isset($aArguments["return"]))
		{
			$aArguments["return"] = "objects";
		}
		
		$sClassName = get_class($this);
		
		switch($aArguments["return"])
		{
			case "objects":
			{
				$aReturn = array();
				$sQuery = "SELECT rowid AS id FROM [{$this->db_table}] ";
				
				if(count($aWhere))
				{
					$sQuery .= "WHERE ".implode(" AND ", $aWhere)." ";
				}
				
				if(count($aSort))
				{
					$sQuery .= "ORDER BY ".implode(", ", $aSort)." ";
				}
				
				if($iResultsPerPage)
				{
					$sQuery .= "LIMIT ".($iResultsPerPage * $iCurrentPage).", ".$iResultsPerPage." ";
				}
				
				$aResults = $this->pDatabase->fetch($sQuery);
				
				foreach($aResults as $pResult)
				{
					$pItem = new $sClassName();
					$pItem->load($pResult->id);
					
					$aReturn[] = $pItem;
				}
				
				return $aReturn;
			}
			
			case "count":
			{
				$iReturn = 0;
				$sQuery = "SELECT count(1) AS item_count FROM [{$this->db_table}] ";
				
				if(count($aWhere))
				{
					$sQuery .= "WHERE ".implode(" AND ", $aWhere)." ";
				}
				
				if(count($aSort))
				{
					$sQuery .= "ORDER BY ".implode(", ", $aSort)." ";
				}
				
				$aResults = $this->pDatabase->fetch($sQuery);
				
				return $aResults[0]->item_count;
			}
		}
		
		return null;
	}
}