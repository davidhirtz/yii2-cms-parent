<?php

namespace davidhirtz\yii2\cms\parent\composer;

use davidhirtz\yii2\cms\models\Entry;
use davidhirtz\yii2\cms\modules\admin\data\EntryActiveDataProvider;
use davidhirtz\yii2\cms\modules\admin\widgets\forms\EntryActiveForm;
use davidhirtz\yii2\cms\parent\data\behaviors\EntryParentActiveDataProviderBehavior;
use davidhirtz\yii2\cms\parent\models\behaviors\EntryParentBehavior;
use davidhirtz\yii2\cms\parent\widgets\forms\behaviors\ParentIdFieldBehavior;
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

        Event::on(EntryActiveForm::class, EntryActiveForm::EVENT_INIT, function (Event $event) {
            /** @var EntryActiveForm $form */
            $form = $event->sender;
            $form->attachBehavior('ParentIdFieldBehavior', ParentIdFieldBehavior::class);
        });

        Event::on(EntryActiveDataProvider::class, EntryActiveDataProvider::EVENT_INIT, function (Event $event) {
            /** @var EntryActiveDataProvider $provider */
            $provider = $event->sender;
            $provider->attachBehavior('EntryParentActiveDataProviderBehavior', EntryParentActiveDataProviderBehavior::class);
        });

        $app->setMigrationNamespace('davidhirtz\yii2\cms\parent\migrations');
    }
}