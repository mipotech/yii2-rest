<?php

namespace mipotech\yii2rest\actions;

use Yii;

/**
 * This is the base class for all nested actions.
 */
abstract class BaseNestedAction extends BaseAction
{
    /**
     * @inheritdoc
     */
    public function run($id, string $nestedAction, $nestedId = null)
    {
        // Retrieve the parent model associated with this nested action
        $parentModel = $this->findModel($id, $nestedAction);

        /*
         * Resolve the name of the controller function to invoke.
         * The pattern is "nested{Action}{Suffix}", where:
         * Action = something like "grades"
         * Suffix = create/index/update/delete/view/etc.
         */
        $actionMap = $this->controller->actions();
        $fnName = 'nested' . $this->normalizeActionName($nestedAction) . $this->functionSuffix();
        if (isset($actionMap[$fnName])) {
            Yii::debug("Found nested action class {$fnName} for nested action {$nestedAction}");
            $tmpAction = Yii::createObject($actionMap[$fnName], [$fnName, $this->controller]);
            $ret = $tmpAction->runWithParams([
                'model' => $parentModel,
                'id' => $nestedId,
            ]);
            $this->postProcess($ret);
            return $ret;
        } elseif (method_exists($this->controller, $fnName)) {
            Yii::debug("Found nested action function {$fnName} for nested action {$nestedAction}");
            $ret = call_user_func([$this->controller, $fnName], $parentModel, $nestedId);
            $this->postProcess($ret);
            return $ret;
        } else {
            Yii::debug("Could not resolve nested action {$nestedAction} to existing function. Target function name: {$fnName}");
            throw new \yii\web\MethodNotAllowedHttpException("No implementation for {$fnName}");
        }
    }

    /**
     * The suffix to add to the nested{Action}{Suffix} callback function
     *
     * @return string
     */
    abstract protected function functionSuffix(): string;

    /**
     * Normalize the action name just like Yii normalizes action names
     *
     * @param string $nestedAction
     * @return string
     */
    protected function normalizeActionName(string $nestedAction): string
    {
        return preg_replace_callback('%-([a-z0-9_])%i', function ($matches) {
            return ucfirst($matches[1]);
        }, ucfirst($nestedAction));
    }

    /**
     * Optional hook for post-processing on the return data
     */
    protected function postProcess(&$ret)
    {
    }
}
