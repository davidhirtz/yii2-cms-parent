<?php

namespace davidhirtz\yii2\cms\parent\models\queries;

/**
 * EntryQuery extends the entry query class by allowing slug look-ups via `parent_slug`. This file must be manually
 * added to the project.
 */
class EntryQuery extends \davidhirtz\yii2\cms\models\queries\EntryQuery
{
    /**
     * @return $this
     */
    public function selectSitemapAttributes()
    {
        return parent::selectSitemapAttributes()->addSelect('parent_slug');
    }

    /**
     * @return $this
     */
    public function whereHasDescendantsEnabled()
    {
        return $this;
    }

    /**
     * @param string $slug
     * @return EntryQuery
     */
    public function whereSlug(string $slug)
    {
        $slug = explode('/', $slug);

        return parent::whereSlug(array_pop($slug))
            ->andWhere(['parent_slug' => implode('/', $slug)]);
    }
}