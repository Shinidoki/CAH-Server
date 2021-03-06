<?php

namespace backend\models;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property integer $cat_id
 * @property string $name
 *
 * @property Cardcategory[] $cardcategories
 * @property Card[] $cards
 * @property Gamecategories[] $gamecategories
 * @property Game[] $games
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cat_id' => 'Cat ID',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCardcategories()
    {
        return $this->hasMany(Cardcategory::className(), ['cat_id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['card_id' => 'card_id'])->viaTable('{{%cardcategory}}', ['cat_id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGamecategories()
    {
        return $this->hasMany(Gamecategories::className(), ['category_id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGames()
    {
        return $this->hasMany(Game::className(), ['game_id' => 'game_id'])->viaTable('{{%gamecategories}}', ['category_id' => 'cat_id']);
    }
}
