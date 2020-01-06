<?php

namespace mipotech\yii2rest\actions;

class NestedDeleteAction extends BaseNestedAction
{
    /**
     * @inheritdoc
     */
    protected function functionSuffix(): string
    {
        return 'Delete';
    }
}
