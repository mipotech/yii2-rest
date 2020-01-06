<?php

namespace mipotech\yii2rest\actions;

/**
 * This is the base class for all nested actions.
 */
abstract class BaseNestedAction extends BaseAction
{
    /**
     * @inheritdoc
     */
    public function run($id, string $nestedAction)
    {
        // Retrieve the parent model associated with this nested action
        $parentModel = $this->findModel($id, $nestedAction);

        /*
         * Resolve the name of the controller function to invoke.
         * The pattern is "nested{Action}{Suffix}", where:
         * Action = something like "grades"
         * Suffix = create/index/update/delete/view/etc.
         */
        $fnName = 'nested' . $this->normalizeActionName($nestedAction) . $this->functionSuffix();
        if (method_exists($this->controller, $fnName)) {
            $ret = ['data' => call_user_func([$this->controller, $fnName], $parentModel)];
            $this->postProcess($ret);
            return $ret;
        } else {
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
