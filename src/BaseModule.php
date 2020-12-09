<?php

namespace mipotech\yii2rest;

use Yii;

class BaseModule extends \yii\base\Module
{
    /**
     * @link https://www.yiiframework.com/doc/api/2.0/yii-filters-cors
     * @var array
     */
    public $corsOptions = [];
    /**
     * @var callable
     */
    public $roleIdCallback = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $configFile = Yii::getAlias('@app/config/rest.php');
        if (file_exists($configFile)) {
            Yii::configure($this, require $configFile);
        }
    }

    /**
     * Override createControllerByID to allow searching for the controller in two different namespaces:
     * 1. First look in the default controller namespace (by default app\api\controllers)
     * 2. If not found, look in the default namespace
     *
     * @inheritdoc
     * @author Chaim Leichman
     */
    public function createControllerByID($id)
    {
        $ret = parent::createControllerByID($id);
        if (!empty($ret)) {
            return $ret;
        } else {
            $this->controllerNamespace = 'app\api\controllers';
            return parent::createControllerByID($id);
        }
    }
}
