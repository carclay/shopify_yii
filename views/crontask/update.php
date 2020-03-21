<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CronTask */

$this->title = 'Update Cron Task: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Cron Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="cron-task-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
