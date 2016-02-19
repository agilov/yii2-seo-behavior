# Seo meta contents behavior for Yii 2

This extension provides behavior functions for seo meta tags and title tag support.
Also provides view helper for registering meta tags and title.

## Installation

My favorite way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require romi45/yii2-seo-behaviour:~1.0
```

or add

```
"romi45/yii2-seo-behaviour": "~1.0"
```
to the `require` section of your `composer.json` file.

and then run migration

```
php yii migrate --migrationPath="@vendor/romi45/yii2-seo-behaviour/migrations"
```

## Configuring

First you need to configure your model:

```php
use romi45\seoContent\components\SeoBehaviour;

class Post extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            [
                'seo' => [
                    'class' => SeoBehaviour::className(),

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

```

<title><?= Html::encode($this->title) ?></title>
```



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.