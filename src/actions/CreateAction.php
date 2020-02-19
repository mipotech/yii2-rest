<?php

namespace mipotech\yii2rest\actions;

use yii\base\Model;

class CreateAction extends \yii\rest\CreateAction
{
    use FileUploadTrait;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $res = parent::run();
        if ($res instanceof Model) {
            $this->handleFileUploads($res);
        }
        return $res;
    }
}
