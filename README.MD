# plugin-component-db

Компонент для работы с базой данных, может использоваться при разработке плагинов для CRM Leadvertex.
Подробнее о [плагинах](https://github.com/leadvertex/plugin-docs-internal).

## Установка

### Совместимость
Версия PHP ⩾ 7.4.0

Дополнительная информации о требованиях на [Packagist](https://packagist.org/packages/leadvertex/plugin-component-db)


### Composer

`composer require leadvertex/plugin-component-db`

## Использование

### Реализация классов

Вы можете создать свои классы, для работы с базой данных, используя один из интерфейсов:

- [`ModelInterface`](#ModelInterface)
- [`PluginModelInterface`](#PluginModelInterface)
- [`SinglePluginModelInterface`](#SinglePluginModelInterface)

В классе необходимо реализовать методы из интерфейса. Часть методов реализованы в [трейте](https://www.php.net/manual/ru/language.oop5.traits.php) [`ModelTrait`](#ModelTrait), лучшим решением, будет его использование.
Единственный, не реализованный в трейте метод, это `public static function schema(): array;`.
Метод `schema()`, должен возвращать массив схемы, по который буден создана таблица в базе данных.

Если необходимо хранить сложную модель, можно переопределить методы: 
- `protected static function beforeWrite(array $data): array`
- `protected static function afterRead(array $data): array`

Эти методы реализованы и используются в [`ModelTrait`](#ModelTrait), в них, можно написать свою сериализацию данных в формат, поддерживаемый бд и десериализацию обратно.

Так же в классе, обязательно, должна быть реализована возможность инициализации поля `id`, объявленного в `ModelTrait`. 
Например, при помощи сеттеров или в конструкторе.

Пример реализации класса:
```
class SimpleModelClass implements ModelInterface
{
    use ModelTrait;

    private int $value_1;
    public string $value_2;

    public function setValue1($value_1): void
    {
        $this->value_1 = $value_1;
    }
    
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public static function schema(): array
    {
        return [
            'value_1' => ['INT'],
            'value_2' => ['VARCHAR(255)'],
        ];
    }
}
```

Подробнее о каждом компоненте, можно прочитать в соответствующем разделе данной документации.

### Запись модели в базу <a name="WriteModelToDB"></a>

Для записи модели в базу, необходимо сделать несколько действий.

- Во-первых, должна быть инициализирована база. Для этого, можно воспользоваться статичным методом `init` класса `Connector`, в который передаем объект класса `Medoo`. Пример инициализации базы `sqlite`: 
    ```
    Connector::init(new Medoo([
        'database_type' => 'sqlite',
        'database_file' => __DIR__ . '/newDB.db'
    ]));
    ```
  И установленна ссылка на плагин, если используется [`PluginModelInterface`](#PluginModelInterface). Для этого, можно использовать статичным методом `setReference` класса `Connector`, в который передаем объект класса `PluginReference`. Пример кода:
    ```
    Connector::setReference(new PluginReference($companyId, $alias, $id));
    ```
- Во-вторых, должен быть создан объект, созданного класса и записаны в него данные, для сохранения.
Пример кода:
  ```
  $model = new SimpleModelClass();
  $model->setId($id);
  $model->setValue1($myInt);
  $model->value_2 = $myString;
  ```
- После чего, модель можно сохранить в базу:`$model->save();`

### Поиск модели в базе

Для поиска модели из базы, можно воспользоваться статичными методами, реализованными в трейте [`ModelTrait`](#ModelTrait):
- `public static function findByCondition(array $where): array`
- `public static function findById(string $id): ?self`
- `public static function findByIds(array $ids): array`

Или статичным методом, реализованном в трейте [`SinglePluginModelTrait`](#SinglePluginModelTrait):
- `public static function find(): ?self`

Подробнее о методах написано в [`ModelTrait`](#ModelTrait) и [`SinglePluginModelTrait`](#SinglePluginModelTrait).
Каждый из методов поиска, возвращает либо саму модель, либо массив моделей.
Примечание: тип возвращаемой модели, соответствует классу, с помощью которого осуществляется поиск.

Пример кода:
```
myModelsFindByCondition = SimpleModelClass::findByCondition(['value_1' => $myInt]);
myModelFindById = SimpleModelClass::findById($id);
myModelsFindByIds = SimpleModelClass::findByIds([1, 2, 3, 4]);
mySingleModelFind = SingleModelClass::find();
```

## ModelInterface<a name="ModelInterface"></a>

[`ModelInterface`](#ModelInterface), является основным интерфейсом, все остальные дочерние от него.

В нем объявлены методы, необходимые, для работы с базой данных. Как уже упоминалось, практически все методы реализованы в трейте [`ModelTrait`](#ModelTrait), а именно:

- `public function save(): void;`
- `public function delete(): void;`
- `public static function findById(string $id): ?self;`
- `public static function findByIds(array $ids): array;`
- `public static function findByCondition(array $where): array;`
- `public static function tableName(): string;`

### Метод `schema()`

Единственный, не реализованный в трейте метод, это `public static function schema(): array;`.
Как уже упоминалось ранее, данный метод, должен возвращать массив схемы, по который буден создана таблица в базе данных.
Пример элемента массива: `'value_1' => ['INT']`, тут `value_1` имя столбца, а `[INT]`, соответствующий ему тип.
Тип может быть любым, который поддерживается базой данных.
Для каждого столбца, в классе, должно быть создано поле, с тем же именем.
Не используйте `AUTO_INCREMENT`, вместо этого используйте UUID `Ramsey\Uuid\Uuid::uuid4()->toString()` для идентификатора модели.
Не используйте `PRIMARY KEY` в описании схемы. Он будет автоматически сгенерирован с помощью `id` или `companyId` + `pluginAlias` + `pluginId` + `id`
Не используйте поля `id`, `companyId`, `pluginAlias` и `pluginId` в схеме. Они будут сгенерирован автоматически.

## PluginModelInterface<a name="PluginModelInterface"></a>

[`PluginModelInterface`](#PluginModelInterface), является дочерним от [`ModelInterface`](#ModelInterface), в нем не объявлен ни один метод.
А отличается он от родительского, только названием. 

### Особенности интерфейса

Интерфейс, отличается только именем, для того, что-бы [`ModelTrait`](#ModelTrait), понимал,
что класс использует данный интерфейс, работал с дополнительными полями, которые, необходимы для работы плагина.
А именно:
- `companyId`
- `pluginAlias`
- `pluginId`

Для того, что-бы использовать класс с данным интерфейсом, необходимо, установить ссылку на плагин, в `Connector`, как это сделать, было описано выше, в разделе: ['Запись модели в базу'](#WriteModelToDB).
После того, как будет установленна ссылка, любые операции с данной моделью, будут выполняться с учетом этих полей.
То есть, если в базу, есть одинаковые записи, которые отличаются, например, только `companyId`, то при поиске или удалении модели, действия будут выполнено только для той записи, у которой соответствует `companyId`, установленному в `Connector::setReference(new PluginReference($companyId, $alias, $id));`.
Происходит это автоматически, без необходимости дополнительно проверять соответствие. 


## SinglePluginModelInterface<a name="SinglePluginModelInterface"></a>

[`SinglePluginModelInterface`](#SinglePluginModelInterface), является дочерним от [`PluginModelInterface`](#PluginModelInterface), и повторяет его поведение.

### Особенности интерфейса

В данном интерфейсе объявлен дополнительный метод `public static function find(): ?self`.
Данный метод реализован в трейте [`SinglePluginModelTrait`](#SinglePluginModelTrait).
При создании класса, использующего данный интерфейс, рекомендуется использовать оба трейта вместе, [`SinglePluginModelTrait`](#SinglePluginModelTrait) и [`ModelTrait`](#ModelTrait).

## ModelTrait<a name="ModelTrait"></a>

Данный трейт рекомендуется использовать с каждым интерфейсом.
В нем реализованы методы, объявленные в основном интерфейсе [`ModelInterface`](#ModelInterface).

### Метод `save()`

Данный метод, сохраняет модель в базу.
Пример кода: `$model->save();`

### Метод `delete()`

Данный метод, удаляет модель из базы.
Пример кода: `$model->delete();`

### Метод `findById()`

Данный метод является статическим, он позволяет найти модель в базе, по ее `id`.
Метод возвращает одну модель.
Пример кода: `myModelFindById = SimpleModelClass::findById($id);`

### Метод `findByIds()`

Данный метод является статическим, он позволяет найти несколько моделей в базе, по их `id`.
Метод принимает массив `id`, а возвращает массив моделей.
Пример кода: `myModelFindByIds = SimpleModelClass::findByIds([$id, lastId]);`

### Метод `findByCondition()`

Данный метод является статическим, он позволяет найти несколько моделей в базе, по их состоянию.
Метод принимает массив `where`, а возвращает массив моделей.
Подробнее о синтаксисе `where`, можно прочитать в документации [Medoo](https://medoo.in/api/where).
Пример кода: `myModelsFindByCondition = SimpleModelClass::findByCondition(['value_1' => $myInt]);`

### Метод `tableName()`

Данный метод, возвращает строку с именем таблицы.
Пример кода: `SimpleModelClass::tableName();`

### Метод `getId()`

Данный метод, возвращает `id` модели в виде строки.
Пример кода: `$model->getId();`

### Метод `beforeSave(bool $isNew)`

Данный метод пустой.
Однако, в своем классе его можно переопределить.
В него можно добавить необходимый код, который будет выполнен после `beforeWrite()`, и перед сохранением модели.

### Метод `afterFind()`

Аналогично методу `beforeSave`.
В него можно добавить необходимый код, который будет выполнен после `afterRead()`.

### Метод `afterRead(array $data)`

Данный метод возвращает исходные данные, не меняя их.
Однако, в своем классе его можно переопределить.
В него можно добавить необходимый код, который будет выполнен после чтения модели из базы.
Например, тут можно производить десериализацию данных.

### Метод `beforeWrite(array $data)`

Аналогично методу `afterRead`.
В него можно добавить необходимый код, который будет выполнен перед записью модели в базу.
Например, для сериализацию данных.

### Отличие методов `beforeWrite`, `beforeSave` и `afterRead`, `afterFind`

Методы `beforeWrite` и `afterRead` принимаю данные на вход и возвращают их. Данные методы можно переопределить, например, для изменения данных.
Методы `beforeSave` и `afterFind` ничего не возвращают. Данные методы можно переопределить для выполнения любого, необходимого кода.

При выполнении метода `save()` или одного из `find`, сначала вызывается `beforeWrite` или `afterRead`, после чего `beforeSave` или `afterFind`, соответственно.

## SinglePluginModelTrait<a name="SinglePluginModelTrait"></a>

Данный трейт рекомендуется использовать с интерфейсом [`SinglePluginModelInterface`](#SinglePluginModelInterface), так как в нем реализованы метод `find()`, объявленные в интерфейсе.

### Метод `find()`

Данный метод является статическим, он позволяет найти модель в базе, по ее `id`, без явной передачи `id`.
Так как такая модель работает с одним плагином, `id` модели равен `pluginId`. И при поиске, передается автоматически.
Метод возвращает одну модель.
Пример кода: `mySingleModelFind = SingleModelClass::find();`

## Компоненты

Для работы с базой данных, можно использовать компоненты:
- [`Connector`](#Connector)
- [`PluginReference`](#PluginReference)

### Connector<a name="Connector"></a>

Класс `Connector` позволяет настроить базу и установить ссылку на плагин.
В нем реализованы методы:
- `public static function init(Medoo $medoo): void`, статичный метод, который принимает объект `Medoo` и инициализирует статическое поле `$db`
- `public static function db(): Medoo`, статичный метод, который возвращает статическое поле `$db`
- `public static function setReference(PluginReference $reference)`, статичный сеттер для поля `$reference`, который принимает объект `PluginReference`
- `public static function getReference(): PluginReference`, статичный геттер для поля `$reference`, который принимает объект `PluginReference`
- `public static function hasReference(): bool`, статичный метод, который отвечает, установлена ли ссылка на плагин


### PluginReference<a name="PluginReference"></a>

Класс `PluginReference` ссылка на плагин.

В конструкторе он принимает:
- `string $companyId`
- `string $alias`
- `string $id`

И содержит три геттера:
- `getCompanyId`
- `getAlias`
- `getId`

## Консольный команды

- Создание таблиц
- Очистка таблиц

### Создание таблиц

Для того, что бы создать таблица в инициализированной базе данных, можно воспользоваться консольной командой:
`php console.php db:create-tables`

Команда рекурсивно пробегает по классам, и создает таблицы, для всех, которые используют интерфейсы.

### Очистка таблиц

TODO :)