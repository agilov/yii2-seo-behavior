<?php

use yii\db\Migration;

/**
 * Migration for seo content storage
 *
 * php yii migrate --migrationPath="@vendor/romi45/yii2-seo-behaviour/migrations"
 */
class m160219_172342_create_seo_content extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%seo_content}}', [
            'id' => $this->primaryKey(),
            'model_name' => $this->string(255)->notNull(),
            'model_id' =>  $this->string(255)->notNull(),
            'title' => $this->string(255),
            'keywords' => $this->string(512),
            'description' => $this->string(1024)
        ]);

        $this->createIndex('seo_content_model_model_id', '{{%seo_content}}', ['model_name', 'model_id'], true);
    }
    
    public function safeDown()
    {
        $this->dropTable('{{%seo_content}}');
    }
}
