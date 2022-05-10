<?php

namespace davidhirtz\yii2\cms\parent\migrations;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\db\MigrationTrait;
use Exception;
use Yii;
use yii\db\Migration;

/**
 * M210528114233Parent adds the necessary columns to {@see Entry}. This migration is not automatically added to the
 * migration namespaces and must be either manually added or extended by a migration on application level.
 */
class M210528114233Parent extends Migration
{
    use MigrationTrait;
    use ModuleTrait;

    /**
     * @inheritDoc
     */
    public function safeUp()
    {
        $schema = $this->getDb()->getSchema();

        foreach ($this->getLanguages() as $language) {
            if ($language) {
                Yii::$app->language = $language;
            }

            $this->addColumn(Entry::tableName(), 'parent_id', $this->integer()->unsigned()->null()->after('type'));
            $this->addColumn(Entry::tableName(), 'path', $this->string()->null()->after('position'));
            $this->addColumn(Entry::tableName(), 'parent_slug', $this->string()->notNull()->defaultValue('')->after('slug'));
            $this->addColumn(Entry::tableName(), 'entry_count', $this->integer()->unsigned()->notNull()->defaultValue(0)->after('category_ids'));

            if ($slugTargetAttribute = (new Entry())->slugTargetAttribute) {
                $this->dropSlugIndex();
                $this->createIndex('slug', Entry::tableName(), $slugTargetAttribute, true);
            }

            $tableName = $schema->getRawTableName(Entry::tableName());
            $this->addForeignKey("{$tableName}_parent_id", Entry::tableName(), 'parent_id', Entry::tableName(), 'id', 'SET NULL');
        }
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        $schema = $this->getDb()->getSchema();

        foreach ($this->getLanguages() as $language) {
            Yii::$app->language = $language;

            $this->dropSlugIndex();

            $tableName = $schema->getRawTableName(Entry::tableName());
            $this->dropForeignKey($tableName . '_parent_id', Entry::tableName());

            $this->dropColumn(Entry::tableName(), 'parent_id');
            $this->dropColumn(Entry::tableName(), 'path');
            $this->dropColumn(Entry::tableName(), 'parent_slug');
            $this->dropColumn(Entry::tableName(), 'entry_count');
        }
    }

    /**
     * Wraps drop index in try/catch block.
     */
    protected function dropSlugIndex()
    {
        try {
            $this->dropIndex('slug', Entry::tableName());
        } catch (Exception $exception) {
        }
    }

    /**
     * @return array
     */
    private function getLanguages()
    {
        return static::getModule()->enableI18nTables ? Yii::$app->getI18n()->getLanguages() : [Yii::$app->language];
    }
}