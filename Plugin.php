<?php

namespace JaxWilko\OpcachePreload;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'          => 'Winter Opcache Preload',
            'description'   => 'Winter CMS support for opcache preloading',
            'author'        => 'Jack Wilkinson',
            'icon'          => 'oc-icon-user-secret',
            'homepage'      => 'https://github.com/jaxwilko/wn-opcachepreload-plugin'
        ];
    }
}
