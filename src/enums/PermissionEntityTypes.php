<?php

namespace mipotech\yii2rest\enums;

use Yii;
use yii2mod\enum\helpers\BaseEnum;

class PermissionEntityTypes extends BaseEnum
{
    const CONTROLLER = 'controller';
    const MODEL = 'model';
    const PAGE = 'page';
    const REPORT = 'report';

    /**
     * @inheritdoc
     */
    public static function getList()
    {
        return [
            self::CONTROLLER => Yii::t('permissions', 'Controller'),
            self::MODEL => Yii::t('permissions', 'Model'),
            self::PAGE => Yii::t('permissions', 'Page'),
            self::REPORT => Yii::t('permissions', 'Report'),
        ];
    }
}
