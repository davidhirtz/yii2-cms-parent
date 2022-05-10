<?php

namespace davidhirtz\yii2\cms\parent\widgets\forms\behaviors;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\admin\widgets\forms\EntryActiveForm;
use davidhirtz\yii2\cms\parent\composer\Bootstrap;
use davidhirtz\yii2\skeleton\helpers\Html;
use Yii;
use yii\base\Behavior;

/**
 * ParentIdFieldTrait can be used by {@see EntryActiveForm} to add a `parent_id` select field. This behavior is
 * attached on startup by {@see Bootstrap}.
 *
 * @property EntryActiveForm $owner
 */
class ParentIdFieldBehavior extends Behavior
{
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
     * @return string
     */
    public function parentIdField()
    {
        if (!$this->owner->model->hasParentEnabled()) {
            return '';
        }

        $entries = Entry::find()
            ->select(['id', 'parent_id', 'name', 'path', 'slug', 'parent_slug', 'entry_count'])
            ->replaceI18nAttributes()
            ->hasDescendantsEnabled()
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->all();

        if ($entries) {
            $options = [
                'data-form-target' => [$this->owner->getSlugId()],
                'encode' => false,
                'prompt' => [
                    'text' => '',
                    'options' => ['data-value' => $this->owner->getSlugBaseUrl()],
                ],
            ];

            foreach ($entries as $entry) {
                $options['options'][$entry->id]['data-value'] = Yii::$app->getUrlManager()->createAbsoluteUrl($entry->getRoute()) . '/';

                if (!$this->owner->model->getIsNewRecord() && in_array($this->owner->model->id, array_merge($entry->getAncestorIds(), [$entry->id]))) {
                    $options['options'][$entry->id]['disabled'] = true;
                }
            }

            /** @noinspection PhpUndefinedMethodInspection */
            return $this->owner->field($this->owner->model, 'parent_id')->dropdownList($this->owner->getParentIdItems($entries), $options);
        }

        return '';
    }

    /**
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
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->owner->getParentIdItems($entries, $entry->id);
                }
            }
        }

        return $this->_parentIdItems;
    }
}