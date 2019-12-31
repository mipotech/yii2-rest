<?php

namespace mipotech\yii2rest\enums;

use Yii;
use yii2mod\enum\helpers\BaseEnum;

class PermissionActions extends BaseEnum
{
    const CREATE = 'create';
    const DELETE = 'delete';
    const LIST = 'index';
    const VIEW = 'view';
    const UPDATE = 'update';

    /**
     * @inheritdoc
     */
    public static function getList()
    {
        return [
            self::CREATE => Yii::t('permissions', 'Create'),
            self::DELETE => Yii::t('permissions', 'Delete'),
            self::LIST => Yii::t('permissions', 'List'),
            self::VIEW => Yii::t('permissions', 'View'),
            self::UPDATE => Yii::t('permissions', 'Update'),
        ];
    }
}
