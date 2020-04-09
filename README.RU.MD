# LeadVertex DB плагин
`\Leadvertex\Plugin\Components\Db\Model` - компонент, разработанный для простой и быстрой работы с моделью и БД.\
Компонент позволяет создавать, сохранять, удалять и искать модели в БД.
Также в состав компонента входят команды для создания таблиц для новых моделей. 

## Установка
```shell script
composer require leadvertex/plugin-component-db
```

## Использование
Для примера, если необходимо создать модель для хранения настроек для какого-либо плагина, вы можете создать новую модель таким образом:
```php
<?php
require 'vendor/autoload.php';

class OptionsModel extends Leadvertex\Plugin\Components\Db\Model
{
    ...
}
```
После создания таблицы в БД для модели вы можете начать использовать новую модель.
Все пользовательские свойства, которые необходимо сохранить в модели можно присвоить используя 'magic' методы. \
Плагин использует [catfan/Meedo](https://github.com/catfan/Medoo "catfan/Meedo") компонент для соединения с указанной БД.\
Для примера, если вам необходимо сохранить url и токен в OptionsModel, вы можете сделать это таким образом:
```php
//Инициалиация Connector
Connector::init($medoo);
Connector::setCompanyId(1);

//Создание новой модели, запись данных в поля и сохранение в БД.
$model = new OptionsModel('1');

$model->secretToken = 'secretToken';
$model->requestUrl = 'https://my.plugin.test/';

$model->save();
```

Для загрузки этой модели необходимо отправить запрос на ее поиск в БД. Если модель будет найдена, вы сможете считать, обновить и удалить ее при надобности: 
```php
//Поиск одной модели по ее id
$model = OptionsModel::findById('1');

//Чтение полей модели
$token = $model->secretToken;
$url = $model->rquestUrl;

//Обновление модели
$model->secretToken = 'newSecretToken';
$model->requestUrl = 'https://my.newPlugin.test/';
$model->save();

//Удаление модели
$model->delete();
```

### Расширенное использование
Для начала необходимо создать класс, наследуемый от абстрактного класса `Model`.
Для примера, вы можете сделать это таким образом:
```php
<?php
require 'vendor/autoload.php';

class OptionsModel extends Leadvertex\Plugin\Components\Db\Model
{
    ...
}
```

После этого, необходимо инициализировать статичный `\Leadvertex\Plugin\Components\Db\Components\Connector` используя метод `Connector::init()`,
который принимает экземпляр `Meedoo`, после чего установить id компании, используя метод `Connector::setCompanyId()`.\
наиболее простой способ использования `Meedoo` в этом случае - использовать sqlite базу. Для примера, вы можете создать подключение таким образом:
```php
Connector::init(new Medoo([
    'database_type' => 'sqlite',
    'database_file' => 'path_to_db.db' // Файл будет создан, если не существует
]));

Connector::setCompanyId('1'); // Каждая модель в указанной компании имеет уникальный id
```
Для получения экземпляра `Medoo` и текущего id компании из инициализированного `Connector` необходимо использовать `Connector:db()` и `Connector:getCompanyId()` методы соответственно.
После этого, необходимо создать новую таблицу в БД для модели. Это можно сделать, используя одну из двух доступных консольных команд: 

- `db:create-table-auto` - автоматически сканирует все модели в пространстве имен `\Leadvertex\Plugin` и создает таблицу для каждой модели, основываясь на её названии.
Обратите внимание, что вы моджете задать произвольное название для таблицы, переопределив метод `tableName()` в классе вашей модели.
Этот метод имеет приоритет при автовыборе названия для таблицы:
```php
class OptionsModel extends Leadvertex\Plugin\Components\Db\Model
{
    public static function tableName(): string
    {
        return "myTableName";
    }
}
```

- `db:create-table-manual *table_name*` - создает одну таблицу с названием, заданным в параметре команды `*table_name*`.
Обратите внимание, что команда требует инициализированный `Connector` для работы. 

Все консольные команды используют компонент [symfony/console](https://github.com/symfony/console "symfony/console") `Command`.
Для примера, простое консольное приложение в файле console.php можно реализовать таким образом:
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

После этого, вы можете выполнить команду в консоли. Для примера команда для автоматического создания таблиц будет выглядеть таким образом:
```shell script
php console.php db:create-table-auto
```

### Свойства модели
Поля `id` и `feature` являются частями составного ключа для БД.\

- `id` - `string` id записи в БД. Может быть `null`, если необходимо сгенерировать значение автоматически (UUID).
- `feature` - `string` особенность записи. По умолчанию имеет пустое `''` значение.

Эти значения могут быть присвоены только при создании нового экземпляра модели.\
Для получения этих значений из модели используйте `getId()` и `getFeature()` методы соответственно:
```php
// Создание новой модели с id = '1' и feature = 'pluginOptions'
$model = new OptionsModel('1', 'pluginOptions');

$id = $model->getId();                          // = '1'
$feature = $model->getFeature();                // = 'pluginOptions'
```
Or you can create a model with null id and/or feature:
```php
// Создание новой модели с id = '1' и feature = ''
$model = new OptionsModel('1');

$id = $model->getId();                          // = '1'
$feature = $model->getFeature();                // = '' Обратите внимание, что это пустая строка, а не null

// Создание новой модели с id = случайный UUID и feature = 'pluginOptions'
$model = new OptionsModel(null, 'pluginOptions');

$id = $model->getId();                          // = сгенерированный UUID
$feature = $model->getFeature();                // = 'pluginOptions'

// Создание новой модели с id = случайный UUID и feature = ''
$model = new OptionsModel(null);

$id = $model->getId();                          // = сгенерированный UUID
$feature = $model->getFeature();                // = '' Обратите внимание, что это пустая строка, а не null
```

#### Встроенные timestamps
Вы можете получить метки используя методы `getCreatedAt()` и `getUpdatedAt()` соответственно.
Для примера, это можно использовать таким образом:
For example, you can use it like this:
```php
// Создание модели.
$model = new OptionsModel('1');

// Получения даты создания и даты последнего изменения.
$createdDate = $model->getCreatedAt();          // Вернет дату создания модели. В это поле автоматически записывается текущая дата при создании модели.
$updatedDate = $model->getUpdatedAt();          // Вернет null, так как модель еще не была обновлена. По умолчанию значение этого поля = null. Для установки значения необходимо использовать метод setUpdatedAt().

// Сохранение модели без ее обновления. Загруженная модель также будет иметь null в updatedAt.
$model->save();

// Если вам необходимо установить дату обновления для модели, вы можете сделать это таким образом:
$model->setUpdatedAt(new DateTimeImmutable());
$model->save();
```
Если вам необходимо устанавливать дату обновления автоматически, вам необходимо переопределить метод модели save() и в нём устанавливать updatedAt на необходимую дату.

#### Теги
У каждой модели есть 3 поля для тегов. Каждый тег может быть использован для описания какого-либо свойства сохраненной модели и в дальнейшем быть использован для поиска.
Для примера, вы можете записать теги в модель таким образом:
```php
// Создание новой модели.
$model = new OptionsModel('1');

// Установка значений тегов и сохранение модели.
$model->setTag_1('option');
$model->setTag_2('authentication');
$model->setTag_3('token');

$model->save();
```

Вы можете использовать теги для множественного поиска моделей по тегам.\ 

#### Пользовательские поля
Все пользовательские поля сохраняются в модель при помощи встроенных методов php. Чтобы использовать их, просто обратитесь к необъявленным полям модели и запишите туда необходимое значение:
```php
$model->userField = 'userValue';
$model->userField2 = 'userValue2';
...
```

Чтобы получить эти данные обратитесь к тем же полям:
```php
$userValue = $model->userField;                 // = 'userValue'
$userValue2 = $model->userField2;               // = 'userValue2'
...
```
Для полуения более подробной информации о встроенных методах вы можете перейти по [этой ссылке](https://www.php.net/manual/ru/language.oop5.magic.php "этой ссылке").

Все пользовательские поля сохраняются в массиве `data`. Вы модете записать в эти поля любые значения, кроме этих типов:
    - `resource`
    - анонимные классы и функции
    - некоторые встроенные PHP-объекты. [Более подробная информация здесь.](https://www.php.net/manual/en/function.serialize.php "More detailed info here.")

При сохранении модели, `data` со всеми полями будет сохранена как сериализованное значение в БД.

### Поиск моделей в БД
Для поиска существующих моделей в БД вы модете использовать один из методов поиска:
- `findById()` - находит одну модель по указанному id и feature, после чего возвращает первое совпадение. Feature не обязателен для поиска. Пример использования:
```php
// Поиск модели с id = '1' и feature = 'pluginOptions'
$model = CustomModelClass::findById(
    '1',                                        // id для поиска
    'pluginOptions'                             // feature для поиска
);

// Поиск модели с id = '2' и feature = ''
$model = CustomModelClass::findById(
    '2',                                        // id для поиска
    ''                                          // feature для поиска
);
```
- `findByIds()` - находит все модели по массиву указанных id и feature. Feature не обязателен для поиска. Пример использования:
```php
// Поиск моделей с id '1' и '2' и feature = ''
$models = CustomModelClass::findByIds(
    ['1', '2'],                                 // Массив id для поиска
    ''                                          // feature для поиска
);

// Поиск моделей с feature 'pluginOptions' вне зависимости от их id
$models = CustomModelClass::findByIds(
    [],                                         // Массив id для поиска
    'pluginOptions'                             // feature для поиска
);
```
- `findMany()` - находит все модели с указанным feature и всеми указанными тегами.
Вы можете оставить любой из тегов пустым, чтобы игнорировать его при поиске.
Также вы можете добавить Limit и Sort чтобы получить данные в нужном формате.\
Последний аргумент для этого метода -  `bool` свойство `inCurrentCompany`, который отвечает за указание области поиска.
По умолчанию это значение равно `true` и это означает, что `findMany()` будет искать модели только в пределах текущей компании.\
Если вы установите значение этого аргумента как `false`, поиск будет осуществлен по всем записям в БД.\
Пример использования:
```php
// Поиск всех моделей с feature = 'pluginOptions', tag_1 = 'option', tag_2 = 'authentication' ИЛИ 'authorization', игнорируя tag_3.
// Результат будет содержать 10 моделей, начиная с пятой и отсортированы по полю id по возрастанию.
$models = CustomModelClass::findMany(
    ['pluginOptions'],                          // Массив features для поиска
    ['option'],                                 // Массив values для поиска as tag_1
    ['authentication', 'authorization'],        // Массив values для поиска as tag_2
    [],                                         // Массив values для поиска as tag_3
    new Limit('10', '5'),                       // Стандартный SQL Limit
    new Sort(Sort::BY_ID, Sort::ASC),           // Стандартный SQL Sort. Все значения описаны в классе Sort как константы. Вы модете взять необходимое значение поля и направления из этих констант.
    false                                       // Установка аргумента inCurrentCompany как false. Это значит, что поиск будет выполнен по всем моделям в базе, а не только по моделям в пределах текущей компании.
);
```