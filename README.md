# phpuush

So, you're probably confused as to what this is. Well, it's a proxy for puush. The developers for puush decided to be all stupid and refused to implement useless features like SFTP and FTP.

So, my absolutely brilliant friend [jannispl](https://github.com/jannispl) decided to write a new [proxy](https://github.com/jannispl/puushproxy) for puush in node.js. This, as far as we know, was the first alternative implementation of puush.

However, I wanted to experiment in the joys that is hiphop-php. So, I made this! It was designed on Apache, tested on nginx and compiled with hiphop-php.

I think I'm one of the only people in the world apart from Facebook using hiphop-php in a product environment, as it may seem.

# Oh. Okay. What do I do next?

Well, many things. You could pick your nose and eat it - or you could follow some installation cues and get the damned thing working.

## Create the database

At the moment, only SQLite is supported, however it's my intention to add mySQL and/or another (no)SQL to make those hipsters all happy and shit.

Go into the `databases/` directory and **copy** `phpuush.db-dist` to something like `phpuush.db` or something.

## More configuration

Here's a sample `configuration.php` file:

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

You'll need to edit only two things, one being the SQLite DB file (look up!) and the other being the domain. The domain is obviously the one that you *have* to change.

## Setting up webservers

Oh, so you actually want this thing to be live, eh? Well, what you need to do is set up whatever webserver you have to either accept connections on another port that is not `80` or listen for `puush.me` - whatever floats your boat. There are a million ways to set it up.

I'll add examples when I can be bothered.

## Setting up your client on Windows - r85

You just edit `%AppData%\puush\puush.ini` to resemble something like this:

    ProxyServer = someproxy
    ProxyPort = someport

And then restart puush.

## Setting up your client on OS X - r62

Only attempt this if you're experienced with how to hexedit binaries and such. You will need to create a SSL certificate (self signed is fine) though.

### Choosing the domain to point to

Thankfully I managed to negate the need for a SSL certificate. This has just made the task from extremely hard to relatively easy.

First thing you have to do is add this to the `/private/etc/hosts` file:

    <address> phpuushed

Then, you replace your puush binary with the one in the repo. You can find this in
`setup/binaries/OS X/puush` - you need to replace the binary in
`puush.app/Contents/MacOS/puush` with the one on this repo.

The only change is that I've changed `https://puush.me/` to `http://phpuushed/`.

## Using the client

You will need to register by going to:

`http://someproxy:someport/page/register`

Don't want to allow anyone else to register? Just rename `controllers/page/register.php` or whatever you want.

Know of any improvements? Hit me!
