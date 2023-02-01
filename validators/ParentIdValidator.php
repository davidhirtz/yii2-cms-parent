<?php

namespace davidhirtz\yii2\cms\parent\validators;

use davidhirtz\yii2\cms\parent\models\behaviors\EntryParentBehavior;
use davidhirtz\yii2\cms\models\Entry;
use yii\base\NotSupportedException;
use yii\validators\Validator;

/**
 * ParentIdValidator validates the entry's `parent_id`. The validator is automatically added to the model's validators
 * by {@see EntryParentBehavior}.
 */
class ParentIdValidator extends Validator
{
    /**
     * @var array|string defaults to `parent_id`
     */
    public $attributes = ['parent_id'];

    /**
     * Makes sure the validator always is checked even if `parent_id` is empty.
     */
    public function init()
    {
        $this->skipOnEmpty = false;
        parent::init();
    }

    /**
     * Validates the `parent_id` and sets `parent_slug` if possible.
     *
     * @param Entry $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $model->setAttribute($attribute, $model->hasParentEnabled() && $model->getAttribute($attribute) ? (int)$model->getAttribute($attribute) : null);

        if ($model->isAttributeChanged($attribute)) {
            if ($parentId = $model->getAttribute($attribute)) {
                if ((!$model->parent || $model->parent->id != $parentId) && !$model->refreshRelation('parent')) {
                    $model->addInvalidAttributeError($attribute);
                } else {
                    foreach ($model->getI18nAttributeNames('parent_slug') as $language => $attributeName) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $model->{$attributeName} = $model->parent->getFormattedSlug($language);
                    }
                }
            }
        }

        if (!$model->getAttribute($attribute)) {
            foreach ($model->getI18nAttributeNames('parent_slug') as $attributeName) {
                $model->{$attributeName} = '';
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate($value, &$error = null)
    {
        throw new NotSupportedException(get_class($this) . ' does not support validate().');
    }
}