<?php

use yii\db\Migration;

/**
 * Class m180424_085812_add_is_chosen_for_gamecards
 */
class m180424_085812_add_is_chosen_for_gamecards extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\backend\models\Gamecards::tableName(), 'is_chosen', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\backend\models\Gamecards::tableName(), 'is_chosen');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180424_085812_add_is_chosen_for_gamecards cannot be reverted.\n";

        return false;
    }
    */
}
