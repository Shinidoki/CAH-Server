<?php

use yii\db\Migration;

/**
 * Handles the creation of table `game_card`.
 * Has foreign keys to the tables:
 *
 * - `game`
 * - `card`
 */
class m161126_120555_create_junction_table_for_game_and_card_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%gamecards}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer(11),
            'user_id' => $this->integer(11),
            'card_id' => $this->integer(11)
        ]);

        // creates index for column `game_id`
        $this->createIndex(
            'idx-gamecards-game_id',
            '{{%gamecards}}',
            'game_id'
        );

        // add foreign key for table `game`
        $this->addForeignKey(
            'fk-gamecards-game_id',
            '{{%gamecards}}',
            'game_id',
            '{{%game}}',
            'game_id',
            'CASCADE'
        );

        // creates index for column `card_id`
        $this->createIndex(
            'idx-gamecards-card_id',
            '{{%gamecards}}',
            'card_id'
        );

        // add foreign key for table `card`
        $this->addForeignKey(
            'fk-gamecards-card_id',
            '{{%gamecards}}',
            'card_id',
            '{{%card}}',
            'card_id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            'idx-gamecards-user_id',
            '{{%gamecards}}',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-gamecards-user_id',
            '{{%gamecards}}',
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
            'fk-gamecards-game_id',
            '{{%gamecards}}'
        );

        // drops index for column `game_id`
        $this->dropIndex(
            'idx-gamecards-game_id',
            '{{%gamecards}}'
        );

        // drops foreign key for table `card`
        $this->dropForeignKey(
            'fk-gamecards-card_id',
            '{{%gamecards}}'
        );

        // drops index for column `card_id`
        $this->dropIndex(
            'idx-gamecards-card_id',
            '{{%gamecards}}'
        );

        // drops foreign key for table `card`
        $this->dropForeignKey(
            'fk-gamecards-user_id',
            '{{%gamecards}}'
        );

        // drops index for column `card_id`
        $this->dropIndex(
            'idx-gamecards-user_id',
            '{{%gamecards}}'
        );

        $this->dropTable('{{%gamecards}}');
    }
}
