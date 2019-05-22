<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contactLists".
 *
 * @property string $contactListID
 * @property string $listName
 * @property string $clientID
 * @property string $originalFileName
 * @property string $generatedFileName
 * @property string $insertedBy
 * @property string $dateCreated
 * @property int $active
 * @property string $dateActivated
 * @property string $updatedBy
 * @property string $dateModified
 *
 * @property BroadcastLists[] $broadcastLists
 * @property Broadcasts[] $broadcasts
 * @property ContactListEntries[] $contactListEntries
 * @property Clients $client
 * @property Users $insertedBy0
 * @property Users $updatedBy0
 */
class ContactLists extends \yii\db\ActiveRecord
{
	public $filename;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contactLists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
		    [['filename'],'required'],
            [['filename'],'file','checkExtensionByMimeType' => false, 'extensions' => 'csv,xls,xlsx','maxSize'=>1024 * 1024 * 10],
            [['listName' ], 'required'],
            [['clientID', 'active','insertedBy', 'updatedBy'], 'integer'],
            [['dateCreated', 'dateActivated', 'dateModified'], 'safe'],
            [['listName'], 'string', 'max' => 120],
            [['originalFileName', 'generatedFileName'], 'string', 'max' => 100],
            [['listName'], 'unique'],
            [['clientID'], 'exist', 'skipOnError' => true, 'targetClass' => Clients::className(), 'targetAttribute' => ['clientID' => 'clientID']],
            [['insertedBy'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['insertedBy' => 'userID']],
            [['updatedBy'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['updatedBy' => 'userID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'contactListID' => 'Contact List',
            'listName' => 'List Name',
            'clientID' => 'Client',
            'originalFileName' => 'Original File Name',
            'generatedFileName' => 'Generated File Name',
            'insertedBy' => 'Inserted By',
            'dateCreated' => 'Date Created',
            'active' => 'Active',
            'dateActivated' => 'Date Activated',
            'updatedBy' => 'Updated By',
            'dateModified' => 'Date Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBroadcastLists()
    {
        return $this->hasMany(BroadcastLists::className(), ['contactListID' => 'contactListID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBroadcasts()
    {
        return $this->hasMany(Broadcasts::className(), ['broadcastID' => 'broadcastID'])->viaTable('broadcastLists', ['contactListID' => 'contactListID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactListEntries()
    {
        return $this->hasMany(ContactListEntries::className(), ['contactListID' => 'contactListID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Clients::className(), ['clientID' => 'clientID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInsertedBy0()
    {
        return $this->hasOne(Users::className(), ['userID' => 'insertedBy']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy0()
    {
        return $this->hasOne(Users::className(), ['userID' => 'updatedBy']);
    }
}
