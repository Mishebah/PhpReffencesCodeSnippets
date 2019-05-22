<?php

namespace app\controllers;

use Yii;
use yii\helpers\Html;
use app\models\ContactLists;
use app\models\ContactListsSearch;
use app\models\ContactListEntries;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use app\components\CoreUtils;
use app\components\PermissionUtils;
use yii\db\Expression;
use app\components\StatusCodes;
use yii\data\ActiveDataProvider;
use yii2tech\spreadsheet\Spreadsheet;

/**
 * ContactListsController implements the CRUD actions for ContactLists model.
 */
class ContactListsController extends Controller
{
	
	public function beforeAction($action)
{
    Yii::$app->view->params['menu'] = '  <div class="navbar-default" role="navigation">
                    <div class="navbar-collapse collapse sidebar-navbar-collapse">
							<ul class="nav navbar-nav side-nav">
                            <li class="selected active">'. Html::a('Contact Lists<span class="fa arrow"></span>', [''],['class'=>'class=&#039;selected active&#039;']).'                          
							<ul class="active-menu">														
							<li>
							'. Html::a('-- New List <span class="glyphicon glyphicon-menu-right">', ['create'],['class'=>'active']).'                    
								</li>
								<li>
							'. Html::a('-- All Lists <span class="glyphicon glyphicon-menu-right">', ['index'],['class'=>'active']).'                    
								</li>
                            	</ul>
                          <li class="selected active">'. Html::a('Contact List Entries<span class="fa arrow"></span>', ['/contact-list-entries/index'],['class'=>'class=&#039;selected active&#039;']).'                          
							<ul class="active-menu">														
							<li>
							'. Html::a('-- Add Number to List <span class="glyphicon glyphicon-menu-right">', ['/contact-list-entries/create'],['class'=>'active']).'                    
								</li>
								<li>
							'. Html::a('-- All Numbers <span class="glyphicon glyphicon-menu-right">', ['/contact-list-entries/index'],['class'=>'active']).'                    
								</li>
                            	</ul>
                             </ul>
					</div>
				</div>';
  //return true;
  return parent::beforeAction($action);
}


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ContactLists models.
     * @return mixed
     */
    public function actionIndex()
    {
						if(!PermissionUtils::checkModuleActionPermission("ContactLists",PermissionUtils::VIEW_ALL)){
				throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        $searchModel = new ContactListsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ContactLists model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
		if(!PermissionUtils::checkModuleActionPermission("ContactLists",PermissionUtils::VIEW_ONE)){
				throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
		 $model = $this->findModel($id);
		 if($model)
		 {
			 //we load the  
			 $dataProvider = new ActiveDataProvider([
    'query' => ContactListEntries::find()->select('MSISDN, contactName,dateCreated')->where(['active'=>StatusCodes::ACTIVE,'contactListID'=>$model->contactListID]),
]);
		 
        return $this->render('view', [
            'model' =>  $model,'dataProvider'=> $dataProvider
        ]);
		 }
		 else
			 throw new \yii\web\NotFoundHttpException('You are not allowed to perform this action.');
    }

    /**
     * Creates a new ContactLists model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
		   if(!PermissionUtils::checkModuleActionPermission("ContactLists",PermissionUtils::CREATE)){
				throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        $model = new ContactLists();

        if ($model->load(Yii::$app->request->post())) {
	$time = microtime(true); // time in Microseconds
				$model->filename = UploadedFile::getInstance($model, 'filename');
			        if ($model->validate()) {
				$time = time();
				 $file = UploadedFile::getInstance($model, 'filename');
				 	$filename = yii::$app->user->identity->clientID."_".yii::$app->user->identity->userID."_".$time. '.' . $file->extension;
                    $model->filename->saveAs('uploads/' .$filename);
                    $model->originalFileName = $file->baseName . '.' . $file->extension;
					$model->generatedFileName =  $filename;
				   $contacts =[];
				 if($file->extension =='csv' )
				 {
                     $handle = fopen("uploads/".$model->generatedFileName, "r");
										
		                     while (($fileop = fgetcsv($handle, 1000, ",")) !== false) 
                     {
					    $phoneNumber = CoreUtils::isValidMobileNo($fileop[0]);
						if($phoneNumber!=0)
						$contacts[] = $phoneNumber;
                     }
				 }
				 elseif($file->extension =='xls' or $file->extension=='xlsx')
				 {
try {
	$data = \moonland\phpexcel\Excel::widget([
  'mode' => 'import', 
  'fileName' => "uploads/".$model->generatedFileName, 
  'setFirstRecordAsKeys' => true, // if you want to set the keys of record column with first record, if it not set, the header with use the alphabet column on excel. 
  'setIndexSheetByName' => true, // set this if your excel data with multiple worksheet, the index of array will be set with the sheet name. If this not set, the index will use numeric. 
  //'getOnlySheet' => 'sheet1', // you can set this property if you want to get the specified sheet from the excel data with multiple worksheet.
 ]);
 //print_r($data);
         foreach ($data as $key=>$value) {//Execute multiple queries
$newArray = array_values($value);
	if(isset($newArray[0]))
	{		$phoneNumber = CoreUtils::isValidMobileNo($newArray[0]);
						if($phoneNumber!=0)
						$contacts[] = $phoneNumber;
	}
        }
} catch (Exception $e) 
{
	throw new BadRequestHttpException($e->getMessage(), 0, $e);
}
}
				
					 //we have removed duplicates now we chaunch it and add some values to it then split it back 
					 
					 $contacts = array_unique($contacts);

   $transaction = Yii::$app->db->beginTransaction();
		if(sizeof($contacts) > 0)
		{
try {
	$savedRecs = 0;
	$model->insertedBy= yii::$app->user->identity->userID;
      $model->updatedBy=  yii::$app->user->identity->userID;
     $model->dateCreated = new Expression('NOW()');
	 $model->active = StatusCodes::ACTIVE;
	  $model->clientID =  yii::$app->user->identity->clientID;
	 if($model->save())
	 {
		 $savedRecs = 0;
		 //we have the numbers now we add the values per chunk 
		 
        $chunks = array_chunk($contacts, 1000);
        foreach ($chunks as $chunk) {//Execute multiple queries
		foreach($chunk as $num)
		$insArr[] = [$model->contactListID,'',$num,yii::$app->user->identity->userID,yii::$app->user->identity->userID,new Expression('NOW()'),StatusCodes::ACTIVE];
		
		            $savedRecs += \Yii::$app->db->createCommand()
                            ->batchInsert("contactListEntries",["contactListID","contactName","MSISDN","insertedBy","updatedBy","dateCreated","active"],$insArr)->execute();
				$insArr =null;
		
        }
        
		/* foreach($contacts as $key=>$value)
		 {
		/* $contactEntries = new ContactListEntries();
		 $contactEntries->contactListID = $model->contactListID;
		 $contactEntries->contactName ='';
		 $contactEntries->MSISDN = $value;
	$contactEntries->insertedBy= yii::$app->user->identity->userID;
      $contactEntries->updatedBy=  yii::$app->user->identity->userID;
     $contactEntries->dateCreated = new Expression('NOW()');
	 $contactEntries->active = StatusCodes::ACTIVE;
	if($contactEntries->save())
	{
		 	 $savedRecs ++;
			
	}
	
	}*/
	if($savedRecs >0)
	{
	  $transaction->commit();
	  	Yii::$app->session->addFlash( 'success',"New List Added   $model->listName  Total Records ". sizeof($contacts) ."  Saved Records  ".$savedRecs . " | " .(microtime(true) - $time) . ' Seconds elapsed'  );
   return $this->redirect(['view', 'id' => $model->contactListID]);
	}
	else
	{
	Yii::$app->session->addFlash( 'error',"Unable to save record  $model->listName  Total Records ". sizeof($contacts) ."  Saved Records  ".$savedRecs. " | ".(microtime(true) - $time) . ' Seconds elapsed' );
		    $transaction->rollBack();

   return $this->redirect(['index']);
	}		 
	 }
	
} catch (Exception $e) 
{
    $transaction->rollBack();
	throw new BadRequestHttpException($e->getMessage(), 0, $e);
}
}
else
{
	Yii::$app->session->addFlash( 'error',"Unable to save record  $model->listName  List is empty");
	
}
}
 }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ContactLists model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
				   if(!PermissionUtils::checkModuleActionPermission("ContactLists",PermissionUtils::UPDATE)){
				throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        $model = $this->findModel($id);
 $model->updatedBy=  yii::$app->user->identity->userID;
 	 $model->filename =$model->generatedFileName;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->contactListID]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ContactLists model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
      			if(!PermissionUtils::checkModuleActionPermission("ContactLists",PermissionUtils::DELETE)){
				throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
      $model =  $this->findModel($id);
      $model->updatedBy=  yii::$app->user->identity->userID;
	 $model->active = StatusCodes::INACTIVE;
	 $model->filename =$model->generatedFileName;
	 if($model->save())
	 	Yii::$app->session->addFlash( 'success',"Service  $model->listName deleted " );
		else
		{
	Yii::$app->session->addFlash( 'error',"Error on deleting $model->listName" );
		
		}

        return $this->redirect(['index']);
    }

    /**
     * Finds the ContactLists model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ContactLists the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
		$model = ContactLists::findOne($id);
	if($model == null)
			throw new NotFoundHttpException('The requested page does not exist.');
	if ((isset(Yii::$app->params['ADMIN_CLIENT_ID']) and  Yii::$app->user->identity->clientID == Yii::$app->params['ADMIN_CLIENT_ID']) or ($model->clientID == Yii::$app->user->identity->clientID))  {
            return $model;
        }
		else
			throw new NotFoundHttpException('The requested page does not exist.');
    	

    }
}
