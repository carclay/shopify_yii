<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CronTask */

$this->title = 'Create Cron Task';
$this->params['breadcrumbs'][] = ['label' => 'Cron Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cron-task-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
