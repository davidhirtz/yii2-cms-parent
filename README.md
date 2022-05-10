README
============================

[Yii 2](http://www.yiiframework.com/) extensions for [yii2-cms](https://github.com/davidhirtz/yii2-cms/) by David Hirtz.
This extension extends the module to allow entries with hiearchy.

INSTALLATION
-------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```
composer require davidhirtz/yii2-cms-parent
```

Make sure to run the migrations after the installation via `php yii migrate`.

SETUP
-------------

- Extend the default entry model `davidhirtz\yii2\cms\models\Entry`: It must make use of the`davidhirtz\yii2\skeleton\db\MaterializedTreeTrait` trait, otherwise the application will throw an error
- Make sure `app\modules\admin\widgets\forms\EntryActiveForm::$fields` includes `parent_id` to display the entries drop down
- [Optional] Use `davidhirtz\yii2\cms\parent\widgets\grid\columns\EntryCountColumn` to display an entries counter in `davidhirtz\yii2\cms\modules\admin\widgets\grid\EntryGridView`
- [Optional] Use `davidhirtz\yii2\cms\parent\widgets\nav\ParentSubmenuTrait` in `davidhirtz\yii2\cms\modules\admin\widgets\nav\Submenu`, follow the documention of the trait methods

