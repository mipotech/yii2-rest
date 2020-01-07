CRUD Controllers
=================

The ability for powerful, flexible CRUD controllers out of the box is the most critical part of this extension.

We will cover the basic topics here. Some of the more advanced capabilities will be covered in other sections.

## Background

This extension offers a class `mipotech\yii2rest\controllers\BaseCrudController`, which inherits from Yii2's [ActiveContoller](https://www.yiiframework.com/doc/api/2.0/yii-rest-activecontroller). This class uses the functionality of

## Your first controller

Let's assume you have a model called `app\models\Lead`. And let's assume you want to expose basic CRUD functionality for this model.

1. Create a controller `@app/api/controllers/LeadsController.php` (we pluralize the controller names as a matter of convention):

```php
<?php

namespace app\api\controllers;

use Yii;
use mipotech\yii2rest\controllers\BaseCrudController;

class LeadsController extends BaseCrudController
{
    public $modelClass = 'app\models\leads\Lead';
}
```

2. Add the route in `config/web.php` under the `urlManager` section:

```php
'urlManager' => [
    ...
    'rules' => [
        [
            'class' => 'mipotech\yii2rest\UrlRule',
            'controller' => [
                ...
                'v1/leads',
                ...
            ],
        ],
    ],
    ...
],
```

3. At this point, you can test your first controller and action as follows:

#### Index (GET)

```bash
curl -H "Accept: application/json" -v http://localhost/v1/leads
```

#### View (GET)

```bash
curl -H "Accept: application/json" -v http://localhost/v1/leads/1
```

#### Create (POST)

```bash
curl -H "Accept: application/json" -H "Content-Type: application/json" -X POST -d {"source":"foo", "date":"2020-02-02"}' -v  http://localhost/v1/leads
```
#### Update (PUT)

```bash
curl -H "Accept: application/json" -H "Content-Type: application/json" -X PUT -d {"source":"foo", "date":"2020-02-02"}' -v  http://localhost/v1/leads/1
```

#### Delete (DELETE)

```bash
curl -H "Accept: application/json" -X DELETE -d {"source":"foo", "date":"2020-02-02"}' -v  http://localhost/v1/leads/1
```

This is all you need to set up a basic, model-based CRUD controller. In the next sections, we will cover permissions and over advanced CRUD capabilities, including nested actions.