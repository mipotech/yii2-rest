<?php

namespace mipotech\yii2rest;

class BaseModule extends \yii\base\Module
{
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
