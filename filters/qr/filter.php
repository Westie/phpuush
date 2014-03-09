<?php
/**
 *	QR filter for phpuush
 *	
 *	@author Blake <blake@totalru.in>
 *	@author Westie <westie@typefish.co.uk>
 *	
 *	@version: 1.0-dev
 */


class FilterQR implements FilterInterface
{
	/**
	 *	Called to test if this is the filter that we want to use.
	 */
	public function test($filter, $file)
	{
		return $filter == "qr";
	}
	
	
	/**
	 *	Called to return a new output for this file, based on changes done
	 *	by this filter.
	 */
	public function evaluate($file)
	{
		
	}
}