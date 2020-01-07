Basic Controllers
=================

## Your first controller

All REST controllers should use the trait `mipotech\yii2rest\RestControllerTrait`. This ensures a consistent, predictable behavior for all REST endpoints.

Let's go ahead and create a test controller:

1. Create the file `@app/api/controllers/TestController.php`
2. Paste in the following basic template:

```php
<?php

namespace app\api\controllers;

use Yii;
use yii\rest\Controller;
use mipotech\yii2rest\RestControllerTrait;

class TestController extends Controller
{
    use RestControllerTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Skip authorization for this first basic action
        $this->authExceptActions[] = 'index';
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        /* Append the controller namespace dynamically so that we can
         * write the actions() function in the parent class
         * without each major version having to redefine the entire array
         */
        $controllerNamespace = $this->module->controllerNamespace;
        return [
            "index" => "{$controllerNamespace}\\{$this->id}\\IndexAction",
        ];
    }

    /**
     * @inheritdoc
     */
    protected function generatePermissionsQuery(string $action, $model, array $params)
    {
        return null;
    }
}
```
3. Now create the action `@api/controllers/test/IndexAction.php`:

```php
<?php

namespace app\api\controllers\test;

use mipotech\yii2rest\actions\BaseAction;

class IndexAction extends BaseAction
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        return [
            'hello' => 'world',
        ];
    }
}
```

4. At this point, you can test your first controller and action as follows:

```bash
curl -H "Accept: application/json" -v http://localhost/v1/test
```
