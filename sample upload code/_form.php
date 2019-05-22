<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ContactLists */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="alert alert-secondary" role="alert">

Fields with * are required. <br >

List File uploaded MUST be CSV or Excel format <br >

The First Row is the Labels row<br >

<h3 style="color:#069"><b><font color="#006699">The First Column should Always be the Mobile Numbers column</font></b></h3><br >

<code><b>[0722111222,24,F] </b></code>
</div>




   <?php $form = ActiveForm::begin(['options' => ['class' => 'form','enctype' => 'multipart/form-data']]); ?>
	                      <div class="form-body">
	<?php echo $form->errorSummary($model, array('errorCssClass' => 'alert alert-error')); ?>

	                        <div class="form-group row">
				                          <label class="col-md-2 label-control" for="timesheetinput1">List Name</label>
                          <div class="col-md-6">
                            <div class="position-relative">  
    <?= $form->field($model, 'listName')->textInput(['maxlength' => true])->label(false) ?>                           
		</div>
                          </div>
                        </div>
	                        <div class="form-group row">
				                          <label class="col-md-2 label-control" for="timesheetinput1">File</label>
                          <div class="col-md-6">
                            <div class="position-relative">  
   	<?= $form->field($model,'filename')->fileInput()->label(false) ?>                           
		</div>
                          </div>
                        </div>
 

                         <div class="form-actions center">
        <?= Html::submitButton('Create List', ['class' => 'btn btn-success']) ?>

                  <?= Html::a(' <i class="ft-x"></i> Cancel', ['index'], ['class' => 'btn btn-warning mr-1']) ?>
						     
                      </div>
</div>



    <?php ActiveForm::end(); ?>