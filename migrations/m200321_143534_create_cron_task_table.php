<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cron_task}}`.
 */
class m200321_143534_create_cron_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cron_task}}', [
            'id' => $this->primaryKey(),
            'shop' => $this->string(255)->notNull(),
            'status' => $this->boolean()->defaultValue(0),
            'last_id' => $this->bigInteger()->notNull()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%cron_task}}');
    }
}
