<?php

namespace mipotech\yii2rest\actions;

class NestedIndexAction extends BaseNestedAction
{
    /**
     * @inheritdoc
     */
    protected function functionSuffix(): string
    {
        return 'Index';
    }
}
