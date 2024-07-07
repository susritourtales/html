<?php

return [
    'hybridauth' => [
        'callback' => 'https://www.sttandapp.com/twistt/executive/auth',
        'providers' => [
            'Google' => [
                'enabled' => true,
                'keys' => [
                    'id' => '62543003514-987amr82r72o7bbh95qauqtuvs80l9t8.apps.googleusercontent.com',
                    'secret' => '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',
                ],
            ],
            'Facebook' => [
                'enabled' => true,
                'keys' => [
                    'id' => '333332756165062',
                    'secret' => 'ad78e3d30abeafec9faa1e2569fbd177',
                ],
            ],
            'Apple' => [
                'enabled' => true,
                'keys' => [
                    'id' => 'com.susritourtales.twistt', // Your Service ID
                    'team_id' => '8KCS3654D2',      // Your Team ID
                    'key_id' => 'R43YS75FGX',        // Your Key ID
                    'key_file' => '/Applications/XAMPP/xamppfiles/htdocs/html/storage/keys/AuthKey_R43YS75FGX.p8' // Path to your downloaded .p8 file
                ],
                'scope' => 'name email', // Requested scopes
            ],
            /* 'Facebook' => [
                'enabled' => true,
                'keys' => [
                    'id' => '2252878885051271',
                    'secret' => '46ffce6a79ee89a247bd7d615df341f7',
                ],
            ], */
        ],
    ],
    'enbhybridauth' => [
        'callback' => 'https://www.sttandapp.com/twistt/enabler/auth',
        'providers' => [
            'Google' => [
                'enabled' => true,
                'keys' => [
                    'id' => '62543003514-987amr82r72o7bbh95qauqtuvs80l9t8.apps.googleusercontent.com',
                    'secret' => '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',
                ],
            ],
            'Facebook' => [
                'enabled' => true,
                'keys' => [
                    'id' => '333332756165062',
                    'secret' => 'ad78e3d30abeafec9faa1e2569fbd177',
                ],
            ],
            'Apple' => [
                'enabled' => true,
                'keys' => [
                    'id' => 'com.susritourtales.twistt', // Your Service ID
                    'team_id' => '8KCS3654D2',      // Your Team ID
                    'key_id' => 'R43YS75FGX',        // Your Key ID
                    'key_file' => '/Applications/XAMPP/xamppfiles/htdocs/html/storage/keys/AuthKey_R43YS75FGX.p8' // Path to your downloaded .p8 file
                ],
                'scope' => 'name email', // Requested scopes
            ],
        ],
    ],
];
