<?php

namespace mipotech\yii2rest\actions;

class NestedViewAction extends BaseNestedAction
{
    /**
     * @inheritdoc
     */
    protected function functionSuffix(): string
    {
        return 'View';
    }
}
