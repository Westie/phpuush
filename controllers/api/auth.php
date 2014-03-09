<?php
/**
 *	Authentication controller for phpuush
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
 *	 - URL: /api/auth
 *	
 *	 - Request:
 *		+ e = email address
 *		+ p = password
 *		+ k = api key
 *		+ z = poop (what the...)
 *
 *	 - Response (authenticated, success):
 *		> {premium},{apikey},[expire],{size-sum}
 *
 *	 - Response (failure):
 *		> -1
 */