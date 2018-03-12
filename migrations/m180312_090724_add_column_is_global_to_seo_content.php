<?php

use yii\db\Migration;

/**
 * Migration for add is global column to seo content table
 *
 * php yii migrate --migrationPath="@vendor/romi45/yii2-seo-behavior/migrations"
 */
class m180312_090724_add_column_is_global_to_seo_content extends Migration
{
    public function safeUp()
    {
    	$this->addColumn('{{%seo_content}}', 'is_global', $this->boolean());

	    $this->createIndex('seo_content_model_name_is_global', '{{%seo_content}}', ['model_name', 'is_global'], true);
    }
    
    public function safeDown()
    {
    	$this->dropIndex('seo_content_model_name_is_global', '{{%seo_content}}');

	    $this->dropColumn('{{%seo_content}}', 'is_global');
    }
}
