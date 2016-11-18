<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "{{%gamecards}}".
 *
 * @property integer $id
 * @property integer $game_id
 * @property integer $user_id
 * @property integer $card_id
 *
 * @property Card $card
 * @property Game $game
 * @property User $user
 */
class Gamecards extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gamecards}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['game_id', 'user_id', 'card_id'], 'required'],
            [['game_id', 'user_id', 'card_id'], 'integer'],
            [['game_id', 'card_id'], 'unique', 'targetAttribute' => ['game_id', 'card_id'], 'message' => 'The combination of Game ID and Card ID has already been taken.'],
            [['card_id'], 'exist', 'skipOnError' => true, 'targetClass' => Card::className(), 'targetAttribute' => ['card_id' => 'card_id']],
            [['game_id'], 'exist', 'skipOnError' => true, 'targetClass' => Game::className(), 'targetAttribute' => ['game_id' => 'game_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'game_id' => 'Game ID',
            'user_id' => 'User ID',
            'card_id' => 'Card ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(Card::className(), ['card_id' => 'card_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGame()
    {
        return $this->hasOne(Game::className(), ['game_id' => 'game_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
}
