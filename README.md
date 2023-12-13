
![Logo](https://raw.githubusercontent.com/Dogger-Inc/.github/main/assets/logo_full.png)


# Dogger PHP SDK

Package to automatically monitor your app and send not handled errors to your dogger dashboard.


## Features

- Unhandled errors monitoring
- Tasks performances


## Installation

Package need to be installed via composer with this command:

```bash
composer require dogger/dogger-sdk
```
    
## Usage/Examples

```php
<?php

require_once 'vendor/autoload.php';

\Dogger\DoggerSdk\init([
    'key' => 'DOGGER-PROJECT-KEY',
    'env' => 'dev' // or: prod | dev | custom-env
]);

```

