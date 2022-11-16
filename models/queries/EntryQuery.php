<?php

namespace davidhirtz\yii2\cms\parent\models\queries;

use davidhirtz\yii2\cms\models\Entry;

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
     * Extends the default `whereSlug` method to allow for look-ups via `parent_slug` if the attribute is part of the
     * unique URL target.
     *
     * @param string $slug
     * @return EntryQuery
     */
    public function whereSlug(string $slug)
    {
        if (in_array('parent_slug', Entry::instance()->slugTargetAttribute ?? [])) {
            $slug = explode('/', $slug);

            return $this->andWhere([
                $this->getI18nAttributeName('slug') => array_pop($slug),
                $this->getI18nAttributeName('parent_slug') => implode('/', $slug),
            ]);
        }

        return parent::whereSlug($slug);
    }
}