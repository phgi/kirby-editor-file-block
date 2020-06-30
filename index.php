<?php

Kirby::plugin('phgi/file-block', [
    'snippets' => [
        'editor/file' => __DIR__ . '/snippets/file.php'
    ],
    'translations' => [
        'en'    => @include_once __DIR__ . '/i18n/en.php',
        'de'    => @include_once __DIR__ . '/i18n/de.php',
    ]
]);
