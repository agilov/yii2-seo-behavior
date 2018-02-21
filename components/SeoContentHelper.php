<?php
namespace romi45\seoContent\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class SeoContentHelper
{
    /**
     * Getting behavior object from given model
     *
     * @param Component $model
     * @return SeoBehavior
     * @throws InvalidConfigException if model don't have our SeoBehavior
     */
    protected static function behavior(Component $model)
    {
        foreach ($model->getBehaviors() as $b) {
            if ($b instanceof SeoBehavior) {
                return $b;
            }
        }

        throw new InvalidConfigException('Model ' . $model->className() . ' must have SeoBehavior');
    }

	/**
	 * Register seo meta tag
	 *
	 * @param Component $model
	 * @param string $modelSeoAttributeName
	 * @param string $metaTagKey
	 */
	protected static function registerSeoMetaTag(Component $model, string $modelSeoAttributeName, string $metaTagKey)
	{
		$value = $model->{$modelSeoAttributeName};
		if ($value)
			Yii::$app->view->registerMetaTag(['name' => $metaTagKey, 'content' => $value], $metaTagKey);
	}

    /**
     * Register all title and seo metadata. You can register part of it using methods below right in view code.
     *
     * @param Component $model
     */
    public static function registerAll(Component $model)
    {
        self::setTitle($model);
        self::registerAllSeoMeta($model);
    }

	/**
	 * Register seo metadata. You can register part of it using methods below right in view code.
	 *
	 * @param Component $model
	 */
	public static function registerAllSeoMeta(Component $model)
	{
		self::registerMetaTitle($model);
		self::registerMetaKeywords($model);
		self::registerMetaDescription($model);
	}

    /**
     * Sets page title. If your layout
     *
     * @param Component $model
     */
    public static function setTitle(Component $model)
    {
        $title = $model->{self::behavior($model)->titleAttribute};
        if ($title)
            Yii::$app->view->title = $title;
    }

    /**
     * Register meta title
     *
     * @param Component $model
     */
    public static function registerMetaTitle(Component $model)
    {
	    $modelSeoAttributeName = self::behavior($model)->titleAttribute;
	    self::registerSeoMetaTag($model, $modelSeoAttributeName, 'title');
    }

    /**
     * Register meta keywords
     *
     * @param Component $model
     */
    public static function registerMetaKeywords(Component $model)
    {
	    $modelSeoAttributeName = self::behavior($model)->keywordsAttribute;
	    self::registerSeoMetaTag($model, $modelSeoAttributeName, 'keywords');
    }

    /**
     * Register meta description
     *
     * @param Component $model
     */
    public static function registerMetaDescription(Component $model)
    {
    	$modelSeoAttributeName = self::behavior($model)->descriptionAttribute;
    	self::registerSeoMetaTag($model, $modelSeoAttributeName, 'description');
    }
}