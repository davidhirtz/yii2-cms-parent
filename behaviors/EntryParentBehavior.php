<?php

namespace davidhirtz\yii2\cms\parent\behaviors;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\parent\validators\ParentIdValidator;
use davidhirtz\yii2\skeleton\helpers\ArrayHelper;
use davidhirtz\yii2\skeleton\validators\UniqueValidator;
use Yii;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;

/**
 * EntryParentBehavior is needed by {@see Entry} to enhance it with parent id functionality
 *
 * @property Entry $owner
 */
class EntryParentBehavior extends Behavior
{
    public function events()
    {
        return [
            Entry::EVENT_INIT => 'onInit',
            Entry::EVENT_CREATE_VALIDATORS => 'onCreateValidators',
            Entry::EVENT_AFTER_VALIDATE => 'onAfterValidate',
            Entry::EVENT_AFTER_INSERT => 'onAfterSave',
            Entry::EVENT_AFTER_UPDATE => 'onAfterSave',
            Entry::EVENT_BEFORE_DELETE => 'onBeforeDelete',
            Entry::EVENT_AFTER_DELETE => 'onAfterDelete',
        ];
    }

    /**
     * Sets default slug validation targets. If other target attributes are needed, simply override the property
     * {@see Entry::$slugTargetAttribute}, but make sure the database unique key is altered accordingly.
     */
    public function onInit()
    {
        if ($this->owner->slugTargetAttribute === null) {
            $this->owner->slugTargetAttribute = ['slug', 'parent_slug'];
        }
    }

    /**
     * Adds {@see ParentIdValidator} to validators. It's added to the first position so {@link UniqueValidator} can
     * afterwards verify the uniqueness of `slug`.
     */
    public function onCreateValidators()
    {
        $validators = array_merge([new ParentIdValidator()], (array)$this->owner->getValidators());
        $this->owner->getValidators()->exchangeArray($validators);
    }

    /**
     * Rebuilds the `path` after parent was validated.
     */
    public function onAfterValidate()
    {
        if ($this->owner->isAttributeChanged('parent_id')) {
            $this->owner->path = $this->owner->parent ? ArrayHelper::createCacheString(ArrayHelper::cacheStringToArray($this->owner->parent->path, $this->owner->parent_id)) : null;
        }
    }

    /**
     * Rebuilds all children's `path` and `parent_slug`, then recalculates the entry count.
     * @param AfterSaveEvent $event
     */
    public function onAfterSave($event)
    {
        if ($this->owner->entry_count) {
            if (array_key_exists('path', $event->changedAttributes) ||
                array_key_exists('slug', $event->changedAttributes) ||
                array_key_exists('parent_slug', $event->changedAttributes)) {
                foreach ($this->owner->getChildren(true) as $entry) {
                    $entry->path = ArrayHelper::createCacheString(ArrayHelper::cacheStringToArray($this->owner->path, $this->owner->id));
                    $entry->parent_slug = $this->owner->getFormattedSlug();
                    $entry->update();
                }
            }
        }

        if (array_key_exists('parent_id', $event->changedAttributes)) {
            $ancestorIds = ArrayHelper::cacheStringToArray($event->changedAttributes['path'] ?? '', $this->owner->getAncestorIds());

            if ($ancestorIds) {
                foreach ($this->owner::findAll($ancestorIds) as $ancestor) {
                    $ancestor->recalculateEntryCount()->update();
                }
            }
        }
    }

    /**
     * Deletes all children before deleting the entry itself.
     */
    public function onBeforeDelete()
    {
        if ($this->owner->entry_count) {
            foreach ($this->owner->getChildren() as $entry) {
                $entry->setIsBatch(true);
                $entry->delete();
            }
        }
    }

    /**
     * Recalculates all ancestor counters after delete.
     */
    public function onAfterDelete()
    {
        if (!$this->owner->getIsBatch()) {
            if ($this->owner->parent_id) {
                foreach ($this->owner->getAncestors() as $ancestor) {
                    $ancestor->recalculateEntryCount()->update();
                }
            }
        }
    }

    /**
     * @param Entry|null $parent
     */
    public function populateParentRelation($parent)
    {
        $this->owner->populateRelation('parent', $parent);
        $this->owner->parent_id = $parent->id ?? null;
    }

    /**
     * @return Entry
     */
    public function recalculateEntryCount()
    {
        $this->owner->entry_count = $this->owner->findDescendants()->count();
        Yii::debug("New Count {$this->owner->entry_count}");
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getFormattedSlug()
    {
        return substr(trim($this->owner->parent_slug . '/' . $this->owner->slug, '/'), 0, 255);
    }

    /**
     * @return string[]
     */
    public function getExcludedTrailAttributes(): array
    {
        return ['path', 'entry_count'];
    }
}