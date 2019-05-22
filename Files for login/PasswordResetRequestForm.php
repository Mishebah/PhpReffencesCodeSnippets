<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $emailAddress;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['emailAddress', 'trim'],
            ['emailAddress', 'required'],
            ['emailAddress', 'email'],
            ['emailAddress', 'exist',
                'targetClass' => '\app\models\User',
                'filter' => ['active' => User::STATUS_ACTIVE],
                'message' => 'There is no user with such emailAddress.'
            ],
        ];
    }

    /**
     * Sends an emailAddress with a link, for resetting the password.
     *
     * @return bool whether the emailAddress was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'active' => User::STATUS_ACTIVE,
            'emailAddress' => $this->emailAddress,
        ]);
		
        if (!$user) {
            return false;
        }
        if (!User::isPasswordResetTokenValid($user->auth_key)) {
			
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        return Yii::$app
            ->mailer
            ->compose(
//                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
               ['html' => 'passwordResetToken-html'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->emailAddress)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();
    }
}
