`txmp.docs / documentation`

# Installation and updating

## Building from source

~~Put `changelog` `data.init` `internal_config` `lib` `static` `.htaccess` `LICENSE` `index.php` into an archive. This is the full or release archive.~~

~~Put `changelog` `lib` `static` `index.php` into an archive. This is the update archive.~~

Use `build-util/build-zips.bat` if possible.

## Installation

### Setup

Unzip the full or release archive into somewhere under your document root.

Open `.htaccess`, replace `RewriteBase` with your installation path.

```plain
Allow from All

php_value display_errors On
RewriteBase /apps/music/
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?_lnk=$1&%{QUERY_STRING} [L]

```

The path should contain a trailing slash. For instance, if your intended URL is `https://example.com:2043/apps/music/`, you use `/apps/music/`.

If you intend to add force HTTPS, add it before txmp's rewrite.

Then, open `internal_config/config_basic.php`, and edit BASIC_URL.

```php
define("BASIC_URL",str_replace('__hostname__',$_SERVER['HTTP_HOST'],"http://__hostname__/apps/music/"));
```

Note that you do not need to add port number manually.

Replace APP_PREFIX with a unique string representing your txmp instance. It should only include alpha, numbers and `-`, and should not start with a number.

```php
define("APP_PREFIX",'nesic-archive');
```

You could find PASS_KEY below. Replace it with a 64-digit random string. DO NOT use the one shown below.

```php
define("PASS_KEY",'7DBDfxBOiqzoagOIOcqniiDd6RXNyMFlZrCvW4c7uh1jRfO6Ga5tBj5PPoFdLj4P');
```

Once PASS_KEY is set, **you must not change it**.

### Data init

Rename `data.init` to `data`.

Then immediately access your installation in your browser. Click login, use username `root` and password `123`. After logging in, **change the password**, and optionally the username.

### Customization

After making sure that your installation work, you may want to do some customizations. See:

- [Configuration and customization](./config.md)

## Updating

Before updating, read the changes carefully, and be especially careful with items with the BREAKING tag. They may contain required additional steps when updating.

Generally, simply **unzip the update archive into your installation folder** and overwrite existing files.

Warning: Direct downgrading in this way can cause serious issues.
