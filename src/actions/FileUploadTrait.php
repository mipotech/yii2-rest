<?php

namespace mipotech\yii2rest\actions;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

trait FileUploadTrait
{
    /**
     * Traverse the $_FILES collection and see if we have any uploaded files
     * to process
     *
     * @param Model $model the model that was just created or updated
     */
    protected function handleFileUploads(Model $model)
    {
        if (empty($_FILES)) {
            Yii::debug("No file uploads to process");
            return;
        }

        foreach ($_FILES as $key => $arr) {
            Yii::debug("Checking model for {$key} property");
            if ($model->hasProperty($key)) {
                $this->saveUploadedFiles($model, $key);
            }
        }
    }

    /**
     * Process uploads for a single model attribute
     *
     * @param Model $model
     * @param string $attribute
     */
    protected function saveUploadedFiles(Model $model, string $attribute)
    {
        $files = UploadedFile::getInstancesByName($attribute);
        Yii::debug("Files found for {$attribute}:\n" . print_r($files, true));
        switch (count($files)) {
            case 0:
                Yii::debug("No files to process for {$attribute}. Skipping...");
                break;
            case 1:
                Yii::debug("One file found for {$attribute}");
                if (method_exists($model, 'handleUpload')) {
                    Yii::debug("Invoking model's custom upload function");
                    $model->handleUpload($attribute, $files[0]);
                } else {
                    Yii::debug("Invoking default upload function");
                    $this->saveUploadedFile($model, $attribute, $files[0]);
                }
                break;
            default:        // more than one
                Yii::debug(count($files) . " files found for {$attribute}");
                // @@@ tbd
                break;
        }
    }

    /**
     * This is the default handler for uploaded files
     *
     * @param Model $model
     * @param string $attribute
     * @param UploadedFile $file
     */
    protected function saveUploadedFile(Model $model, string $attribute, UploadedFile $file)
    {
        // Resolve the short name of the class
        $classNameParts = explode('\\', get_class($model));
        $shortClassName = lcfirst(array_pop($classNameParts));

        // Build the various paths
        $virtualPath = "/uploads/{$shortClassName}/" . date('Y');
        $physicalPath = Yii::getAlias('@webroot') . $virtualPath;
        $fileName = $model->primaryKey . '-' . $file->baseName . '-' . Yii::$app->security->generateRandomString(5) . '.' . $file->extension;
        $savePath = $physicalPath . '/' . $fileName;

        Yii::debug("Virtual path: {$virtualPath}\nPhysical path: {$physicalPath}\nFinal save path: {$savePath}");

        if (!is_dir($physicalPath)) {
            mkdir($physicalPath, 0777, true);
        }

        // Before saving the new file, check if there's an old file that needs to be removed
        $isUpdate = in_array(Yii::$app->request->method, ['PUT', 'PATCH']);
        if ($isUpdate && !empty($model->{$attribute})) {
            $tmpPath = Yii::getAlias('@webroot' . $model->{$attribute});
            Yii::debug("Deleting existing file: {$tmpPath}");
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
                Yii::debug("File deleted: {$tmpPath}");
            } else {
                Yii::warning("File not found: {$tmpPath}");
            }
        }

        $res = $file->saveAs($savePath);
        if (!$res) {
            Yii::warning("saveAs failed. Trying copy...");
            $res = $file->saveAs($savePath, false);
            if ($res) {
                @unlink($file->tempName);
            } else {
                Yii::error("saveAs with copy also failed");
            }
        }
        $model->{$attribute} = $virtualPath . '/' . $fileName;
        $model->save(false);
    }
}
