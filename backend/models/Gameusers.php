<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "{{%gameusers}}".
 *
 * @property integer $id
 * @property integer $game_id
 * @property integer $user_id
 *
 * @property User $user
 * @property Game $game
 */
class Gameusers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gameusers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['game_id', 'user_id'], 'required'],
            [['game_id', 'user_id'], 'integer'],
            [['game_id', 'user_id'], 'unique', 'targetAttribute' => ['game_id', 'user_id'], 'message' => 'The combination of Game ID and User ID has already been taken.'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['game_id'], 'exist', 'skipOnError' => true, 'targetClass' => Game::className(), 'targetAttribute' => ['game_id' => 'game_id']],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGame()
    {
        return $this->hasOne(Game::className(), ['game_id' => 'game_id']);
    }
}
