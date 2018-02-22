# Seo meta contents behavior for Yii 2

This extension provides behavior functions for seo meta tags and title tag support.
Also provides view helper for registering meta tags and title.

## Installation

My favorite way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require romi45/yii2-seo-behavior:~1.0
```

or add

```
"romi45/yii2-seo-behavior": "~1.0"
```
to the `require` section of your `composer.json` file.

and then run migration

```
php yii migrate --migrationPath="@vendor/romi45/yii2-seo-behavior/migrations"
```

## Configuring

First you need to configure your model:

```php
use romi45\seoContent\components\SeoBehavior;

class Post extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            [
                'seo' => [
                    'class' => SeoBehavior::className(),

                    // This is default values. Usually you can not specify it
                    'titleAttribute' => 'seoTitle',
                    'keywordsAttribute' => 'seoKeywords',
                    'descriptionAttribute' => 'seoDescription'
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // ...
            [['seoTitle', 'seoKeywords', 'seoDescription'], 'safe'],
            [['seoTitle'], 'checkSeoTitleIsGlobalUnique'], // It recommends for title to be unique for every page. You can ignore this recommendation - just delete this rule.
            // ...
        ];
    }
}
```

Now you are ready to use it on form

```php

<?= $form->field($model, 'seoTitle')->textInput(); ?>
<?= $form->field($model, 'seoKeywords')->textInput(); ?>
<?= $form->field($model, 'seoDescription')->textarea(); ?>
```

As you can see, `seoTitle`, `seoKeywords` and `seoDescription` is the attributes (by default) from which we can access SEO content of model.

Once you post a form with the above fields, they will be automatically saved and linked to our `Post` model.



To register meta tags and set title in view use following code:

```php

use romi45\seoContent\components\SeoContentHelper;

/**
 * You can also user partial register functions
 * @see SeoContentHelper::registerAll()
 */
SeoContentHelper::registerAll($model);
```

Do not forget about title tag in layout.

```php
<title><?= Html::encode($this->title) ?></title>
```

## Patterns

You can use patterns in values and replace it will replaced with some model properties, application config
property, application parameter or view parameter type will defined by prefixes.

####Model Attribute

```php
%%model_ATTRIBUTE_NAME%%
```

For example ```%%model_title%%``` will replace with ```php $model->title```

####Application Global Config Attribute

```php
%%appConfig_ATTRIBUTE_NAME%%
```

For example ```%%appConfig_name%%``` will replace with ```php Yii::$app->name```

####Application Global Parameter Attribute

```php
%%appParam_ATTRIBUTE_NAME%%
```

For example ```%%appParam_contactEmail%%``` will replace with ```php Yii::$app->params['contactEmail'']```

####View Global Parameter Attribute

```php
%%viewParam_ATTRIBUTE_NAME%%
```

For example ```%%viewParam_contactEmail%%``` will replace with ```php Yii::$app->view->params['contactEmail'']```.

####Separator

```php
%%sep%%
```

By default separator pattern replaced with '-'. If you want to use another value for separator you need to identify
```php Yii::$app->view->params['titleSeparator'']``` param.

Hint: instead of 'titleSeparator' you can use ```romi45\seoContent\components\SeoPatternHelper::SEPARATOR_VIEW_PARAMETER_KEY```
constant value.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.