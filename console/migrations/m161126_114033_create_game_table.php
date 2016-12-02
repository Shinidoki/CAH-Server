<?php

use yii\db\Migration;

/**
 * Handles the creation of table `game`.
 */
class m161126_114033_create_game_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%game}}', [
            'game_id' => $this->primaryKey(),
            'game_name' => $this->string(50)->notNull(),
            'state' => $this->integer(11)->notNull()->defaultValue(1),
            'game_mode' => $this->integer(11)->notNull()->defaultValue(0),
            'target_score' => $this->integer(11),
            'kicktimer' => $this->integer(11),
            'host_user_id' => $this->integer(11)->notNull(),
            'create_date' => $this->timestamp().' DEFAULT NOW()',
            'last_activity' => $this->timestamp()
        ]);

        $this->addForeignKey(
            'fk-game-host_user_id',
            '{{%game}}',
            'host_user_id',
            '{{%user}}',
            'user_id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey(
            'fk-game-host_user_id',
            '{{%game}}'
        );
        $this->dropTable('{{%game}}');
    }
}
