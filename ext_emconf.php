<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Salted user password hashes',
    'description' => 'Uses a password hashing framework for storing passwords. Integrates into the system extension "felogin". Use SSL or rsaauth to secure datatransfer! Please read the manual first!',
    'category' => 'services',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Marcus Krause, Steffen Ritter',
    'author_email' => 'marcus#exp2009@t3sec.info',
    'author_company' => 'TYPO3 Security Team',
    'version' => '8.3.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.3.0-8.3.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'rsaauth' => ''
        ],
    ],
];
