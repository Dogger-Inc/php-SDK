<?php

namespace Dogger\DoggerSdk;

function init($config) {
    $defaultConfig = [
        'url' => 'http://127.0.0.1:8000',
    ];
    new Dogger(array_merge($defaultConfig, $config));
}

