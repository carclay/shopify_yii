<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%cron_task}}`.
 */
class m200321_150858_add_started_column_to_cron_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cron_task', 'started', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('cron_task', 'started');
    }
}
