<?php
/**
 *	Database class for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


class Database extends SQLite3
{
	/**
	 *	Database query count. Useful for statistics?
	 */
	public $count = 0;
	
	
	/**
	 *	Singleton method
	 */
	public static function getInstance()
	{
		static $pInstance = null;
		
		if($pInstance === null)
		{
			global $aGlobalConfiguration;
			
			$pInstance = new Database($aGlobalConfiguration["databases"]["sql"]);
		}
		
		return $pInstance;
	}
	
	
	/**
	 *	Customised query method, with built in prepared statements if necessary.
	 */
	public function query($sQuery, array $aArguments = null)
	{
		++$this->count;

		if($aArguments === null)
		{
			return parent::query($sQuery);
		}
		
		$pStatement = parent::prepare($sQuery);
		
		$iFragmentPosition = 0;
		$iArgumentType = null;

		foreach($aArguments as $sKey => &$mArgument)
		{
			if(is_string($mArgument))
			{
				$iArgumentType = SQLITE3_TEXT;
			}
			elseif(is_integer($mArgument))
			{
				$iArgumentType = SQLITE3_INTEGER;
			}
			elseif(is_bool($mArgument))
			{
				$mArgument = (integer) $mArgument;
				$iArgumentType = SQLITE3_INTEGER;
			}
			elseif(is_float($mArgument))
			{
				$iArgumentType = SQLITE3_FLOAT;
			}
			elseif(is_object($mArgument))
			{
				$pReflection = new ReflectionClass($mArgument);
				
				if($pReflection->hasMethod("__toString"))
				{
					$mArgument = (string) $mArgument;
					$iArgumentType = SQLITE3_TEXT;
				}
				else
				{
					$mArgument = serialize($mArgument);
					$iArgumentType = SQLITE3_BLOB;
				}
			}
			else
			{
				$mArgument = serialize($mArgument);
				$iArgumentType = SQLITE3_BLOB;
			}
			
			if(is_numeric($sKey))
			{
				$pStatement->bindParam(++$iFragmentPosition, $mArgument, $iArgumentType);
			}
			else
			{
				$pStatement->bindParam((string) $sKey, $mArgument, $iArgumentType);
			}
		}
		
		return $pStatement->execute();
	}
	
	
	/**
	 *	Customised exec to allow support for prepared stuff.
	 */
	public function exec($sQuery, array $aArguments = null)
	{
		if($aArguments === null)
		{
			return parent::exec($sQuery);
		}
		
		$pResult = $this->query($sQuery, $aArguments);
		
		if($pResult)
		{
			$pResult->finalize();
		}
		
		return $pResult !== false;
	}
	
	
	/**
	 *	Method to fetch fields from database, as objects.
	 */
	public function fetch($sQuery, array $aArguments = null)
	{
		$aReturn = array();
		$pResult = $this->query($sQuery, $aArguments);
		
		if($pResult)
		{
			while(($aResult = $pResult->fetchArray(SQLITE3_ASSOC)))
			{
				$aReturn[] = (object) $aResult;
			}
			
			$pResult->finalize();
			
			return $aReturn;
		}
		
		return null;
	}
	
	
	/**
	 *	Shorthand quote method
	 */
	public function quote($sString)
	{
		return parent::escapeString($sString);
	}
	
	
	/**
	 *	Customised insert method
	 */
	public function insert($sTable, $aValues = array())
	{
		$aKeys = array_keys($aValues);
		$aFragments = array();
		
		$iFragmentCount = count($aKeys);
		
		foreach($aValues as $sKey => $sValue)
		{
			$aFragments[] = ":{$sKey}";
		}
		
		if($this->busyTimeout(2500))
		{
			$this->exec("INSERT INTO [{$sTable}] ([".implode("], [", $aKeys)."]) VALUES (".implode(", ", $aFragments).")", $aValues);
			return $this->lastInsertRowID();
		}
		
		throw new Exception("Database error.");
	}
}