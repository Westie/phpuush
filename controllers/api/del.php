<?php
/**
 *	Deletion controller for phpuush
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
 *	 - URL: /api/hist
 *
 *	 - Request:
 *		+ k = apikey
 *		+ i = file identifier - on puush.me, is base10 of file hash
 *		+ z = poop (what the...)
 *
 *	 - Response (history, success):
 *		> 0
 *		> {id},{YYYY-MM-DD HH:MM:SS},{http://pointer/url},{filename.jpg},{views},{unknown}
 *
 *	 - Response (failure):
 *		> -1
 */