<?php
namespace app\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\LoginForm;
use app\models\User;
use yii\web\ForbiddenHttpException;

use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use app\models\SignupForm;
use app\models\ContactForm;
use app\models\RequestForm;

use app\components\PermissionUtils;
use app\models\BPayments;
use app\models\CPayments;
use yii\web\UploadedFile;

/**
 * Site controller
 */
class SiteController extends Controller
{
	   public $layout = 'site';
	   
	
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout','login','index','terms', 'contact','signup','captcha','passwordreset','setpassword'],
                'rules' => [
                    [
                        'actions' => ['signup','contact','captcha','index','terms','login','passwordreset','setpassword'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
					
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
	 	$this->layout = 'site';
		 $model = new SignupForm();
        //return $this->render('index');
		        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
		
	  //return $this->goHome();
        if (!Yii::$app->user->isGuest) {
			
          return $this->redirect(['broadcasts/']);
        }
		
	$this->layout = 'site';
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
			//we set the modules here 
			//PermissionUtils::setUserModules();
		
			        return $this->redirect(['broadcasts/']);

           // return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
      
    }

    /**
     * Logs out the current user.
     *
     
* @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
	 	$this->layout = 'site';
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->addFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->addFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            	//$this->layout = 'login';
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }
    public function actionRequest()
    {
	 	$this->layout = 'main';
        $model = new RequestForm();
        if ($model->load(Yii::$app->request->post())  ) {
			$model->auth_file = UploadedFile::getInstance($model, 'auth_file');
$model->kyc_file = UploadedFile::getInstance($model, 'kyc_file');
if($model->validate())
{
            if ($model->sendEmail("ggatuma@gmail.com")) {
                Yii::$app->session->addFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->addFlash('error', 'There was an error sending email.');
            }
			 return $this->refresh();
} else {
                Yii::$app->session->addFlash('error', 'There was an error sending email.');
				        return $this->render('request', [
                'model' => $model,
            ]);
            }

           
        } else {
            	//$this->layout = 'login';
            return $this->render('request', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
			$this->layout = 'site';
        return $this->render('about');
    }
    public function actionTerms()
    {
			$this->layout = 'site';
        return $this->render('terms');
    }
    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
               // print_r($user);die;
//	                Yii::$app->session->addFlash('success', 'Check your email for further instructions.');
//                if (Yii::$app->getUser()->login($user)) { Was good for autologin now its not
                    //return $this->goHome();
     //   $this->layout = 'login';
		             Yii::$app->session->addFlash('success', 'Check your email for further instructions to activate your acction');
          return $this->render('confirm', [
            'model' => $model,
	    'name'=> 'Reset Password',
	    'message'=>'Check your email for further instructions on how to set your password '
        ]);
					 
		 /*return $this->render('confirm', [
            'model' => $model,
	    'name'=> 'Activate New Account',
	    'message'=>'Hi, We have sent an activation link to your email account '
        ]);
		*/
                //}
            }
			else
			{
				$model->addError('emailAddress','The email address has been taken, enter a new one');
			}
        }
		
		
		$this->layout = 'site';
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionPasswordreset()
    {
        $model = new PasswordResetRequestForm();
if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
             Yii::$app->session->addFlash('success', 'Check your email for further instructions on how to set your password');
  //$this->layout = 'login';
          return $this->render('confirm', [
            'model' => $model,
	    'name'=> 'Reset Password',
	    'message'=>'Check your email for further instructions on how to set your password '
        ]);
	
	

            } else {				
                Yii::$app->session->addFlash('error', 'Sorry, we are unable to reset password for email provided.');
		           }


        }


	//$this->layout = 'login';
        return $this->render('passwordReset', [
            'model' => $model,
        ]);


    }
     /*
	Action activate account 
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
public function actionActivate($token)
    {
	//	$this->layout = 'login';
 try {

 if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('Activation token cannot be blank.');
   }
$user = User::findByAuthKey($token);
        if (!$user) {
            throw new InvalidParamException('Wrong activation token.');
        }
	$user->removeAuthKey();
	$user->activateUser();
	if($user->save())
	{
		 Yii::$app
            ->mailer
            ->compose(
                ['html' => 'confirm-html'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name ])
            ->setTo($user->emailAddress)
            ->setSubject('Welcome to ' . Yii::$app->name)
            ->send();
			
    
	}
	else
        throw new InvalidParamException('Request was unsuccessful. Try again.');



//we now update and go home
 } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }


}
    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionSetpassword($token)
    {
		//$this->layout = 'login';
        try {
            $model = new ResetPasswordForm($token);
			
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->addFlash('success', 'New password has been Set. Login with your new Password');
                            return $this->render('confirm', [
            'model' => $model,
	    'name'=> 'Activate Account',
	    'message'=>'New password has been Set. Login with your new Password'
        ]);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
