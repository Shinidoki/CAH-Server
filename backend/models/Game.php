<?php

namespace backend\models;

use Yii;

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
 *
 * @property Gamecards[] $gamecards
 * @property Card[] $cards
 * @property Gameusers[] $gameusers
 * @property User[] $users
 */
class Game extends \yii\db\ActiveRecord
{
    const STATE_INLOBBY = 0;
    const STATE_STARTED = 1;
    const STATE_FINISHED = 2;
    const STATE_PAUSED = 3;

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
            [['game_name'], 'required'],
            [['state', 'game_mode', 'target_score', 'kicktimer'], 'integer'],
            [['game_name'], 'string', 'max' => 50],
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
        ];
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
}
