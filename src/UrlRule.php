<?php

namespace mipotech\yii2rest;

class UrlRule extends \yii\rest\UrlRule
{
    public $pluralize = false;
    public $extraPatterns = [
        'GET,HEAD {id}/<nestedAction:[\w\-]+>' => 'nested-index',
        'POST {id}/<nestedAction:[\w\-]+>' => 'nested-create',
        'GET,HEAD {id}/<nestedAction:[\w\-]+>/<nestedId:[0-9]+>' => 'nested-view',
        'PUT {id}/<nestedAction:[\w\-]+>/<nestedId:[0-9]+>' => 'nested-update',
        'PUT {id}/<nestedAction:[\w\-]+>/<nestedId:[0-9]+>' => 'nested-delete',
    ];
}
