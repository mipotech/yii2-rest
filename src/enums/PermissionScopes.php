<?php

namespace mipotech\yii2rest\enums;

use Yii;
use yii2mod\enum\helpers\BaseEnum;

/**
 * The permission scopes are intentionally numeric
 * and in a very specific order.
 *
 * When we query for a permissions rules, we want to be able to query by scope ASC,
 * so that we'll get the more specific rules first and the more encompassing rules
 * after that. I.e., if there is a rule for a specific user and also for the role
 * to which the user belongs, we'll take the user's specific configuration.
 *
 * @author Chaim
 */
class PermissionScopes extends BaseEnum
{
    const USER = 1;
    const ROLE = 2;
    const GLOBAL = 3;

    /**
     * @inheritdoc
     */
    public static function getList()
    {
        return [
            self::USER => Yii::t('permissions', 'User'),
            self::ROLE => Yii::t('permissions', 'Role'),
            self::GLOBAL => Yii::t('permissions', 'Global'),
        ];
    }
}
