<?php
namespace romi45\seoContent\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class SeoContentHelper
{
    /**
     * Getting behaviour object from given model
     *
     * @param Component $model
     * @return SeoBehaviour
     * @throws InvalidConfigException if model don't have our SeoBehaviour
     */
    protected static function behaviour(Component $model)
    {
        foreach ($model->getBehaviors() as $b) {
            if ($b instanceof SeoBehaviour) {
                return $b;
            }
        }

        throw new InvalidConfigException('Model ' . $model->className() . ' must have SeoBehaviour');
    }

    /**
     * Register all title and seo metadata. You can register part of it using methods below right in view code.
     *
     * @param Component $model
     */
    public static function registerAll(Component $model)
    {
        self::setTitle($model);
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
        $title = $model->{self::behaviour($model)->titleAttribute};
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
        $title = $model->{self::behaviour($model)->titleAttribute};
        if ($title)
            Yii::$app->view->registerMetaTag(['name' => 'title', 'content' => $title], 'title');
    }

    /**
     * Register meta keywords
     *
     * @param Component $model
     */
    public static function registerMetaKeywords(Component $model)
    {
        $keywords = $model->{self::behaviour($model)->keywordsAttribute};
        if ($keywords)
            Yii::$app->view->registerMetaTag(['name' => 'keywords', 'content' => $keywords], 'keywords');
    }

    /**
     * Register meta description
     *
     * @param Component $model
     */
    public static function registerMetaDescription(Component $model)
    {
        $description = $model->{self::behaviour($model)->descriptionAttribute};
        if ($description)
            Yii::$app->view->registerMetaTag(['name' => 'description', 'content' => $description], 'description');
    }

}