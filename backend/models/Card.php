<?php

namespace backend\models;

/**
 * This is the model class for table "{{%card}}".
 *
 * @property integer $card_id
 * @property string $text
 * @property integer $is_black
 *
 * @property Cardcategory[] $cardcategories
 * @property Category[] $cats
 * @property Gamecards[] $gamecards
 * @property Game[] $games
 */
class Card extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%card}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'is_black'], 'required'],
            [['is_black'], 'integer'],
            [['text'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'card_id' => 'Card ID',
            'text' => 'Text',
            'is_black' => 'Is Black',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCardcategories()
    {
        return $this->hasMany(Cardcategory::className(), ['card_id' => 'card_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCats()
    {
        return $this->hasMany(Category::className(), ['cat_id' => 'cat_id'])->viaTable('{{%cardcategory}}', ['card_id' => 'card_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGamecards()
    {
        return $this->hasMany(Gamecards::className(), ['card_id' => 'card_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGames()
    {
        return $this->hasMany(Game::className(), ['game_id' => 'game_id'])->viaTable('{{%gamecards}}', ['card_id' => 'card_id']);
    }
}
