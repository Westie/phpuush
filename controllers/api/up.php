<?php
/**
 *	Upload controller for phpuush
 *	
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *	
 *	@version: 1.0-dev
 */


/**
 *	Specification:
 *	--------------
 *	
 *	 - URL: /api/up
 *
 *	 - Request:
 *		+ k = apikey
 *		+ c = hash of uploaded file
 *		+ z = poop (what the...)
 *		+ f = file
 *
 *	 - Response (upload, success):
 *		> 0,{http://pointer/url},{id},{size}
 *
 *	 - Response (failure):
 *		> -1
 */