<?php

use yii\db\Migration;

/**
 * Handles the creation of table `card`.
 */
class m161126_104910_create_card_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%card}}', [
            'card_id' => $this->primaryKey(),
            'text' => $this->string()->notNull(),
            'is_black' => $this->smallInteger(1)->notNull()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%card}}');
    }
}
