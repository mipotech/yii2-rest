Setup
=====

## config/web.php

You will need to make the following modifications to `config/web.php` in order to enable basic module functionality:

1. In `components`, make sure that the JSON parser is enabled, as follows:

```php
'request' => [
    ...
    'parsers' => [
        'application/json' => 'yii\web\JsonParser',
    ],
    ...
]
```

2. Configure the URL manager to use the custom REST UrlRule:

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        [
            'class' => 'mipotech\yii2rest\UrlRule',
            'controller' => [
                ...
                'v1/leads',
                'v1/products',
                'v1/orders',
                ...
            ],
        ],
    ],
],
```

3. In the `modules` section, add the first module in the following manner:

```php
'modules' => [
    ...
     'v1' => [
        'class' => 'app\modules\v1\Module',
    ],
    ...
]
```

## config/rest.php

Create a new file in the `config` directory named `rest.php`. Paste in the following template:

```php
<?php

return [
    /**
     * Custom callback for resolving the current users's role ID
     * @param yii\web\User $user
     * @return string|int|null
     */
    'roleIdCallback' => function($user) {
        // This is just an example of one possible implementation
        return Yii::$app->user->identity->role->id;
    }
];
```
## Create the first module

1. Create the `@app/modules` directory if it does not yet exist.
2. Create a directory named `v1`
3. Inside the `v1` directory, create a file named `Module.php`
4. Paste in the the following template:

```php
<?php

namespace app\modules\v1;

class Module extends \mipotech\yii2rest\BaseModule
{
    public $controllerNamespace = 'app\modules\v1\controllers';
}
```

## Directory Structure

By convention, the directory `@app/api` will house all of your REST controllers and actions.

To get started, create the following directories:

```bash
$ mkdir api
$ mkdir api/controllers
```

At this point, the configuration in complete. In the next section, we will create the first endpoint.
