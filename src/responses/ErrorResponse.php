<?php

namespace mipotech\yii2rest\responses;

class ErrorResponse extends BaseResponse
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var int
     */
    public $status;
    /**
     * @var mixed
     */
    public $detail;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'status'], 'required'],
            [['status'], 'integer'],
            [['title',], 'string'],
            [['detail'], 'safe'],
        ];
    }
}
