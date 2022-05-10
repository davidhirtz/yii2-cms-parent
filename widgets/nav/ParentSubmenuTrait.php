<?php

namespace davidhirtz\yii2\cms\parent\widgets\nav;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\admin\widgets\nav\Submenu;
use Yii;

/**
 * ParentSubmenuTrait can be used to enhance {@link Submenu} with parent functionality.
 */
trait ParentSubmenuTrait
{
    /**
     * Sets the model from request. This allows to display the entry in views, where no entry was set. Include this in
     * {@see Submenu::init()}.
     *
     * @return Entry
     */
    public function setEntryFromRequest()
    {
        if (!$this->model) {
            if ($parent = Yii::$app->getRequest()->get('parent')) {
                $this->model = Entry::findOne((int)$parent);
            }
        }

        // Remove parent from GET params
        $this->params['parent'] = null;
        return $this->model;
    }

    /**
     * Sets entry's ancestor breadcrumbs. Include this in {@see Submenu::setEntryBreadcrumb()}.
     */
    public function setEntryAncestorBreadcrumbs()
    {
        $model = $this->isSection() ? $this->model->entry : $this->model;
        $view = $this->getView();

        if ($model->parent_id) {
            foreach ($model->getAncestors() as $ancestor) {
                $view->setBreadcrumb($ancestor->getI18nAttribute('name'), ['/admin/entry/update', 'id' => $ancestor->id]);
            }
        }
    }

    /**
     * Adds a link for this entries parent entries. Merge this with other items in {@link Submenu::getEntryFormItems()}.
     * @return array
     */
    public function getEntryParentItems(): array
    {
        $entry = $this->isSection() ? $this->model->entry : $this->model;

        return [
            [
                'label' => Yii::t('cms', 'Entries'),
                'url' => ['/admin/entry/index', 'parent' => $entry->id],
                'icon' => 'book',
                'badge' => $entry->entry_count ?: false,
                'badgeOptions' => [
                    'class' => 'badge d-none d-md-inline-block',
                ],
                'labelOptions' => [
                    'class' => 'd-none d-md-inline'
                ],
                'active' => ['admin/entry/index'],
                'visible' => $entry->hasDescendantsEnabled() || $entry->entry_count,
            ],
        ];
    }
}