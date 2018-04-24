<?php

use yii\db\Migration;

/**
 * Class m180424_080730_addBlanksOnCards
 */
class m180424_080730_addBlanksOnCards extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\backend\models\Card::tableName(), 'blanks', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\backend\models\Card::tableName(), 'blanks');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180424_080730_addBlanksOnCards cannot be reverted.\n";

        return false;
    }
    */
}
