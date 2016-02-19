<?php

namespace romi45\seoContent\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "seo_content".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property string $title
 * @property string $keywords
 * @property string $description
 */
class SeoContent extends ActiveRecord
{
    /**
     * @var array
     */
    protected $_attribute_labels = [];

    /**
     * Sets attribute label
     *
     * @param $attr
     * @param $value
     */
    public function setAttributeLabel($attr, $value)
    {
        $this->_attribute_labels[$attr] = $value;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'seo_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'unique', 'on' => 'unique_title'],
            [['model_name', 'model_id'], 'required'],
            [['model_name', 'title'], 'string', 'max' => 255],
            [['keywords'], 'string', 'max' => 512],
            [['description'], 'string', 'max' => 1024],
            [['model_name', 'model_id'], 'unique', 'targetAttribute' => ['model_name', 'model_id'], 'message' => 'The combination of Model Name and Model ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return $this->_attribute_labels;
    }
}