<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m161126_112634_create_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%user}}', [
            'user_id' => $this->primaryKey(),
            'generated_id' => $this->string(128)->notNull(),
            'user_name' => $this->string(40)->notNUll(),
            'is_judge' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'score' => $this->integer(11)->notNull()->defaultValue(0),
            'last_activity' => $this->timestamp()->notNull()
        ]);

        $this->createIndex(
            'generated_id_unique',
            '{{%user}}',
            'generated_id',
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex(
            'generated_id_unique',
            '{{%user}}'
        );
        $this->dropTable('{{%user}}');
    }
}
