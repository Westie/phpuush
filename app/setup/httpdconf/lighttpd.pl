# config for lighttpd
# give your thanks to Blake!

$HTTP["host"] =~ "your.domain.here" {
	server.document-root = "/path/to/phpuush"
	url.rewrite-once = (".*" => "index.php")
}

$HTTP["host"] == "puush.me" {
	server.document-root = "/path/to/phpuush"
	url.rewrite-once = (".*" => "index.php")
}

$HTTP["host"] == "puu.sh" {
	server.document-root = "/path/to/phpuush"
	url.rewrite-once = (".*" => "index.php")
}

$HTTP["host"] == "phpuushed" {
	server.document-root = "/path/to/phpuush"
	url.rewrite-once = (".*" => "index.php")
}