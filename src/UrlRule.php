<?php

namespace mipotech\yii2rest;

class UrlRule extends \yii\rest\UrlRule
{
    public $pluralize = false;
    public $extraPatterns = [
        'GET,HEAD {id}/<nestedAction:[\w\-]+>' => 'nested-index',
        'POST {id}/<nestedAction:[\w\-]+>' => 'nested-create',
        'GET,HEAD {id}/<nestedAction:[\w\-]+>/<nestedId:[\w]+>' => 'nested-view',
        'PUT {id}/<nestedAction:[\w\-]+>/<nestedId:[\w]+>' => 'nested-update',
        'DELETE {id}/<nestedAction:[\w\-]+>/<nestedId:[\w]+>' => 'nested-delete',
        '{id}/<nestedAction:[\w\-]+>' => 'options',
        '{id}/<nestedAction:[\w\-]+>/<nestedId:[\w]+>' => 'options',
    ];
}
