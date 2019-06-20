<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Log;
use yii\jui\DatePicker;
//use kartik\date\DatePicker;
//use kartik\field\FieldRange;
//use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\LogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="log-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'date')->widget(DatePicker::class,['dateFormat' => 'yyyy-MM-dd','language'=>'ru']); ?>
    <?= $form->field($model, 'date2')->widget(DatePicker::class,['dateFormat' => 'yyyy-MM-dd','language'=>'ru']); ?>

    <?php  echo $form->field($model, 'os') ->dropDownList(ArrayHelper::map(Log::find()->select('os')->groupBy('os')->all(),'os','os'),['prompt'=>'все значения'])  ?>

    <?php  echo $form->field($model, 'archi')->dropDownList(ArrayHelper::map(Log::find()->select('archi')->groupBy('archi')->all(),'archi','archi'),['prompt'=>'все значения']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary','onclick'=>"location.href='/'"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
