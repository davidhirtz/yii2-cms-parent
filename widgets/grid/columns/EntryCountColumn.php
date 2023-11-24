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
     * @var bool|null if set to null the column will only show if at least one model has descendants enabled
     */
    public $visible = null;

    /**
     * @return void
     */
    public function init()
    {
        if ($this->visible === null) {
            $this->visible = false;

            foreach ($this->grid->dataProvider->getModels() as $model) {
                if ($model->hasDescendantsEnabled()) {
                    $this->visible = true;
                    break;
                }
            }
        }

        parent::init();
    }

    /**
     * @param Entry $model
     * @param string $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $url = Url::current(['parent' => $model->id, 'type' => null, 'q' => null]);
        return Html::a(Yii::$app->getFormatter()->asInteger($this->getDataCellValue($model, $key, $index)), $url, ['class' => 'badge']);
    }
}
