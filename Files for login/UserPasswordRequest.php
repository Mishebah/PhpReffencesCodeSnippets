<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "userPasswordRequest".
 *
 * @property integer $requestID
 * @property string $email
 * @property string $token
 * @property string $IP
 * @property integer $active
 * @property string $dateCreated
 */
class UserPasswordRequest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'userPasswordRequest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'token', 'IP'], 'required'],
            [['active'], 'integer'],
            [['dateCreated'], 'safe'],
            [['email', 'token'], 'string', 'max' => 150],
            [['IP'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'requestID' => Yii::t('app', 'Request ID'),
            'email' => Yii::t('app', 'Email'),
            'token' => Yii::t('app', 'Token'),
            'IP' => Yii::t('app', 'Ip'),
            'active' => Yii::t('app', 'Active'),
            'dateCreated' => Yii::t('app', 'Date Created'),
        ];
    }
}
