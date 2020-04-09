# LeadVertex DB plugin component
`\Leadvertex\Plugin\Components\Db\Model` - component that is designed for fast and easy work with the model and DB.\
You can create, save, delete and search models in your base.
Also it has commands to manage DB table creation for new models.

## Installation
```shell script
composer require leadvertex/plugin-component-db
```

## Usage
For example, if you need a model to store options for some plugin, you can create a new model class like this:
```php
<?php
require 'vendor/autoload.php';

class OptionsModel extends Leadvertex\Plugin\Components\Db\Model
{
    ...
}
```

After you created a table for model in DB you can start using a new model.
All custom properties, that need to be stored in model is accessible using magic methods.\
Plugin uses [catfan/Meedo](https://github.com/catfan/Medoo "catfan/Meedo") component to connect with specified DB.\
For example if you need to store url and some token in our OptionsModel you can use it like that:
```php
//Initializing Connector
Connector::init($medoo);
Connector::setCompanyId(1);

//Creating a new model, storing some data in it and saving.
$model = new OptionsModel('1');

$model->secretToken = 'secretToken';
$model->requestUrl = 'https://my.plugin.test/';

$model->save();
```

To load this model from a base you need to find it. Once it's done you can read, update and delete it if needed:
```php
//Finding a single model
$model = OptionsModel::findById('1');

//Read data fields
$token = $model->secretToken;
$url = $model->rquestUrl;

//Updating a model
$model->secretToken = 'newSecretToken';
$model->requestUrl = 'https://my.newPlugin.test/';
$model->save();

//Deleting a model
$model->delete();
```

### Advanced usage
So first of all you need to create class inherited from abstract `Model` class.
For example you can do it like this:
```php
<?php
require 'vendor/autoload.php';

class OptionsModel extends Leadvertex\Plugin\Components\Db\Model
{
    ...
}
```

Then you need to initialize static `\Leadvertex\Plugin\Components\Db\Components\Connector` using `Connector::init()`
which accepts an instance of Medoo and then set company id with `Connector::setCompanyId()`.\
Easiest way to use Meedoo in this case - use it with sqlite base. For example you can use it like this:
```php
Connector::init(new Medoo([
    'database_type' => 'sqlite',
    'database_file' => 'path_to_db.db' // File will be created if not exists
]));

Connector::setCompanyId('1'); // All models in selected company should have unique ids
```
Also you can get Medoo DB and company id from initialized connector using `Connector:db()` and `Connector:getCompanyId()` methods.

After that, you need to create a new tables for models in your DB using one of the following console commands:

- `db:create-table-auto` - automatically scan all models in `\Leadvertex\Plugin` namespace and creates a new table for each model, based on its name.
Note, that you can change table name for this model to your desirable variant by overriding `tableName()` method in your model class.
This method always has priority when automatically determining table name:
```php
class OptionsModel extends Leadvertex\Plugin\Components\Db\Model
{
    public static function tableName(): string
    {
        return "myTableName";
    }
}
```

- `db:create-table-manual *table_name*` - creates a single table in your DB with specified `*table_name*`. Note, that you need initialized `Connector` for it to work.

All console commands use [symfony/console](https://github.com/symfony/console "symfony/console") `Command` component.
For example, simple console app in console.php file would look like this: 
```php
<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Leadvertex\Plugin\Components\Db\Commands\CreateTableAutoCommand;
use Leadvertex\Plugin\Components\Db\Commands\CreateTableManualCommand;

$application = new Application();

$application->add(new CreateTableAutoCommand());
$application->add(new CreateTableManualCommand());
$application->run();
```

So at this point you can execute the command in your console. For example, auto creating tables command would look like this:
```shell script
php console.php db:create-table-auto
```

### Model properties
`id` and `feature` fields basically is a part of compound primary key.\

- `id` - `string` id of record in your DB. Can be `null` if you want to auto generate its value (UUID).
- `feature` - `string` feature  of this record. By default has an empty `''` value.

This values can be assigned only during creation of new  instance of the model.\
To get this values from the model use `getId()` and `getFeature()` methods:
```php
// Creating of the new model with id = '1' and feature = 'pluginOptions'
$model = new OptionsModel('1', 'pluginOptions');

$id = $model->getId();                          // = '1'
$feature = $model->getFeature();                // = 'pluginOptions'
```
Or you can create a model with null id and/or feature:
```php
// Creating of the new model with id = '1' and feature = ''
$model = new OptionsModel('1');

$id = $model->getId();                          // = '1'
$feature = $model->getFeature();                // = '' Note, that this is empty string, not null

// Creating of the new model with id = random UUID and feature = 'pluginOptions'
$model = new OptionsModel(null, 'pluginOptions');

$id = $model->getId();                          // = stored UUID
$feature = $model->getFeature();                // = 'pluginOptions'

// Creating of the new model with id = random UUID and feature = ''
$model = new OptionsModel(null);

$id = $model->getId();                          // = stored UUID
$feature = $model->getFeature();                // = '' Note, that this is empty string, not null
```

#### Built-in timestamps
You can get these values by using `getCreatedAt()` and `getUpdatedAt()` methods.
For example, you can use it like this:
```php
// Creating a model.
$model = new OptionsModel('1');

//Getting creating date and last update date.
$createdDate = $model->getCreatedAt();          // Returns a date of creating model. Its value would be automatically assigned as current date and time.
$updatedDate = $model->getUpdatedAt();          // Returns null since the model has not yet been updated. By default this value is null. You need to change it manually with setUpdatedAt() method.

// Saving the model with null-updatedAt value. Loaded model would have null in updatedAt field as well.
$model->save();

//If you want to set new update date to your model you need to use this 
$model->setUpdatedAt(new DateTimeImmutable());
$model->save();
```
If you want to set update time automatically you need to override save() method and change updatedAt value in it to desirable variant.

#### Tags
The model has 3 tag-fields in it. Each tag can be used to describe some property of the model and then used to search for all models with the specified tags.
For example, you can store all tags values like this:
```php
// Creating a new model.
$model = new OptionsModel('1');

// Setting tags values and saving the model.
$model->setTag_1('option');
$model->setTag_2('authentication');
$model->setTag_3('token');

$model->save();
```

You can use them to easily find all models with required tags.\ 

#### Custom fields
All custom fields is stored via under the hood php methods. To use them you can simply access undeclared model field and assign it some value:
```php
$model->userField = 'userValue';
$model->userField2 = 'userValue2';
...
```

To get that values you need to call undeclared fields as well:
```php
$userValue = $model->userField;                 // = 'userValue'
$userValue2 = $model->userField2;               // = 'userValue2'
...
```
For more detailed info about this methods you can visit [this page](https://www.php.net/manual/ru/language.oop5.magic.php "this page").

All custom fields are stored in `data` array. You can use all values that you want, except this types:
    - `resource`
    - anonymous classes and functions
    - some innate PHP-objects. [More detailed info here.](https://www.php.net/manual/en/function.serialize.php "More detailed info here.")

When saving the model, the `data` with all its fields would be stored as serialized value in DB.

### Find models in DB
To find existing models from DB you can choose one of find options:
- `findById()` - finds the model with specified id and feature and returns first result. Feature is optional. Usage example:
```php
// Finding a model with id = '1' and feature = 'pluginOptions'
$model = CustomModelClass::findById(
    '1',                                        // id to find
    'pluginOptions'                             // feature to find
);

// Finding a model with id = '2' and feature = ''
$model = CustomModelClass::findById(
    '2',                                        // id to find
    ''                                          // feature to find
);
```
- `findByIds()` - finds all models with array of specified ids and feature. Feature is optional. Usage example:
```php
// Finding all models with ids '1' and '2' and feature = ''
$models = CustomModelClass::findByIds(
    ['1', '2'],                                 // Array of ids to find
    ''                                          // feature to find
);

// Finding all models with feature 'pluginOptions' regardless of their id
$models = CustomModelClass::findByIds(
    [],                                         // Array of ids to find
    'pluginOptions'                             // feature to find
);
```
- `findMany()` - finds all models with specified feature and all represented tags.
You can leave any of tags blank to ignore this field when finding.
Also you can add Limit and Sort to get data in the desired format.\
Last argument for the method is a `bool` property `inCurrentCompany`, which means that you can specify the search scope for the `findMany()` method.
By default its value is `true`, and it means that `findMany()` would search models only in current company.\
If you set this property to `false`, you can search models in all companies.\
Usage example:
```php
// Finding all models with feature = 'pluginOptions', tag_1 = 'option', tag_2 = 'authentication' OR 'authorization' regardless of tag_3.
// Result would contain 10 models starting from 5th and sorted by ids in ascending order.
$models = CustomModelClass::findMany(
    ['pluginOptions'],                          // Array of features to find
    ['option'],                                 // Array of values to find as tag_1
    ['authentication', 'authorization'],        // Array of values to find as tag_2
    [],                                         // Array of values to find as tag_3
    new Limit('10', '5'),                       // Standard SQL Limit with limit and offset
    new Sort(Sort::BY_ID, Sort::ASC),           // Standard SQL Sort with field and direction. All values describes in Sort class as constants, so you can pick required field and direction.
    false                                       // Setting inCurrentCompany to false. This means that the search will be performed on all models in the database, and not just on the models of the current company.
);
```