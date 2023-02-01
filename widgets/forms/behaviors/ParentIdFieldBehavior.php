<?php

namespace davidhirtz\yii2\cms\parent\widgets\forms\behaviors;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\admin\widgets\forms\EntryActiveForm;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\cms\parent\composer\Bootstrap;
use davidhirtz\yii2\skeleton\helpers\Html;
use Yii;
use yii\base\Behavior;

/**
 * ParentIdFieldTrait can be used by {@see EntryActiveForm} to add a `parent_id` select field. All methods in this
 * behavior can be overridden by the owner. This behavior is attached on startup by {@see Bootstrap}.
 *
 * @property EntryActiveForm $owner
 */
class ParentIdFieldBehavior extends Behavior
{
    use ModuleTrait;

    /**
     * @var array
     */
    private $_parentIdItems = [];

    /**
     * @return string[]
     */
    public function events()
    {
        return [
            EntryActiveForm::EVENT_BEFORE_RUN => 'onBeforeRun',
        ];
    }

    /**
     * Sets default values via GET parameters.
     */
    public function onBeforeRun()
    {
        if ($this->owner->model->getIsNewRecord() && !$this->owner->model->hasErrors()) {
            $this->owner->model->parent_id = Yii::$app->getRequest()->get('parent');
        }
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     * @return string
     */
    public function parentIdField()
    {
        if ($this->owner->model->hasParentEnabled()) {
            if ($entries = $this->owner->findParentEntries()) {
                return $this->owner->field($this->owner->model, 'parent_id')
                    ->dropdownList($this->owner->getParentIdItems($entries), $this->owner->getParentIdOptions($entries));
            }
        }

        return '';
    }

    /**
     * Returns the entries that can be used as parent entries. This can be overridden by the entry's active form class.
     * @return Entry[]
     */
    public function findParentEntries()
    {
        $entries = Entry::find()
            ->select($this->owner->model->getI18nAttributesNames(['id', 'parent_id', 'name', 'path', 'slug', 'parent_slug', 'entry_count']))
            ->whereHasDescendantsEnabled()
            ->orderBy(static::getModule()->defaultEntryOrderBy)
            ->indexBy('id')
            ->all();

        return array_filter($entries, function (Entry $entry) {
            return $entry->hasDescendantsEnabled();
        });
    }

    /**
     * Returns the select options form the parent dropdown. This can be overridden by the entry's active form class.
     *
     * @noinspection PhpUndefinedMethodInspection
     * @param Entry[] $entries
     * @return array
     */
    public function getParentIdOptions($entries)
    {
        $options = [
            'encode' => false,
            'prompt' => ['text' => ''],
        ];

        foreach ($this->owner->model->getI18nAttributeNames('slug') as $language => $attribute) {
            $options['data-form-target'][] = $this->owner->getSlugId($language);
            $options['prompt']['options']['data-value'][] = $this->owner->getSlugBaseUrl($language);
        }

        foreach ($entries as $entry) {
            foreach ($this->owner->model->getI18nAttributeNames('slug') as $language => $attribute) {
                $options['options'][$entry->id]['data-value'][] = $this->owner->getParentIdOptionDataValue($entry, $language);
            }

            if (!$this->owner->model->getIsNewRecord() && in_array($this->owner->model->id, array_merge($entry->getAncestorIds(), [$entry->id]))) {
                $options['options'][$entry->id]['disabled'] = true;
            }
        }

        return $options;
    }

    /**
     * @param Entry $entry
     * @return string
     */
    public function getParentIdOptionDataValue($entry, $language = null)
    {
        return Yii::$app->getI18n()->callback($language, function () use ($entry) {
            return rtrim(Yii::$app->getUrlManager()->createAbsoluteUrl($entry->getRoute()), '/') . '/';
        });
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     * @param Entry[] $entries
     * @param null $parentId
     * @return array
     */
    public function getParentIdItems($entries, $parentId = null)
    {
        foreach ($entries as $entry) {
            if ($entry->parent_id == $parentId) {
                $count = count($entry->getAncestorIds());
                $this->_parentIdItems[$entry->id] = ($count ? ("&nbsp;" . str_repeat("–", $count) . ' ') : '') . Html::encode($entry->getI18nAttribute('name'));

                if ($entry->entry_count) {
                    $this->owner->getParentIdItems($entries, $entry->id);
                }
            }
        }

        return $this->_parentIdItems;
    }
}