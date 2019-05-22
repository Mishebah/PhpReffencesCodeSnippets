<?php
namespace app\models;
use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Clients;
use yii\db\Expression;
use app\components\StatusCodes;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public 	$emailAddress;
    public $repeatemail;
     public $password;
    public $repeatpassword;
     public $clientName;
	      public $MSISDN;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
           
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['emailAddress', 'trim'],
            ['emailAddress', 'required'],
            ['emailAddress', 'email'],
            ['emailAddress', 'string', 'max' => 255],
            ['emailAddress', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],
            
			 ['MSISDN', 'trim'],
            ['MSISDN', 'required'],
            ['MSISDN', 'integer'],
            ['MSISDN', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This mobile number has already been taken. Please enter a new one '],
			
           ['clientName', 'safe'],
            ['clientName', 'required'],
             ['clientName', 'string', 'min' => 2, 'max' => 255],    
	    ['clientName', 'unique', 'targetClass' => 'app\models\Clients', 'message' => 'This organization name has already been taken. Use a new one'],
 
             
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
			 ['repeatpassword', 'required'],
			    ['repeatpassword', 'compare', 'compareAttribute'=>'password', 'skipOnEmpty' => false, 'message'=>"Passwords don't match"],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        
      
        if (!$this->validate()) {
            return null;
        }
        //we save the client first here    [['parentClientID', 'clientName', 'clientCode', 'contactPersonName', 'businessPIN', 'telephoneNo', 'emailAddress', 'subType'], 'required'],
        $client = new Clients();
		$userGroupsmodel = new UserGroups();
			$connection = \Yii::$app->db;
    $transaction = $connection->beginTransaction();
//	try {
         //$client->parentClientID =0;
         $client->clientName = $this->clientName;
        $client->clientCode = $this->username;
         $client->contactPersonName = $this->username;
          $client->businessPIN  = "CLIENT";
              $client->emailAddress = $this->emailAddress;
        //  $client->subType = "CLIENT";
          if($client->save())
          {
			  
        $user = new User();
        $user->clientID = $client->clientID;
        $user->userName = $this->username;
        $user->emailAddress = $this->emailAddress;
	$user->active =User::STATUS_NEW;
        $user->setPassword($this->password);
        $user->generateAuthKey();
		 $user->dateCreated =  new Expression('NOW()');
        
if($user->save())
{
	
		 $userGroupsmodel->insertedBy= 1;
       $userGroupsmodel->updatedBy= 1;
      $userGroupsmodel->dateCreated =  new Expression('NOW()');
	 $userGroupsmodel->active = StatusCodes::ACTIVE;
	 $userGroupsmodel->userID =  $user->userID;
 $userGroupsmodel->groupID  = Yii::$app->params['DEFAULT_SETTINGS']['USER_GROUP']; 
	 	if(!$userGroupsmodel->save())
		{
			//print_r($userGroupsmodel->getErrors());
			Yii::$app->session->addFlash( 'error', "Error Setting new account. try again later");
		$transaction->rollback();
		return null;
		}
		
		$model = new Services();
	$model->insertedBy= 1;
      $model->updatedBy= 1;
     $model->dateCreated = new Expression('NOW()');
	 $model->active = StatusCodes::ACTIVE;
	$model->serviceName = $client->clientName." DEFAULT ACCOUNT"; 
	$model->serviceTypeID =1;
	$model->clientID =$client->clientID;
	$model->dedicated =0;
	$model->sourceAddressID = Yii::$app->params['DEFAULT_SETTINGS']['SOURCEADDR_ID'];
	$model->active = StatusCodes::ACTIVE;
	$model->dateCreated = new Expression('NOW()');
	$model->sdpServiceID= Yii::$app->params['DEFAULT_SETTINGS']['SDP_SERVICE_ID'];
	
	if(!$model->save())
		{
			//print_r($model->getErrors());
			Yii::$app->session->addFlash( 'error', "Error Setting new account. try again later");
		$transaction->rollback();
		return null;
		}
		
$credits = new CreditAllocation();
	$credits->allocatedBy= 1;
      $credits->updatedBy= 1;
	  	$credits->clientID =$client->clientID;
     $credits->dateAllocated = new Expression('NOW()');
 	 	 $credits->creditStatusID = StatusCodes::ACTIVE;
$credits->creditsAllocated=Yii::$app->params['DEFAULT_SETTINGS']['FREE_UNITS'];

	if(!$credits->save())
		{
			print_r($credits->getErrors());
			Yii::$app->session->addFlash( 'error', "Error Setting new account. try again later");
		$transaction->rollback();
		return null;
		}
		
		$transaction->commit();
//we send email here 
 Yii::$app
            ->mailer
            ->compose(
                ['html' => 'activate-html'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name ])
            ->setTo($this->emailAddress)
            ->setSubject('Welcome to ' . Yii::$app->name)
            ->send();
return $user;
}
else
{
 $transaction->rollBack();
   	Yii::$app->session->addFlash('error', "Error Setting new account. try again later");
return null;
}
      //  return $user->save() ? $user : null;
          }
          else
		  {
			  
			   $transaction->rollBack();
   	Yii::$app->session->addFlash('error', "Error Setting new account. try again later");
          return  null;
		  }
	  
            $transaction->commit();

/*	} catch (\Exception $e) {
		
    $transaction->rollBack();
   	Yii::$app->session->addFlash('error', "Error Setting new account. try again later");

} catch (\Throwable $e) {

    $transaction->rollBack();
	//Yii::$app()->session->addFlash('error', "Error Experienced saving transactions".$e->getMessage());
		   Yii::$app->session->addFlash( 'error','Error Setting new account. try again later' );
            
}
*/
    }
	public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'emailAddress' => $this->emailAddress,
        ]);

        if (!$user) {
            return false;
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();
    }
}
