# phpuush

So, you're probably confused as to what this is. Well, it's a proxy for puush. The developers for puush decided to be all stupid and refused to implement useless features like SFTP and FTP.

So, my absolutely brilliant friend [@jannispl](https://github.com/jannispl) decided to write a new [proxy](https://github.com/jannispl/puushproxy) for puush in node.js. This, as far as we know, was the first alternative implementation of puush.

Almost half a decade on, I thought I would improve it to make it better.

## How do I migrate to the new version?

### Preamble

Firstly, [install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos).

There are three files and folders you'll need to find:

- `configuration.php`
- `databases/phpuush.db` (or whatever your database file is)
- `uploads/` (or whatever your upload folder is)

You can either copy or move - but make sure that you have a backup of this stuff first.

Now you figure out where those files and folders are, you'll need to create a separate installation of phpuush.

If you are lazy like me, and want everything in one folder so when you're developing whilst drunk you can simply run `git pull` and things are magically fixed, just clone the repo using git.

If you are security conscious (like some of my friends) then you can download master as a compressed archive, extract it to somewhere, move certain folders around then edit the `APP_DIR` constant within `index.php` but you shouldn't even need to do this, if you properly re-configure everything.

**Make backups!**

### Moving files to new homes

- `configuration.php` **must** be relocated to `app/configuration.php`. You may notice that the format of the configuration has changed - there is no need to change this as the configuration itself is backwards compatible.

- `databases/phpuush.db` can be located anywhere that PHP has write access, however for the purposes of this example, move it to `app/databases/phpuush-demo.db`. You need to update the `database.sql` property with the absolute path to this file within the config.

- `uploads/` can be located anywhere that PHP has write access, however for the purposes of this example, move it to `app/uploads`. You need to update the `files.upload` property with the absolute path to this folder within the config.

Then, you'll need to run `composer install --no-dev`

Please make sure that your phpuush.db file is not accessible to the outside world. The best thing to do is to make it so that *every* request to that folder is handled by `index.php`. If you can download your `phpuush.db` or `composer.json` through your browser you've configured it incorrectly.

## Bonus: Environment variables

You can completely skip the idea of a boring old configuration file and use environment variables instead! Create `.env` or `app/.env` (or even assigned via your web service) and assign the following:

```
PHPUUSH_DATABASE=/dsn/or/path-to-sqlite.db
PHPUUSH_FILES_DOMAIN=http://your-domain
PHPUUSH_FILES_UPLOAD=/your/upload/path
```

As long it's accessible by your web service, it'll work.
