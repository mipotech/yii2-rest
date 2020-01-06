<?php

namespace mipotech\yii2rest\actions;

class NestedUpdateAction extends BaseNestedAction
{
    /**
     * @inheritdoc
     */
    protected function functionSuffix(): string
    {
        return 'Update';
    }
}
