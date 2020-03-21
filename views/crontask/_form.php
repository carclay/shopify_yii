<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CronTask */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cron-task-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'shop')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'last_id')->textInput() ?>

    <?= $form->field($model, 'started')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
