<?php

namespace davidhirtz\yii2\cms\parent\widgets\grid\columns;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\admin\widgets\grid\EntryGridView;
use davidhirtz\yii2\skeleton\helpers\Html;
use Yii;
use yii\grid\DataColumn;
use yii\helpers\Url;

/**
 * EntryGridView can be implemented by {@see EntryGridView} to add an entry counter column, which links to the grid
 * view of the selected entries' children.
 *
 * @property EntryGridView $grid
 */
class EntryCountColumn extends DataColumn
{
    /**
     * @var string
     */
    public $attribute = 'entry_count';

    /**
     * @var string[]
     */
    public $headerOptions = [
        'class' => 'd-none d-md-table-cell text-center',
    ];

    /**
     * @var string[]
     */
    public $contentOptions = [
        'class' => 'd-none d-md-table-cell text-center',
    ];

    /**
     * @param Entry $model
     * @param string $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $url = Url::current(['parent' => $model->id, 'type' => null, 'q' => null]);
        return Html::a(Yii::$app->getFormatter()->asInteger($model->getAttribute($key)), $url, ['class' => 'badge']);
    }
}
