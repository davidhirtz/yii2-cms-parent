<?php

namespace davidhirtz\yii2\cms\parent\composer;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\admin\widgets\grid\EntryGridView;
use davidhirtz\yii2\cms\parent\behaviors\EntryParentBehavior;
use davidhirtz\yii2\cms\parent\behaviors\ParentIdFieldBehavior;
use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;

/**
 * Class Bootstrap
 * @package davidhirtz\yii2\cms\shopify\bootstrap
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        Event::on(Entry::class, Entry::EVENT_INIT, function (Event $event) {
            /** @var Entry $entry */
            $entry = $event->sender;
            $entry->attachBehavior('EntryParentBehavior', EntryParentBehavior::class);
        });

        Event::on(EntryGridView::class, EntryGridView::EVENT_INIT, function (Event $event) {
            /** @var EntryGridView $entry */
            $entry = $event->sender;
            $entry->attachBehavior('ParentIdFieldBehavior', ParentIdFieldBehavior::class);
        });

        $app->setMigrationNamespace('davidhirtz\yii2\cms\parent\migrations');
    }
}