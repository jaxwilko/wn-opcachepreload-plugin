<?php

require __DIR__ . '/classes/Preloader.php';

$options = getopt('dhve', ['help', 'dry', 'verbose', 'errors']);

if (isset($options['h']) || isset($options['help'])) {
    echo <<<TEXT
OPTIONS
-h, --help      show this screen
-d, --dry       perform a dry run
-v, --verbose   output info
-e, --errors    output errors
TEXT;
    exit(0);
}

$baseDir = dirname(__DIR__, 3);

\JaxWilko\OpcachePreload\Classes\Preloader::instance()
    ->setBasePath($baseDir)
    ->setPaths([
        'bootstrap',
        'config',
        'modules',
        'plugins',
        'themes',
        'vendor/winter',
        'vendor/laravel'
    ])
    ->ignore([
        '/.*?\/plugins\/\w*\/\w*\/updates\/.*$/i',
        '/.*?\/plugins\/\w*\/\w*\/vendor\/.*$/i',
        '/.*?\/plugins\/jaxwilko\/opcachepreload\/.*$/i',
        '/.*?\/database\/migrations\/.*$/i',
        '/.*?\/tests\/.*$/',
        '/.*?\/lang\/.*$/',
        '/.*?\/storage\/cms\/.*$/',
        '/.*?\/storage\/framework\/views\/.*$/'
    ])
    ->options([
        'dry' => isset($options['d']) || isset($options['dry']),
        'verbose' => isset($options['v']) || isset($options['verbose']),
        'errors' => isset($options['e']) || isset($options['errors']),
    ])
    ->run();
