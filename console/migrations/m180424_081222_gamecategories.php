<?php

use yii\db\Migration;

/**
 * Class m180424_081222_gamecategories
 */
class m180424_081222_gamecategories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%gamecategories}}', [
            'game_id' => $this->integer(11)->notNull(),
            'category_id' => $this->integer(11)->notNull()
        ]);

        $this->addPrimaryKey('PRIMARY_KEY', '{{%gamecategories}}', ['game_id', 'category_id']);

        $this->addForeignKey('fk_category_game', '{{%gamecategories}}', 'game_id', \backend\models\Game::tableName(), 'game_id', 'CASCADE');
        $this->addForeignKey('fk_game_category', '{{%gamecategories}}', 'category_id', \backend\models\Category::tableName(), 'cat_id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%gamecategories}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180424_081222_gamecategories cannot be reverted.\n";

        return false;
    }
    */

}
