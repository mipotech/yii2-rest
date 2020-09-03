<?php

namespace mipotech\yii2rest\models;

use yii\mongodb\ActiveRecord;

use mipotech\yii2rest\enums\{PermissionActions, PermissionEntityTypes, PermissionScopes};

use mipotech\yii2cms\components\CmsRecordInterface;

class Permission extends ActiveRecord implements CmsRecordInterface
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'permission';
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            /**
             * @property string page / model / report
             */
            'entity_type',
             /**
              * @property string page name, report name, fully qualified class name
              */
            'entity_name',
            /**
             * @property int|string|ObjectId enable to drill down to the level of a specific instance/record
             */
            'entity_id',
            /**
             * @property string[] ['create', 'delete', 'index', 'update', 'view']
             */
            'allowed_actions',
            /**
             * @property string user / role / global
             */
            'scope',
            /**
             * @property int user_id or role_id
             */
            'scope_id',
            /**
             * @property array query conditions
             *
             * Example:
             * conditions: [
             *     "data_DetailedCourses.OwnerEntityId": "{{CURRENT_USER.ID}}"
             * ]
             */
            'conditions',       // array of
            /**
             * @property array specific fields with granular permissions
             *
             * Example:
             * fields: [{
             *     "name": "BaseCourseName",
             *     "level": "update" or "read"
             * }]
             * or as a simple array:
             * fields: [
             *     "Id",
             *     "Name",
             *     "Description"
             * ]
             */
            'fields',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_type', 'entity_name', 'allowed_actions', 'scope',], 'required'],
            ['entity_type', 'in', 'range' => array_keys(PermissionEntityTypes::getList())],
            [['entity_name'], 'string'],
            [['allowed_actions', 'conditions'], 'each', 'rule' => ['string']],
            ['scope', 'in', 'range' => array_keys(PermissionScopes::getList())],
            ['scope_id', 'required', 'when' => function($model) {
                return in_array($model->scope, [
                    PermissionScopes::USER,
                    PermissionScopes::ROLE,
                ]);
            }],
            ['fields', 'each', 'rule' => ['safe']],
            // When the record refers to a model, make sure the model name
            // is valid
            ['entity_name', function ($attribute, $params, $validator) {
                if ($this->entity_type == PermissionEntityTypes::MODEL) {
                    if (!class_exists($this->{$attribute})) {
                        $this->addError($attribute, "Class {$this->{$attribute}} does not exist");
                    }
                }
            }],
            ['entity_id', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'entity_type' => 'Entity Type',
            'entity_name' => 'Entity Name',
            'entity_id' => 'Entity ID',
            'allowed_actions' => 'Allowed Actions',
            'scope' => 'Scope',
            'scope_id' => 'Scope ID',
            'conditions' => 'Conditions',
            'fields' => 'Specific field rules',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Normalize data so that:
        // 1. All numeric values are saved as numbers
        // 2. All empty string values are saves as NULL
        foreach ($this->attributes as $key => $attribute) {
            if (is_numeric($attribute)) {
                $this->{$key} = $attribute + 0;
            } elseif (is_string($attribute) && !strlen($attribute)) {
                $this->{$key} = null;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function displayFields()
    {
        return [
            '_id',
            [
                'attribute' => 'entity_type',
                'value' => function ($model, $key, $index, $column) {
                    return PermissionEntityTypes::getList()[$model->entity_type];
                },
                'filter' => PermissionEntityTypes::getList(),
            ],
            [
                'attribute' => 'entity_name',
            ],
            [
                'attribute' => 'entity_id',
            ],
            [
                'attribute' => 'allowed_actions',
                'value' => function ($model, $key, $index, $column) {
                    return implode("<br>", $model->allowed_actions);
                },
                'format' => 'html',
            ],
            [
                'attribute' => 'scope',
                'value' => function ($model, $key, $index, $column) {
                    return PermissionScopes::getList()[$model->scope];
                },
                'filter' => PermissionScopes::getList(),
            ],
            /**
             * When extending this class, it would be
             */
            [
                'attribute' => 'scope_id',
                'value' => function ($model, $key, $index, $column) {
                    switch ($model->scope) {
                        case PermissionScopes::USER:
                            $displayText = 'User ' . $model->scope_id;
                            break;
                        case PermissionScopes::ROLE:
                            $displayText = 'Role ' . (is_array($model->scope_id) ? implode(', ', $model->scope_id) : $model->scope_id);
                            break;
                        case PermissionScopes::GLOBAL:
                        default:
                            $displayText = 'Global';
                            break;
                    }
                    return $displayText;
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     * @todo Add render fules for conditions array
     * @todo Add render fules for fields array
     */
    public function renderRules()
    {
        $actionsOptions = PermissionActions::getList();
        if (!empty($this->allowed_actions)) {
            foreach ($this->allowed_actions as $act) {
                if (!in_array($act, array_keys($actionsOptions))) {
                    $actionsOptions[$act] = $act;
                }
            }
        }

        return [
            'entity_type' => [
                'type' => 'select',
                'options' => PermissionEntityTypes::getList(),
            ],
            'entity_name' => [
                'type' => 'text',
            ],
            'entity_id' => [
                'type' => 'text',
            ],
            'allowed_actions' => [
                'type' => 'select',
                'multiple' => true,
                'options' => $actionsOptions,
                'pluginOptions' => [
                    'tags' => true,
                ],
            ],
            'scope' => [
                'type' => 'select',
                'options' => PermissionScopes::getList(),
            ],
            'scope_id' => [
                'type' => 'text',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function searchFields()
    {
        return [];
    }
}
