<?php
/**
 *	Database delegator class for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *	
 *	@version: 1.0-dev
 */


class Database
{
	/**
	 *	Singleton method
	 */
	public static function getInstance()
	{
		static $instance = null;
		
		if(!isset($instance))
			$instance = self::getDatabase();
		
		return $instance;
	}
	
	
	/**
	 *	Retrieves the correct instance of the database based on the configuration.
	 */
	public static function getDatabase()
	{
		return null;
	}
}