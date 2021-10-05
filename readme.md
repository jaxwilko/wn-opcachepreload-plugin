# Winter Opcache Preload

Preloading allows you to read php files into memory at server startup which helps boost performance. The tradeoff for 
this is that you will need to restart your webserver when you make a change to one of the cached php files.

[Official preloading php docs](https://www.php.net/manual/en/opcache.preloading.php).

Please note that preloading is not supported on Windows.

### Installation

Install the plugin:

```bash
composer require jaxwilko/wn-opcachepreload-plugin
```

Configure opcache settings in your `php.ini`:

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0 
opcache.revalidate_freq=0
opcache.preload=/path/to/winter/plugins/jaxwilko/opcachepreload/preload.php
opcache.preload_user=apache
```

> Change `/path/to/winter` to whatever is right for your setup

> Change `apache` in `preload_user` to whatever the right user is for you

### Usage

Reboot your webserver (nginx / php-fpm / apache).

### Misc

The preload script supports some options for testing.

```
OPTIONS
-h, --help      show this screen
-d, --dry       perform a dry run
-v, --verbose   output info
-e, --errors    output errors
```

### Disclaimer

This plugin works well for me in my production setups but that does not necessarily mean it will work for you, please 
test thoroughly before using in production.
