<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%log}}`.
 */
class m190619_165412_create_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%log}}', [
            'id' => $this->primaryKey(),
            'ip' => $this->string()->notNull(),
            'date' => $this->dateTime()->notNull(),
            'url' => $this->string()->notNull(),
            'useragent' => $this->string()->notNull(),
            'os' => $this->string(),
            'archi' => $this->string(),
            'browser' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%log}}');
    }
}
