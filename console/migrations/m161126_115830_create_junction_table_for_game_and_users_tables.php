<?php

use yii\db\Migration;

/**
 * Handles the creation of table `game_users`.
 * Has foreign keys to the tables:
 *
 * - `game`
 * - `users`
 */
class m161126_115830_create_junction_table_for_game_and_users_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%gameusers}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer(11),
            'user_id' => $this->integer(11),
        ]);

        // creates index for column `game_id`
        $this->createIndex(
            'idx-gameusers-game_id',
            '{{%gameusers}}',
            'game_id'
        );

        // add foreign key for table `game`
        $this->addForeignKey(
            'fk-gameusers-game_id',
            '{{%gameusers}}',
            'game_id',
            '{{%game}}',
            'game_id',
            'CASCADE'
        );

        // creates index for column `users_id`
        $this->createIndex(
            'idx-gameusers-user_id',
            '{{%gameusers}}',
            'user_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-gameusers-user_id',
            '{{%gameusers}}',
            'user_id',
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
        // drops foreign key for table `game`
        $this->dropForeignKey(
            'fk-gameusers-game_id',
            '{{%gameusers}}'
        );

        // drops index for column `game_id`
        $this->dropIndex(
            'idx-gameusers-game_id',
            '{{%gameusers}}'
        );

        // drops foreign key for table `users`
        $this->dropForeignKey(
            'fk-gameusers-user_id',
            '{{%gameusers}}'
        );

        // drops index for column `users_id`
        $this->dropIndex(
            'idx-gameusers-user_id',
            '{{%gameusers}}'
        );

        $this->dropTable('{{%gameusers}}');
    }
}
