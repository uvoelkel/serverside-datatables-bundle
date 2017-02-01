<?php

namespace Voelkel\DataTablesBundle\Table;

class Localization
{
    /** @var string */
    static private $locale;

    /** @var array */
    static private $data = [];

    static $defaults = [
        'de' => [
            'true' => 'Ja',
            'false' => 'Nein',
            'datetime' => 'd.m.Y H:i:s',
        ],
        'en' => [
            'true' => 'true',
            'false' => 'false',
            'datetime' => 'Y-m-d H:i:s',
        ],
    ];

    static public function setConfig(array $config)
    {
        self::$locale = $config['locale'];

        $defaults = isset(self::$defaults[self::$locale]) ? self::$defaults[self::$locale] : self::$defaults['en'];
        self::$data = array_merge($defaults, isset($config['data']) ? $config['data'] : []);
    }

    static public function get($key)
    {
        return self::$data[$key];
    }
}
