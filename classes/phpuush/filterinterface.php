<?php
/**
 *	The filter interface makes sure that filters have what they're supposed to have
 *	in order to work properly.
 *	
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *	
 *	@version: 1.0-dev
 */


interface FilterInterface
{
	/**
	 *	Called to test if this is the filter that we want to use.
	 */
	public function test($filter, $file);
	
	
	/**
	 *	Called to return a new output for this file, based on changes done
	 *	by this filter.
	 */
	public function evaluate($file);
}