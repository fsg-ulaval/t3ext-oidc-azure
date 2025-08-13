<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'OpenID Connect Authentication - Azure provider',
    'description' => 'This extension replace the generic OAuth provider factory of causal/oidc by the Azure version.',
    'category' => 'services',
    'author' => 'Cyril Janody',
    'author_company' => '',
    'author_email' => 'cyril.janody@fsg.ulaval.ca',
    'state' => 'stable',
    'version' => '1.0.0-dev',
    'constraints' => [
        'depends' => [
            'oidc' => '4.0.0-4.99.99',
            'php' => '8.3.0-8.4.99',
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
