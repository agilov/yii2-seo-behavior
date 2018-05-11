<?php

namespace romi45\seoContent\components;

use Yii;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use romi45\seoContent\models\SeoContent;
use yii\helpers\ArrayHelper;

/**
 * Seo content behavior
 *
 * @property BaseActiveRecord $owner
 */
class SeoBehavior extends Behavior {
	/**
	 * @var BaseActiveRecord
	 */
	private $_model = null;

	/**
	 * @var string Model attribute for title
	 */
	public $titleAttribute = 'seoTitle';

	/**
	 * @var boolean enable or disable sql queries caching
	 */
	public $enableSqlQueryCache = false;

	/**
	 * @var integer sql queries cache duration
	 */
	public $sqlQueryCacheDuration = 1209600; // 14 day

	/**
	 * @var string Model attribute for keywords
	 */
	public $keywordsAttribute = 'seoKeywords';

	/**
	 * @var string Model attribute for description
	 */
	public $descriptionAttribute = 'seoDescription';


	public function attach($owner) {
		parent::attach($owner);

		if (!is_bool($this->enableSqlQueryCache)) {
			throw new InvalidConfigException('SeoBehavior Error: $enableSqlQuery must be boolean in class: '.$owner::className());
		}

		if (!is_integer($this->sqlQueryCacheDuration)) {
			throw new InvalidConfigException('SeoBehavior Error: $sqlQueryCacheDuration must be integer in class: '.$owner::className());
		}
	}

	/**
	 * @inheritdoc
	 */
	public function canGetProperty($name, $checkVars = true) {
		if (in_array($name, [$this->titleAttribute, $this->keywordsAttribute, $this->descriptionAttribute])) {
			return true;
		}

		return parent::canGetProperty($name, $checkVars);
	}

	/**
	 * @inheritdoc
	 */
	public function __get($name) {
		$model = $this->getSeoContentModel();

		$result = null;

		if ($model) {
			switch ($name) {
				case $this->titleAttribute:
					$result = ($this->isPropertyChanged($model, 'title')) ? $model->title :
						SeoPatternHelper::replace($model->title, $this->owner);
					break;
				case $this->keywordsAttribute:
					$result = ($this->isPropertyChanged($model, 'keywords')) ? $model->keywords :
						SeoPatternHelper::replace($model->keywords, $this->owner);
					break;
				case $this->descriptionAttribute:
					$result = ($this->isPropertyChanged($model, 'description')) ? $model->description :
						SeoPatternHelper::replace($model->description, $this->owner);
					break;
			}
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function canSetProperty($name, $checkVars = true) {
		if (in_array($name, [$this->titleAttribute, $this->keywordsAttribute, $this->descriptionAttribute])) {
			return true;
		}

		return parent::canSetProperty($name, $checkVars);
	}

	/**
	 * @inheritdoc
	 */
	public function __set($name, $value) {
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
	public function events() {
		return [
			BaseActiveRecord::EVENT_AFTER_INSERT => 'saveSeoContent',
			BaseActiveRecord::EVENT_AFTER_UPDATE => 'saveSeoContent',
			BaseActiveRecord::EVENT_AFTER_DELETE => 'deleteSeoContent',
		];
	}


	/**
	 * Saving seo content
	 */
	public function saveSeoContent() {
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
	public function deleteSeoContent() {
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
	public function getSeoContentModel() {
		if ($this->_model === null) {
			$seoOwnModelQuery = SeoContent::find()->where([
				'model_id' => $this->owner->getPrimaryKey(),
				'model_name' => $this->owner->className()
			]);

			$enableCache = $this->owner->enableSqlQueryCache;
			$cacheDuration = $this->owner->sqlQueryCacheDuration;

			if ($enableCache) {
				$seoOwnModelQuery->cache($cacheDuration);
			}

			$seoModel = $seoOwnModelQuery->limit(1)->one();

			if (empty($seoModel)) {
				$seoGlobalModelQuery = SeoContent::find()->where([
					'model_name' => $this->owner->className(),
					'is_global' => 1,
				]);

				if ($enableCache) {
					$seoGlobalModelQuery->cache($cacheDuration);
				}

				$seoModel = $seoGlobalModelQuery->limit(1)->one();
			}

			$this->_model = $seoModel;

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
	public function checkSeoTitleIsGlobalUnique() {
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

	/**
	 * Check is property in model was changed.
	 *
	 * @param ActiveRecord $model
	 * @param $propertyName
	 *
	 * @return bool
	 */
	public function isPropertyChanged(ActiveRecord $model, $propertyName) {
		return ArrayHelper::keyExists($propertyName, $model->dirtyAttributes);
	}
}