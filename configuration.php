<?php
/**
 *	Configuration for phpuush
 *
 *	@author Blake <blake@totalru.in>
 *	@author PwnFlakes <pwnflak.es>
 *	@author Westie <westie@typefish.co.uk>
 *
 *	@version: 0.1
 */


$aGlobalConfiguration = array
(
	"databases" => array
	(
		"sql" => __DIR__."/databases/phpuush.db",
		"mime" => __DIR__."/databases/mime.types",
	),
	
	"files" => array
	(
		"upload" => __DIR__."/uploads/",
		"domain" => "http://your.domain.tld",
	),
);