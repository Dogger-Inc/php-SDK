<?php

namespace Dogger\DoggerSdk;

function init($config) {
    $defaultConfig = [
        'url' => 'https://dogger.cloud',
    ];
    return new Dogger(array_merge($defaultConfig, $config));
}

