<?php

namespace davidhirtz\yii2\cms\parent\helpers;

use davidhirtz\yii2\cms\models\Entry;
use Yii;

/**
 * Loads and caches the parent entry from the request parameter.
 */
class EntryParentFromRequest
{
    /**
     * @var Entry|false|null
     */
    private static Entry|false|null $_parent = null;

    /**
     * @return Entry|false|null
     */
    public static function getParent(): Entry|false|null
    {
        if (static::$_parent === null) {
            if ($parentId = Yii::$app->getRequest()->get('parent')) {
                static::$_parent = Entry::findOne((int)$parentId) ?? false;
            }
        }

        return static::$_parent;
    }
}