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
    const STATE_END_OF_ROUND = 2;
    const STATE_FINISHED = 3;
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

    public function translateState($state)
    {
        if ($state == self::STATE_INLOBBY) {
            return "In Lobby";
        } elseif ($state == self::STATE_STARTED) {
            return "Started";
        } elseif ($state == self::STATE_END_OF_ROUND) {
            return "End of Round";
        } elseif ($state == self::STATE_FINISHED) {
            return "Finished";
        }
        return "Undefined State!";
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
    public function getHostUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'host_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFreeCards()
    {
        return $this->hasMany(Card::className(), ['card_id' => 'card_id'])->viaTable('{{%gamecards}}', ['game_id' => 'game_id'], function($query){
            /** @var ActiveQuery $query */
            $query->andWhere(['user_id' => NULL]);
        });
    }

    /**
     * Get only the data that clients are allowed to see
     *
     * @return User[]
     */
    public function getCensoredUsers()
    {
        return $this->getUsers()->select(['user_id', 'user_name', 'is_judge', 'score', 'last_activity'])->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['user_id' => 'user_id'])->viaTable('{{%gameusers}}', ['game_id' => 'game_id']);
    }

    /**
     * Update the activity timestamp
     * @return bool
     */
    public function updateActivity()
    {
        $this->last_activity = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Starts the game.
     */
    public function start()
    {
        $this->state = self::STATE_STARTED;
        /** @var Category[] $cats */
        $cats = $this->getCategories()->with('cards')->all();
        $batchinsert = [];
        foreach ($cats as $category)
        {
            $cards = $category->cards;
            foreach ($cards as $card)
            {
                $batchinsert[] = [
                    'game_id' => $this->game_id,
                    'user_id' => NULL,
                    'card_id' => $card->card_id
                ];
            }
        }
        if (!empty($batchinsert))
        {
            self::getDb()->createCommand()->batchInsert(Gamecards::tableName(), array_keys($batchinsert[0]), $batchinsert)->query();
        }
        $this->save();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['cat_id' => 'category_id'])->viaTable('{{%gamecategories}}', ['game_id' => 'game_id']);
    }

    /**
     * @return null|User
     */
    public function getJudge()
    {
        return $this->getUsers()->where(['is_judge' => 1])->one();
    }

    /**
     * @return null|Card
     */
    public function getCurrentBlackCard()
    {
        return $this->getCards()->joinWith('gamecards')->where(['is_black' => 1, 'game_id' => $this->game_id])->andWhere(['IS NOT', 'user_id', NULL])->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['card_id' => 'card_id'])->viaTable('{{%gamecards}}', ['game_id' => 'game_id']);
    }

    /**
     * Kicks all players that haven't reacted for a specified time
     */
    public function timeOutUsers()
    {
        $timeOut = date('Y-m-d H:i:s', strtotime("-{$this->kicktimer} seconds"));
        /** @var Gameusers[] $kickedUsers */
        $kickedUsers = $this->getGameusers()->joinWith('user')->where(['<', 'last_activity', $timeOut])->all();
        if (!empty($kickedUsers)) {
            foreach ($kickedUsers as $gameUser) {
                $this->kickUser($gameUser);
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGameusers()
    {
        return $this->hasMany(Gameusers::className(), ['game_id' => 'game_id']);
    }

    /**
     * Kicks a User from the game and determines a new judge if this player was the judge
     *
     * @param Gameusers $gameUser
     */
    public function kickUser($gameUser)
    {
        $newJudge = false;
        if ($gameUser->user->is_judge == 1) {
            $newJudge = true;
            /** @var Gamecards $blackCard */
            $blackCard = $this->getGamecards()->joinWith('card')->where(['is_black' => 1, 'user_id' => $gameUser->user_id])->one();
            if (!empty($blackCard)) {
                $blackCard->user_id = NULL;
                $blackCard->save();
            }
        }
        Gamecards::deleteAll(['user_id' => $gameUser->user_id]);

        $gameUser->delete();
        if ($newJudge) {
            $this->selectNewJudge();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGamecards()
    {
        return $this->hasMany(Gamecards::className(), ['game_id' => 'game_id']);
    }

    /**
     * Selects a new judge
     * TODO: Right now it's always the first player that gets returned from the query. Needs to select the next player instead
     */
    public function selectNewJudge()
    {
        if (empty($this->gameusers)) {
            $this->delete();
            return;
        }
        $this->gameusers[0]->user->is_judge = 1;
        $this->gameusers[0]->user->save();
        if (!empty($blackCard)) {
            $blackCard->user_id = $this->gameusers[0]->user->user_id;
            $blackCard->save();
        }
    }
}