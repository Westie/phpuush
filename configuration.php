<?php
/**
 *	Configuration for phpuush
 *	
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *	
 *	@version: 1.0-dev
 */


$configuration = array
(
	# defining database options
	"database" => array
	(
		"type" => "sqlite",
		
		"settings" => array
		(
			"db" => "databases/phpuush.db",
		),
	),
	
	# where are things stored?
	"uploads" => "uploads/",
	
	# what domain should we use to access this service?
	"endpoint" => "http://phpuush.local",
);