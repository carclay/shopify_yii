<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%cron_task}}`.
 */
class m200322_084535_add_processed_column_to_cron_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cron_task', 'processed', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('cron_task', 'processed');
    }
}
