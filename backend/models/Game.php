<?php

namespace backend\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%game}}".
 *
 * @property integer $game_id
 * @property string $create_date
 * @property string $last_activity
 * @property string $game_name
 * @property integer $state
 * @property integer $game_mode
 * @property integer $target_score
 * @property integer $kicktimer
 * @property integer $host_user_id
 *
 * @property User $hostUser
 * @property Gamecards[] $gamecards
 * @property Card[] $cards
 * @property Gameusers[] $gameusers
 * @property User[] $users
 * @property Gamecategories[] $gamecategories
 * @property Category[] $categories
 */
class Game extends \yii\db\ActiveRecord
{
    const STATE_INLOBBY = 0;
    const STATE_STARTED = 1;
    const STATE_FINISHED = 2;
    const STATE_PAUSED = 3;
    const MAX_PLAYERS = 10;
    const MAX_CARDS = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%game}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_date', 'last_activity'], 'safe'],
            [['game_name', 'host_user_id'], 'required'],
            [['state', 'game_mode', 'target_score', 'kicktimer', 'host_user_id'], 'integer'],
            [['game_name'], 'string', 'max' => 50],
            [['host_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['host_user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'game_id' => 'Game ID',
            'create_date' => 'Create Date',
            'last_activity' => 'Last Activity',
            'game_name' => 'Game Name',
            'state' => 'State',
            'game_mode' => 'Game Mode',
            'target_score' => 'Target Score',
            'kicktimer' => 'Kicktimer',
            'host_user_id' => 'Host User ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGamecategories()
    {
        return $this->hasMany(Gamecategories::className(), ['game_id' => 'game_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['cat_id' => 'category_id'])->viaTable('{{%gamecategories}}', ['game_id' => 'game_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHostUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'host_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGamecards()
    {
        return $this->hasMany(Gamecards::className(), ['game_id' => 'game_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['card_id' => 'card_id'])->viaTable('{{%gamecards}}', ['game_id' => 'game_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFreeCards()
    {
        return $this->hasMany(Card::className(), ['card_id' => 'card_id'])->viaTable('{{%gamecards}}', ['game_id' => 'game_id'], function ($query) {
            /** @var ActiveQuery $query */
            $query->andWhere(['user_id' => NULL]);
        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGameusers()
    {
        return $this->hasMany(Gameusers::className(), ['game_id' => 'game_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['user_id' => 'user_id'])->viaTable('{{%gameusers}}', ['game_id' => 'game_id']);
    }

    /**
     * Get only the data that clients are allowed to see
     *
     * @return User[]
     */
    public function getCensoredUsers()
    {
        return $this->getUsers()->select(['user_id','user_name','is_judge','score','last_activity'])->all();
    }


}
