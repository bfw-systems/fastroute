<?php

return [
    'routes' => [
        '/' => [
            'target' => 'index.php'
        ],
        '/login' => [
            'target' => 'login.php',
            'httpMethod' => ['GET', 'POST']
        ],
        '/article-{id:\d+}' => [
            'target' => 'article.php',
            'get' => ['action' => 'read']
        ]
    ]
];
