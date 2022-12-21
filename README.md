# Token Manager

A token manager for jwt tokens

## Installation


The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require dmstr/yii2-token-manager
```

or add

```
"dmstr/yii2-token-manager": "dev-master"
```

to the require section of your `composer.json` file.

## Configuration

Add the component to your config

```php
use dmstr\tokenManager\components\TokenManager;

return [
    'components' => [
        'tokenManager' => [
            'class' => TokenManager::class
        ]
    ]
];
```

## Usage

Once the extension is installed and configurated, simply use it in your code by:

For more infos about Yii2 and JWT check out [lcobucci/jwt](https://github.com/lcobucci/jwt)
```php
use dmstr\tokenManager\exceptions\LoadTokenException;

$token = ...; // valid Jwt token

Yii::$app->tokenManager->setToken($token);

try {
    $roles = Yii::$app->tokenManager->getRoles();
} catch (LoadTokenException $exception) {
 // ...
}
```
