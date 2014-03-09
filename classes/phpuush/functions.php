<?php
/**
 *	Utilities class for PHP
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *	
 *	@version: 1.0-dev
 */


class Functions
{
	/**
	 *	Singleton method
	 */
	public static function getInstance()
	{
		static $instance = null;
		
		if(!isset($instance))
			$instance = new self();
		
		return $instance;
	}
	
	
	/**
	 *	Method to translate the request URI into something useful.
	 */
	public function translateRequestURI()
	{
		$request = explode("/", parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
		
		if(!$request)
			$request = array();
		
		return array_values(array_filter($request));
	}
}