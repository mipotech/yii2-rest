<?php

namespace mipotech\yii2rest\controllers;

use mipotech\yii2rest\enums\PermissionEntityTypes;

use mipotech\yii2rest\enums\PermissionScopes;
use mipotech\yii2rest\models\Permission;
use mipotech\yii2rest\RestControllerTrait;
use Yii;
use yii\base\Action;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\rest\ActiveController;
use yii\rest\IndexAction;

abstract class BaseCrudController extends ActiveController
{
    use RestControllerTrait {
        checkAccess as public traitCheckAccess;
    }

    /**
     * @var string|null the name of the relevant search model
     */
    public $searchModelClass = null;

    /**
     * @var boolean manual not found error response
     */
    public $manualNotFoundError = false;

    /**
     * @inheritdoc
     */
    protected function generatePermissionsQuery(string $action, $model, array $params)
    {
        /*
         * Build the scope condition
         * No matter what, we allow for a global role and a user-level rule.
         * If the consumer of this package has defined a callback to resolve the role id,
         * then we will add a role-level condition as well.
         */
        $scopeCondition = ['or',
            ['scope' => PermissionScopes::GLOBAL],
            ['and', [
                'scope' => PermissionScopes::USER,
                'scope_id' => Yii::$app->user->id,
            ]],
        ];
        if (is_callable(Yii::$app->controller->module->roleIdCallback)) {
            if ($roleId = call_user_func(Yii::$app->controller->module->roleIdCallback, Yii::$app->user)) {
                if (is_array($roleId)) {
                    $scopeCondition[] = ['and',
                        ['scope' => PermissionScopes::ROLE],
                        ['in', 'scope_id', $roleId],
                    ];
                } else {
                    $scopeCondition[] = ['and', [
                        'scope' => PermissionScopes::ROLE,
                        'scope_id' => $roleId,
                    ]];
                }
            }
        }

        $permissionQuery = Permission::find()
            ->where([
                'entity_type' => PermissionEntityTypes::MODEL,
                'entity_name' => $this->modelClass,
            ])
            ->andWhere(['in', 'allowed_actions', $action])
            ->andWhere($scopeCondition)
            ->orderBy([
                'scope' => SORT_ASC,
                'scope_id' => SORT_DESC, // favor a rule with a scope specific to this user
            ]);
        if (!is_null($model)) {
            $permissionQuery->andWhere(['or',
                ['entity_id' => null],
                ['entity_id' => $model->primaryKey],
            ]);
        }
        return $permissionQuery;
    }

    /**
     * @return mixed
     */
    protected function getRoleId()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        // Enforce custom permission rules for all actions that
        // invoke the findModel functionality
        $actions['index']['findModel'] = [$this, 'findModel'];
        $actions['delete']['findModel'] = [$this, 'findModel'];
        $actions['update']['findModel'] = [$this, 'findModel'];
        $actions['view']['findModel'] = [$this, 'findModel'];

        // Override the action class for file upload support
        $actions['create']['class'] = 'mipotech\yii2rest\actions\CreateAction';
        $actions['update']['class'] = 'mipotech\yii2rest\actions\UpdateAction';

        $actions['nested-create'] = [
            'class' => 'mipotech\yii2rest\actions\NestedCreateAction',
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'findModel' => [$this, 'findModel'],
        ];
        $actions['nested-delete'] = [
            'class' => 'mipotech\yii2rest\actions\NestedDeleteAction',
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'findModel' => [$this, 'findModel'],
        ];
        $actions['nested-index'] = [
            'class' => 'mipotech\yii2rest\actions\NestedIndexAction',
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'findModel' => [$this, 'findModel'],
        ];
        $actions['nested-view'] = [
            'class' => 'mipotech\yii2rest\actions\NestedViewAction',
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'findModel' => [$this, 'findModel'],
        ];
        $actions['nested-update'] = [
            'class' => 'mipotech\yii2rest\actions\NestedUpdateAction',
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'findModel' => [$this, 'findModel'],
        ];

