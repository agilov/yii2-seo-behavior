<?php

use yii\db\Migration;

/**
 * Class m190618_014614_drop_seo_content_index
 */
class m190618_014614_drop_seo_content_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('seo_content_model_name_is_global', '{{%seo_content}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190618_014614_drop_seo_content_index cannot be reverted.\n";

        return false;
    }
}
