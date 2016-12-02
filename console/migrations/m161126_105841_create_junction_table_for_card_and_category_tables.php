<?php

use yii\db\Migration;

/**
 * Handles the creation of table `card_category`.
 * Has foreign keys to the tables:
 *
 * - `card`
 * - `category`
 */
class m161126_105841_create_junction_table_for_card_and_category_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%cardcategory}}', [
            'id' => $this->primaryKey(),
            'card_id' => $this->integer()->notNull(),
            'cat_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `card_id`
        $this->createIndex(
            'idx-cardcategory-card_id',
            '{{%cardcategory}}',
            'card_id'
        );

        // add foreign key for table `card`
        $this->addForeignKey(
            'fk-cardcategory-card_id',
            '{{%cardcategory}}',
            'card_id',
            '{{%card}}',
            'card_id',
            'CASCADE'
        );

        // creates index for column `category_id`
        $this->createIndex(
            'idx-cardcategory-category_id',
            '{{%cardcategory}}',
            'cat_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-cardcategory-category_id',
            '{{%cardcategory}}',
            'cat_id',
            '{{%category}}',
            'cat_id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `card`
        $this->dropForeignKey(
            'fk-cardcategory-card_id',
            '{{%cardcategory}}'
        );

        // drops index for column `card_id`
        $this->dropIndex(
            'idx-cardcategory-card_id',
            '{{%cardcategory}}'
        );

        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-cardcategory-category_id',
            '{{%cardcategory}}'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-cardcategory-category_id',
            '{{%cardcategory}}'
        );

        $this->dropTable('{{%cardcategory}}');
    }
}
