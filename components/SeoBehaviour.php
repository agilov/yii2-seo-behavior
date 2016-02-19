<?php
namespace romi45\seoContent\components;

use Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use romi45\seoContent\models\SeoContent;

/**
 * Seo content behaviour
 *
 * @property BaseActiveRecord $owner
 */
class SeoBehaviour extends Behavior
{
    /**
     * @var BaseActiveRecord
     */
    private $_model = null;

    /**
     * @var string Model attribute for title
     */
    public $titleAttribute = 'seoTitle';

    /**
     * @var string Model attribute for keywords
     */
    public $keywordsAttribute = 'seoKeywords';

    /**
     * @var string Model attribute for description
     */
    public $descriptionAttribute = 'seoDescription';


    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if (in_array($name, [$this->titleAttribute, $this->keywordsAttribute, $this->descriptionAttribute])) {
            return true;
        }

        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        $model = $this->getSeoContentModel();

        $result = null;

        if ($model) {
            switch ($name) {
                case $this->titleAttribute:
                    $result = $model->title;
                    break;
                case $this->keywordsAttribute:
                    $result = $model->keywords;
                    break;
                case $this->descriptionAttribute:
                    $result = $model->description;
                    break;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if (in_array($name, [$this->titleAttribute, $this->keywordsAttribute, $this->descriptionAttribute])) {
            return true;
        }

        return parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case $this->titleAttribute:
                $this->getSeoContentModel()->title = $value;
                break;
            case $this->keywordsAttribute:
                $this->getSeoContentModel()->keywords = $value;
                break;
            case $this->descriptionAttribute:
                $this->getSeoContentModel()->description = $value;
                break;
        }
    }

    /**
     * Events triggers
     *
     * @return array
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => 'saveSeoContent',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'saveSeoContent',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'deleteSeoContent',
        ];
    }


    /**
     * Saving seo content
     */
    public function saveSeoContent()
    {
        $model = $this->getSeoContentModel();
        $model->title = $this->owner->{$this->titleAttribute};
        $model->keywords = $this->owner->{$this->keywordsAttribute};
        $model->description = $this->owner->{$this->descriptionAttribute};
        $model->save();
    }


    /**
     * Deleting seo content
     *
     * @throws \Exception
     */
    public function deleteSeoContent()
    {
        $model = $this->getSeoContentModel();
        if ($model && !$model->getIsNewRecord()) {
            $model->delete();
        }
    }

    /**
     * Seo content
     *
     * @return SeoContent
     */
    public function getSeoContentModel()
    {
        if ($this->_model === null) {
            $this->_model = SeoContent::findOne([
                'model_id' => $this->owner->getPrimaryKey(),
                'model_name' => $this->owner->className()
            ]);

            if ($this->_model === null) {
                $this->_model = new SeoContent();
                $this->_model->model_id = $this->owner->getPrimaryKey();
                $this->_model->model_name = $this->owner->className();
            }
        }

        return $this->_model;
    }


    /**
     * @return bool title unique validator
     */
    public function checkSeoTitleIsGlobalUnique()
    {
        $model = $this->getSeoContentModel();

        $model->setScenario('unique_title');

        $model->title = $this->owner->{$this->titleAttribute};

        $model->setAttributeLabel('title', $this->owner->getAttributeLabel($this->titleAttribute));

        $model->validate();

        if ($errors = $model->getErrors('title')) {

            $model->clearErrors();

            foreach ($errors as $e) {
                $this->owner->addError($this->titleAttribute, $e);
            }

            return false;
        }

        return true;
    }
}