<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "{{%cardcategory}}".
 *
 * @property integer $id
 * @property integer $card_id
 * @property integer $cat_id
 *
 * @property Category $cat
 * @property Card $card
 */
class Cardcategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cardcategory}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_id', 'cat_id'], 'required'],
            [['card_id', 'cat_id'], 'integer'],
            [['card_id', 'cat_id'], 'unique', 'targetAttribute' => ['card_id', 'cat_id'], 'message' => 'The combination of Card ID and Cat ID has already been taken.'],
            [['cat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['cat_id' => 'cat_id']],
            [['card_id'], 'exist', 'skipOnError' => true, 'targetClass' => Card::className(), 'targetAttribute' => ['card_id' => 'card_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card_id' => 'Card ID',
            'cat_id' => 'Cat ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCat()
    {
        return $this->hasOne(Category::className(), ['cat_id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(Card::className(), ['card_id' => 'card_id']);
    }
}
