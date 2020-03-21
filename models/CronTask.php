<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cron_task".
 *
 * @property int $id
 * @property string $shop
 * @property int|null $status
 * @property int $last_id
 * @property int|null $started
 */
class CronTask extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cron_task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shop', 'last_id'], 'required'],
            [['status', 'last_id', 'started'], 'integer'],
            [['shop'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop' => 'Shop',
            'status' => 'Status',
            'last_id' => 'Last ID',
            'started' => 'Started',
        ];
    }
}
