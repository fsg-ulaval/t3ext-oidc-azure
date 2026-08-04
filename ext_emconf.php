<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'OpenID Connect Authentication - Azure provider',
    'description' => 'This extension replace the generic OAuth provider factory of causal/oidc by the Azure version.',
    'category' => 'services',
    'author' => 'Cyril Janody',
    'author_company' => '',
    'author_email' => 'cyril.janody@fsg.ulaval.ca',
    'state' => 'stable',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'oidc' => '6.0.0-6.99.99',
            'php' => '8.2.0-8.4.99',
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
