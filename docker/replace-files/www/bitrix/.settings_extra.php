<?
return [
    'cache' => [
        "value" => [
            'sid' => '$_SERVER["DOCUMENT_ROOT"]."#01"',
            'type' => 'memcache',
            'memcache' => [
                'host' => '172.16.237.5',
                'port' => '11211'
            ],
        ],
        'readonly' => false,
    ]
];
