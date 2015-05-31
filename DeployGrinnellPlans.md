## Your very own Plans ##
This page describes how to set up your own PC or server to host an instance of Plans. You can do this to give yourself a testing platform if you're doing development work, or if you wanted to use Plans software somewhere new, you can start your own server to host it (although you might want to take additional security precautions besides those described in this guide).

_Disclaimer_: At the moment, this is far from a comprehensive guide. It's simply what worked for me, hopefully with enough details that you can fill in any missing parts. So don't take it as gospel, and don't blame me if your computer becomes sentient and flees captivity.

Other Notes: I've written this based on my experience setting things up on a UNIX-like system. These instructions have been tested on Gentoo Linux and Mac OS X. If other platforms do or do not work, please let us know!
I imagine it's comparable on other platforms, but I've never tried, so I don't really know. And if you do have things you would like to add to this page, please do! That's what wikis are for!

## Get the files ##
You'll need a copy of Plans code, which you can check out from SVN from this group (just click on the Source tab). Note that you don't necessarily need the whole repository; you can check out the trunk for roughly the current Plans code, or you can check out the xhtml branch for the most recent development that Ian has been doing (not guaranteed to completely work just yet).

You'll also need a MySQL database dump. To download the basic database dump, see the [sample dump instructions and description](SampleDatabaseDescription.md). We'll use this later.

## Install software ##
I can't provide all the details of setting everything up, but with a little looking you can find nice instructions for most of these programs. You'll need MySQL, PHP, and some software to serve http requests to your computer (I used lighttpd, apache is also popular).

Our PHP code requires PHP5, the PDO MySQL Driver, mcrypt, and xdiff.

## Set up Plans configuration ##
You'll need to define a few variables used by Plans so that it can connect to MySQL and encrypt the user sessions. No passwords are stored in source control.  First, copy Configuration.php.tmpl to Configuration.php

```
cp Configuration.php.tmpl Configuration.php
```

Next, open it up and look for the values being set to '' with need to be edited.  For example:

```
define('TZ', 'America/Chicago');
define('MYSQL_DB', 'plansdb');
define('MYSQL_USER', 'plans');
define('MYSQL_PASS', 'foobar');
define('MYSQL_HOST', 'localhost');

define('DB_URI', 'mysql://plans:foobar@localhost/plansdb');

define('COOKIE_DOMAIN', ''); 
define('COOKIE_PAYLOAD', 'pe');
define('SESSION_ENCRYPTION_KEY', 'SECRETENCRYPTIONKEY!!!!');
```

## Set up PHP configuration ##

Your default settings for PHP will probably work fine, with three small exceptions:

  1. The current code requires mcrypt, a PHP extension to encrypt session-data per user. You can work around this dependency by appending the following code snippet to Configuration.php.
```
// Stubs for mcrypt
define('MCRYPT_RAND', 0);
function mcrypt_generic_init($a, $b, $c) { return 0; }
function mcrypt_generic_deinit($a) { return 0; }

function mcrypt_module_open($a, $b, $c, $d) { return 0; }
function mcrypt_module_close($a) { return 0; }

function mcrypt_create_iv($a, $b) { return 0; }
function mcrypt_enc_get_iv_size($a) { return 0; }

function mcrypt_generic($a, $b) { return $b; }
function mdecrypt_generic($a, $b) { return $b; }
```
> > You should **be careful**: do not commit this code to the repository.
  1. You'll need to enable "register globals".
> > If this option is not available, you can use the following code snippet to emulate its behavior. To use it, place the code at the top of Plans.php.
```
// Emulate register_globals on
if (!ini_get('register_globals')) {
    $superglobals = array($_SERVER, $_ENV,
        $_FILES, $_COOKIE, $_POST, $_GET);
    if (isset($_SESSION)) {
        array_unshift($superglobals, $_SESSION);
    }
    foreach ($superglobals as $superglobal) {
        extract($superglobal, EXTR_SKIP);
    }
}
```
> > You should **be careful**: do not commit this code to the repository.
  1. You'll need to enable short open tags.
> > You can do this either in your php.ini file, or with:
```
ini_set('short_open_tag', true);
```
> > You should **be careful**: do not commit this code to the repository.

Set up your webserver of choice to accept incoming http requests, and make sure it's pointing to the folder where you have all the Plans code (you'll want it to be able to find index.php).

If you get errors about mcrypt functions, check phpinfo() to make sure you have the mcrypt module loaded.

If you get errors about a Doctrine MySQL adaptor missing make sure you have PHP's PDO module installed AND the MySQL adaptor for it.  If you are using OS X you will need to either download the PHP source and compile the modules necessary or switch away from the stock PHP/Apache installation and use a 3rd party version.  Here is a helpful link which discusses one way of compiling a specific php module for OS X, but depending on your architecture may require modification:  http://gidden.net/tom/2008/06/30/mysql-and-pdo-on-os-x-leopard-intel/

Finally, let's set up a MySQL database for plans to use.

### Creating a plans account ###
This sets up privileges for a plans account in mysql (running Plans through the root account could be dangerous). We use "plansdb" for the database name and "plans" for the username, but you may change these if you like. Instead of "foobar", use whatever password you prefer (remember, this is a separate password from the machine account).
```
mysql -u root -p
<type password, or hit Enter if you haven't set a password>
CREATE DATABASE plansdb;
GRANT all ON plansdb.* TO plans@localhost IDENTIFIED BY "foobar";
quit

```

### Setting up the database ###
If you downloaded the sample database dump, unpack it with:
```
gunzip grinnellplans_xhtml_sample_dbdump.sql.gz
```
Now, let's import this into MYSQL:
```
mysql -u plans -p plansdb < grinnellplans_sample_db.sql
```
And finally we need to make sure the DB is up to date with the latest and greatest configuration:
```
cd your_plans_dir; php db/migrate.php
```
If it throws an exception that says "Already at version # X", don't worry about it. If it throws a different exception, worry.

## GeoIP ##
The Geo IP library is included with plans, but the location database is not.  In the geo/ folder there is a file called INSTALL which lists where to download the database.  Once downloaded, it needs to be uncompressed and renamed so plans can find and use it.

```
wget http://www.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
gunzip GeoLiteCity.dat.gz
mv GeoLiteCity.dat geo/geo.dat
```

## Done! ##
If everything's worked, you should be all set. Make sure your web server is running, fire up a web browser and point it towards the server you've just set up (for example, if it's your PC, you might try http://127.0.0.1/ or http://127.0.0.1/plans or something like that, depending on how you've configured it).
If you run into problems, feel free to email the devel list, hosted as the GrinnellPlans-Development [list](http://groups.google.com/group/grinnellplans-development)

If would like to see more information added to any section of this guide, please edit this page (you will need to be a developer in this group) or leave a comment in the box below.