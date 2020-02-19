<?php

namespace mipotech\yii2rest\actions;

use yii\base\Model;

class UpdateAction extends \yii\rest\UpdateAction
{
    use FileUploadTrait;

    /**
     * @inheritdoc
     */
    public function run($id)
    {
        $res = parent::run($id);
        if ($res instanceof Model) {
            $this->handleFileUploads($res);
        }
        return $res;
    }
}
