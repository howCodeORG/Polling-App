<p align="center">
<img src="https://s3.amazonaws.com/f.cl.ly/items/3Q2830043H1Y1c1F1K2D/directus-logo-stacked.png" alt="Directus Logo"/>
</p>

# Directus SDK for PHP

[![Build Status](https://img.shields.io/travis/directus/directus-sdk-php.svg?style=flat-square)](https://travis-ci.org/directus/directus-sdk-php)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/directus/directus-sdk-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/directus/directus-sdk-php)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/directus/directus-sdk-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/directus/directus-sdk-php/?branch=master)

For PHP driven applications, use this SDK to more easily communicate with your Directus managed database.

## Requirements

PHP version 5.4 or greater.

## Install

### Via Composer

You can install the SDK using [Composer](http://getcomposer.org).

Add `directus/sdk` to composer `require` list.

```json
{
  "require": {
    "directus/sdk": "^1.0"
  }
}
```

As we are trying to get zend-db to [support spaces and dashes in columns](https://github.com/zendframework/zend-db/issues/208) officially, the SDK require to use a forked zend-db that supports it.

Add these lines into your `composer.json`:

```
"minimum-stability": "dev",
"repositories": [
  {
    "type": "git",
    "url": "https://github.com/wellingguzman/zend-db"
  }
]
```

The result will look like this:

```json
{
  "require": {
    "directus/sdk": "^1.0"
  },
  "minimum-stability": "dev",
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/wellingguzman/zend-db"
    }
  ]
}
```

Then run `composer install`.

To use the SDK you have to include the [composer autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading)

```php
require_once 'vendor/autoload.php';
```

## Usage

### Database connection

``` php
require 'vendor/autoload.php';

$config = [
    'database' => [
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '123',
        'database' => 'directus_db',
        // Optional
        // 'port' => 3306,
        // 'charset' => 'utf8'
    ],
    'filesystem' => [
        'root' => '/path/to/directus/storage/uploads'
    ]
];

$client = \Directus\SDK\ClientFactory::create($config);
$articles = $client->getItems('articles');

foreach($articles as $article) {
    echo $article->title . '<br>';
}
```

### Directus Hosted

You can sign up for a Directus Hosted account at https://directus.io.

```php
require 'vendor/autoload.php';

$client = \Directus\SDK\ClientFactory::create('user-token', [
    // the sub-domain in your instance url
    'instance_key' => 'user--instance',
    'version' => '1' // Optional - default 1
]);

$articles = $client->getItems('articles');
foreach($articles as $article) {
    echo $article->title . '<br>';
}
```

### Your own server

```php
require 'vendor/autoload.php';

$client = \Directus\SDK\ClientFactory::create('user-token', [
    // Directus API Path without its version
    'base_url' => 'http://directus.local',
    'version' => '1' // Optional - default 1
]);

$articles = $client->getItems('articles');
foreach($articles as $article) {
    echo $article->title . '<br>';
}
```
