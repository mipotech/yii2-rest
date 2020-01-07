<?php

namespace mipotech\yii2rest;

use Yii;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Response;

trait RestControllerTrait
{
    /**
     * @var app\models\ApiError
     */
    public $errorObject = null;
    /**
     * @var array an array of language labels for display on the resulting
     *  client-facing page
     */
    public $labels = [];

    /**
     * @var array actions that do not require authentication
     */
    protected $authExceptActions = [];
    /**
     * @var mipotech\yii2rest\models\Permission the model representing the permission rule
     *  that was found for authorizing the current request
     */
    protected $permissionRule;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove authentication filter
        if (isset($behaviors['authenticator'])) {
            unset($behaviors['authenticator']);
        }

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => array_merge(['options'], $this->authExceptActions),
        ];

        // enable JSON-based requests
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ],
        ];

        return $behaviors;
    }

    /**
     * Load the permission rule for this request
     *
     * @param string $action
     * @param yii\base\Model $model
     * @param array $params
     */
    public function loadPermissionsRule(string $action, $model = null, array $params = [])
    {
        $permissionsQuery = $this->generatePermissionsQuery($action, $model, $params);
        if (is_bool($permissionsQuery)) {
            if ($permissionsQuery) {
                Yii::debug("Permissions query set to true. Allowing global access");
                return;
            } else {
                $msg = "No access allowed to requested route";
                $this->errorHandler('Forbidden', 403, $msg);
                throw new \yii\web\ForbiddenHttpException($msg);
            }
        } elseif (empty($permissionsQuery)) {
            Yii::debug("Empty permissions query");
            return;
        }

        // If we got here, then try to retrieve at least one permission
        // rule that can authorize this request
        $this->permissionRule = $permissionsQuery->one();

        Yii::debug("Permissions record: " . json_encode($this->permissionRule));

        if (empty($this->permissionRule)) {
            $msg = "No permissions rule found. User ID = " . Yii::$app->user->id . " ; Model = {$this->modelClass}; Action = {$action}";
            $this->errorHandler('Forbidden', 403, $msg);
            Yii::error($msg, 'permissions');
            throw new \yii\web\ForbiddenHttpException($msg);
        }
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (is_null($this->permissionRule)) {
            $this->loadPermissionsRule($action);
        }
    }

    /**
     * A helper function for outputting error messages with complex output
     *
     * @param string $title
     * @param int $status
     * @param mixed $detail
     * @return array
     */
    public function errorHandler(string $title, int $status, $detail = null)
    {
        $this->errorObject = new responses\ErrorResponse([
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
        ]);
        Yii::$app->response->statusCode = $status;
        return $this->errorObject->toArray();
    }

    /**
     * Generate a query for retrieving the permission(s)
     * record relevant to the current request.
     *
     * @return yii\db\Query|bool
     */
    abstract protected function generatePermissionsQuery(string $action, $model, array $params);
}
