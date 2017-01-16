<?php

namespace backend\models;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $user_id
 * @property string $generated_id
 * @property string $user_name
 * @property integer $is_judge
 * @property integer $score
 * @property string $last_activity
 *
 * @property Gamecards[] $gamecards
 * @property Gameusers[] $gameusers
 * @property Game[] $games
 * @property Game[] $hostedGames
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    public static function isValidToken($token)
    {
        $found = self::find()->where(['generated_id' => $token])->one();
        return !empty($found);
    }

    public function beforeValidate()
    {
        if(empty($this->generated_id)){
            $this->generated_id = md5($this->user_name.mt_rand());
        }
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['generated_id', 'user_name'], 'required'],
            [['is_judge', 'score'], 'integer'],
            [['last_activity'], 'safe'],
            [['generated_id'], 'string', 'max' => 128],
            [['user_name'], 'string', 'max' => 40],
            [['generated_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'generated_id' => 'Generated ID',
            'user_name' => 'User Name',
            'is_judge' => 'Is Judge',
            'score' => 'Score',
            'last_activity' => 'Last Activity',
        ];
    }

    /**
     * Removes the user from all games he is in
     */
    public function removeFromAllGames()
    {
        /** @var Gameusers[] $gameUsers */
        $gameUsers = $this->getGameusers()->with('game')->all();
//        VarDumper::dump($gameUsers, 10, true);die;
        foreach ($gameUsers as $gameUser) {
            $gameUser->game->kickUser($gameUser);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGameusers()
    {
        return $this->hasMany(Gameusers::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHostedGames()
    {
        return $this->hasMany(Game::className(), ['host_user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGames()
    {
        return $this->hasMany(Game::className(), ['game_id' => 'game_id'])->viaTable('{{%gameusers}}', ['user_id' => 'user_id']);
    }

    public function updateActivity()
    {
        $this->last_activity = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * @return Gamecards[]
     */
    public function getCurrentChosenCards()
    {
        return $this->getGamecards()->where(['is_chosen' => 1])->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGamecards()
    {
        return $this->hasMany(Gamecards::className(), ['user_id' => 'user_id']);
    }
}