        if (!empty($this->searchModelClass)) {
            $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        }
        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return array_merge(parent::verbs(), [
            'nested-index' => ['GET', 'HEAD'],
            'nested-view' => ['GET', 'HEAD'],
            'nested-create' => ['POST'],
            'nested-update' => ['PUT', 'PATCH'],
            'nested-delete' => ['DELETE'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        $this->traitCheckAccess($action, $model, $params);

        // If this is a modification request, check if we are limited to specific fields
        if (!empty($this->permissionRule) && (Yii::$app->request->isPut || Yii::$app->request->isPost)) {
            if (isset($this->permissionRule['fields']) && !empty($this->permissionRule['fields'][$action])) {
                $postedFields = Yii::$app->request->bodyParams;
                $postedFieldKeys = array_keys($postedFields);
                foreach ($postedFieldKeys as $key) {
                    if (!in_array($key, $this->permissionRule['fields'][$action])) {
                        throw new \yii\web\ForbiddenHttpException("Invalid update field: {$key}");
                    }
                }
            }
        }
    }

    /**
     * Custom implementation of findModel that also checks
     * permission ru    les
     *
     * @param string $id
     * @param \yii\base\Action|string $action
     * @throws \yii\base\Exception
     * @throws \yii\web\NotFoundHttpException
     * @return Model|null
     */
    public function findModel(string $id, $action)
    {
        if ($this->hasMethod('generateFindQuery')) {
            $query = $this->generateFindQuery($id, $action);
        } else {
            $modelClass = $this->modelClass;

            $query = $modelClass::find();
            $keys = $modelClass::primaryKey();

            // It's important to add the table name of the model to the primary keys
            // because there might be other tables joined later with identical column names
            array_walk($keys, function (&$value, $key) {
                $value = $this->modelClass::tableName() . '.' . $value;
            });

            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                $query->where(array_combine($keys, $values));
            }
        }

        if (is_null($this->permissionRule)) {
            if (is_string($action)) {
                $this->loadPermissionsRule($action);
            } elseif ($action instanceof \mipotech\yii2rest\actions\BaseNestedAction) {
                $this->loadPermissionsRule(Yii::$app->request->get('nestedAction'));
            } elseif ($action instanceof \yii\base\Action) {
                $this->loadPermissionsRule($action->id);
            } else {
                throw new \yii\base\Exception("Action must be instance of yii\base\Action or textual ID");
            }
        }

        if (!empty($this->permissionRule->conditions)) {
            foreach ($this->permissionRule->conditions as $cond) {
                $params = $cond['params'] ?? [];
                array_walk($params, function (&$item, $key) {
                    if (is_string($item) && preg_match('/^php:/', $item)) {
                        $tmp = preg_replace('/^php:/', '', $item);
                        $item = eval('return ' . $tmp . ';');
                    }
                });
                $query->andWhere($cond['condition'], $params);
                if (isset($cond['join'])) {
                    foreach ($cond['join'] as $joinRule) {
                        $joinType = $joinRule['type'] ?? 'leftJoin';
                        $query->{$joinType}($joinRule['table'], $joinRule['on']);
                    }
                }
            }
        }
        $model = $query->one();

        if (isset($model)) {
            return $model;
        } else {
            if(!$this->manualNotFoundError) {
                throw new \yii\web\NotFoundHttpException();
            }
        }
    }

    public function prepareDataProvider(IndexAction $action, $filter)
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        // Build an array of request params to be passed to the search model
        $modelClass = $this->modelClass;
        $searchModelName = $this->searchModelClass;
        //$model = new $modelClass();
        $searchModel = new $searchModelName;
        $formName = $searchModel->formName();

        $searchParams = array_filter($requestParams, function ($key) use ($searchModel) {
            return $searchModel->hasProperty($key);
        }, ARRAY_FILTER_USE_KEY);
        $searchArr = [
            $formName => $searchParams,
        ];

        $searchDataProvider = $searchModel->search($searchArr);

        // Automatic free text search
        if (!empty($searchModel->q)) {
            $searchFields = $searchModel->searchFields();
            $cond = [];
            foreach ($searchFields as $searchField) {
                if ($searchField instanceof Expression) {
                    $cond[] = ['like', $searchField, $searchModel->q];
                } elseif (is_string($searchField)) {
                    if (preg_match('/(.+)\.(\w+)/', $searchField, $matches)) {
                        $tableName = $matches[1];
                        $fieldName = $matches[2];
                    } else {
                        $tableName = $searchModel::tableName();
                        $fieldName = $searchField;
                    }

                    Yii::debug("Search field: " . print_r($searchField, true) . "; Table name = {$tableName}; Field name = {$fieldName}");

                    /**
                     * @link http://www.yiiframework.com/doc-2.0/yii-db-columnschema.html#$phpType-detail
                     */
                    $schemaFieldType = $searchModel::getDb()
                        ->getTableSchema($tableName)
                        ->getColumn($fieldName)
                        ->phpType;

                    // Special cases based upon the DB field type
                    switch ($schemaFieldType) {
                        case 'integer':
                            // If the field is numeric and the search term is not, then skip this field.
                            // Otherwise, the search returns incorrect results
                            if (!is_numeric($searchModel->q)) {
                                continue 2;
                            } else {
                                $cond[] = ['=', $tableName . '.' . $fieldName, $searchModel->q];
                            }
                            break;
                        case 'string':
                            $cond[] = ['like', $tableName . '.' . $fieldName, $searchModel->q];
                            break;
                    }
                }
            }

            array_unshift($cond, 'or');
            $searchDataProvider->query->andFilterWhere($cond);
        }

        // Parse any conditions that come from the permission rules
        if (!empty($this->permissionRule->conditions)) {
            foreach ($this->permissionRule->conditions as $cond) {
                $params = $cond['params'] ?? [];
                array_walk($params, function (&$item, $key) {
                    if (is_string($item) && preg_match('/^php:/', $item)) {
                        $tmp = preg_replace('/^php:/', '', $item);
                        $item = eval('return ' . $tmp . ';');
                    }
                });
                $searchDataProvider->query->andWhere($cond['condition'], $params);

                if (isset($cond['join'])) {
                    $cleanJoin = $this->cleanJoin($searchDataProvider->query->join);
                    foreach ($cond['join'] as $key => $joinRule) {
                        if (!$cleanJoin || !$this->includesInJoin($joinRule, $cleanJoin)) {
                            $joinType = $joinRule['type'] ?? 'leftJoin';
                            $searchDataProvider->query->{$joinType}($joinRule['table'], $joinRule['on']);
                        }
                    }
                }
            }
        }

        // Generate a clean data provider a la \yii\rest\IndexAction
        $finalDataProvider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $searchDataProvider->query,
            'pagination' => [
                'pageSizeLimit' => [1, 150],
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $requestParams,
                'attributes' => array_merge($searchModel->attributes(), get_object_vars($searchModel)),
            ],
        ]);

        return $finalDataProvider;
    }

    /**
     * clears join from spaces, }, {, %
     */
    private function cleanJoin($join)
    {
        if ($join) {
            foreach ($join as $k => $current) {
                foreach ($current as $key => $val) {
                    if (is_scalar($val)) {
                        $join[$k][$key] = strtoupper(preg_replace("/[\s{}%]/", "", $val));
                    }
                }
            }
        }
        return $join;
    }

    /**
     * @return bool true if $join is included in $allJoins
     */
    private function includesInJoin($join, $allJoins): bool
    {
        $valuesJoin = array_values($join);

        foreach ($allJoins as $currentJoin) {
            $valuesCurrent = array_values($currentJoin);

            $same = true;
            foreach ($valuesCurrent as $key => $val) {
                if ($val != strtoupper(preg_replace("/[\s{}%]/", "", $valuesJoin[$key]))) {
                    $same = false;
                    break;
                }
            }

            // if we got here - all the parts are equal and we've found a fit join
            if ($same) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        // Save the original result before serialization
        if (is_object($result)) {
            $rawResult = clone $result;
        } else {
            $rawResult = $result;
        }

        $result = parent::afterAction($action, $result);

        /*
         * If the action returned a data provider, then format the output
         * in the following manner:
         * 'data' => [
         *     'count' => X,
         *     'rows' => [...],
         *     //'labels' => [...]
         * ],
         */
        if ($rawResult instanceof \yii\data\BaseDataProvider && Yii::$app->response->statusCode < 300) {
            $count = Yii::$app->response->headers->get('x-pagination-total-count');
            $result = [
                'data' => [
                    'count' => $count,
                    'rows' => $result,
                    //'labels' => $this->labels,
                ],
            ];
        } elseif (is_array($result) && Yii::$app->response->statusCode < 300) {
            $result = [
                'data' => $result,
            ];
        }

        return $result;
    }
}
