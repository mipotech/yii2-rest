Versioning
=================

## Intro

This package comes with a class named `BaseModule`, which inherits from `yii\base\Module`.
`BaseModule` allows you to easily conform to the best practice of versioning your API.

## Configuration

The first step is to set up your initial version module in the config/web.php. Your config block will look something like this:

```php
'modules' => [
    ...
    'v1' => [
        'class' => 'app\modules\v1\Module',
    ],
    ...
]
```

## Create the module

Next, we need to create the actual module file:

1. Browse to (or create) the `modules` directory.
2. Create a folder named `v1`.
3. Create a file named `Module.php`, and paste the following code:

```php
<?php

namespace app\modules\v1;

class Module extends \mipotech\yii2rest\BaseModule
{
    public $controllerNamespace = 'app\modules\v1\controllers';
}
```


## Inheritance Structure

The default fallback namespace for REST controllers and actions is `app\api\controllers`. That's where the extension will look for your controller/action if Yii2 can't resolve the requested URL to a controller/action that exists under your versioned module (such as "v1").


## Next Steps

At this point, you will now be able to access your REST API endpoint using the following URL structure:

```
http://localhost/v1/user/me
```


## Minor versioning

The accepted convention is to implement versioning via HTTP header and not via URL structure. For example:

```
Accepts-version: 1.1
```
