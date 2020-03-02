# LeadVertex DB plugin component
`\Leadvertex\Plugin\Components\Db\Model` - component that is designed for flexible work with the model and DB.\
You can create, save, delete and search models in your base.
Also it has commands to manage DB table creation for new models.
It uses [catfan/Meedo component](https://github.com/catfan/Medoo "catfan/Meedo component") to connect with specified DB.

## Installation
```shell script
composer require leadvertex/plugin-component-db
```

## Usage
For example, in your plugin app you can create a new model class like this:
```php
<?php
require 'vendor/autoload.php';

use Leadvertex\Plugin\Components\Db\Model;

class CustomModel extends Model
{
    public static function tableName(): string
    {
        return "CustomTable";
    }
}
```
Note, that you can change table name for this model to your desirable variant by overriding tableName() method.

Then you need to initialize static `\Leadvertex\Plugin\Components\Db\Components\Connector` using `Connector::init()`
which accepts an instance of Medoo and company id.\
For example you can use it like this:
```php
Connector::init($medoo, $companyId);
```
Also you can get Medoo DB and company id from initialized connector using `Connector:db()` and `Connector:companyId()` methods.

At this moment you need to create a new tables for models in your DB using one of the following console commands:

- `db:create-table-auto` - automatically searches all models in `\Leadvertex\Plugin` namespace and creates creates a new table for each model, based on its name.
- `db:create-table-manual *table_name*` - creates a single table in your DB with specified `*table_name*`. Note, that you need initialized `Connector` for it to work.

All console commands implements [symfony/console](https://github.com/symfony/console "symfony/console") `Command` component.

### Fixed model properties
Model has a number of fixed properties that you can't modify by yourself:

- `id` - `string` id of record in your DB. Can be `null`.
- `feature` - `string` feature  of this record. By default has an empty `''` value.

`Feature` and `id` fields basically is a part of one id of specific record.\
This values can be assigned only during creation of new  instance of the model.\
To get this values from the model use `getId()` and `getFeature()` methods.

- `createdAt` - `DateTime` of model creation.
- `updatedAt` - `DateTime` of last saved model change.
- `isNew` - determines whether this is a new model or loaded from DB.

### Variable model properties
- `tag_1` - `string` tag #1 for record.
- `tag_2` - `string` tag #2 for record.
- `tag_3` - `string` tag #3 for record.

You can use them to easily search all models with required tags.\
To set or get these properties you need to use getTag_№() and setTag_№() where № is a number of desired tag. 

- `data` - an array of variable fields, that you can modify as you want. You can use all values that you want, except this types:
    - `resource`
    - anonymous classes and functions
    - some innate PHP-objects. [More detailed info here.](https://www.php.net/manual/en/function.serialize.php "More detailed info here.")

To set data value you need to use magic `__set()` method in a model:
```php
$model->userField = $userValue;
$model->userField2 = $userValue2;
...
```
The same goes for getting data values - you need to use magic `__get()` method in a model:
```php
$userValue = $model->userField;
$userValue2 = $model->userField2;
...
```
On model save `data` with all its fields would be stored as json in DB.

### Loading models from DB
To load existing models from DB you can choose one of search options:
- `findById()` - searches the model with specified id and feature and returns first result. Feature is optional. Usage example:
```php
$model = CustomModelClass::findById($id, $feature);
```
- `findByIds()` - searches all models with array of specified ids and feature. Feature is optional. Usage example:
```php
$models = CustomModelClass::findByIds([$oldId, $newId], $feature);
```
- `findMany()` - searches all models with specified feature and all represented tags.
You can leave any of tags blank to ignore this field when searching.
Also you can add Limit and Sort to get data in the desired format. Usage example:
```php
$models = CustomModelClass::findMany([$feature], [$tag_1, 'myTag'], (array) $tags, [], new Limit($limit, $offset), new Sort(Sort::BY_ID, Sort::ASC));
```

### Saving models to DB
When you fill all required fields you can save the model to DB by calling `$model->save();` method.
Also you can update model if it was previously loaded from DB with same method.

### Deleting models from DB
If you need to delete any model from DB, you need to load it and call `$model->delete()` method.