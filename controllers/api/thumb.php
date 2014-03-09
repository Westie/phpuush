<?php
/**
 *	Thumbnail controller for phpuush
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
 *	 - URL: /api/thumb
 *
 *	 - Request:
 *		+ k = apikey
 *		+ i = file identifier - on puush.me, is base10 of file hash
 *
 *	 - Response (success):
 *		image, resized
 *
 *	 - Response (failure):
 *		> -1
 */