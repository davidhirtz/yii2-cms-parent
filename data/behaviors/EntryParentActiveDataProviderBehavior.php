<?php

namespace davidhirtz\yii2\cms\parent\data\behaviors;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\admin\data\EntryActiveDataProvider;
use davidhirtz\yii2\cms\parent\composer\Bootstrap;
use Yii;
use yii\base\Behavior;

/**
 * EntryParentActiveDataProviderBehavior extends {@see EntryActiveDataProvider} by providing a 'parent` property and
 * search via request parameter. This behavior is attached on startup by {@see Bootstrap}.
 *
 * @property EntryActiveDataProvider $owner
 */
class EntryParentActiveDataProviderBehavior extends Behavior
{
    /**
     * @var Entry
     */
    public $parent;

    /**
     * @return array
     */
    public function events(): array
    {
        return [
            EntryActiveDataProvider::EVENT_AFTER_PREPARE => 'onAfterPrepare',
        ];
    }

    /**
     * Limits results to the scope of `parent` unless a text search is performed.
     */
    public function onAfterPrepare()
    {
        if (!$this->owner->searchString) {
            if (!$this->owner->parent) {
                if ($parentId = Yii::$app->getRequest()->get('parent')) {
                    $this->owner->parent = Entry::findOne((int)$parentId);
                }
            }

            $this->owner->query->andWhere(['parent_id' => $this->owner->parent->id ?? null]);
        }
    }
}