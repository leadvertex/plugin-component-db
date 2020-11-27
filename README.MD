[![Build Status](https://travis-ci.com/leadvertex/plugin-component-db.svg?branch=disami_test)](https://travis-ci.com/leadvertex/plugin-component-db)

# Что такое plugin-component-db?

`plugin-component-db` - это компонент для работы с базой данных, может использоваться при разработке плагинов для CRM Leadvertex.
Подробнее о [плагинах](https://github.com/leadvertex/plugin-docs-internal). //TODO

## Установка

### Совместимость
Версия PHP ⩾ 7.4.0

Дополнительная информации о требованиях на [Packagist](https://packagist.org/packages/leadvertex/plugin-component-db)

### Composer

`composer require leadvertex/plugin-component-db`

## С чего начать?

### Реализация классов

Вы можете использовать свои классы, для работы с базой данных, используя один из интерфейсов:

- [`ModelInterface`](#ModelInterface)
- [`PluginModelInterface`](#PluginModelInterface)
- [`SinglePluginModelInterface`](#SinglePluginModelInterface)

Ваш класс, должен реализовать методы из интерфейса. Практически все методы реализованы в классе [`Model`](#Model), поэтому стоит наследоваться от него.

### Класс реализующий [`ModelInterface`](#ModelInterface)

В качестве примера, напишем класс `User`, у которой, есть два поля: `$name` и `$age`:
```
class User extends Model
{
    public string $name;
    public int $age;

    /**
     * @param string $id
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public static function schema(): array
    {
        return [
            'name' => ['VARCHAR(255)'],
            'age' => ['INT'],
        ];
    }
}
```
Обязательно должен быть реализован метод `public static function schema(): array`, это единственный метод, не реализованный в `Model`.
Данный метод, должен возвращать схему, по которой будет создана таблица в базе.
В ней должны содержаться все поля класса, которые необходимо записывать в базу. Подробнее о схеме в соответствующем разделе.
Так же, необходимо инициализировать поле `$id`, которое объявлено в классе [`Model`](#Model).
Рекомендуется, генерировать `$id` при помощи `Ramsey\Uuid\`.

Пример записи модели `User` в базу и чтения из нее:

```
Connector::config(new Medoo([
    'database_type' => 'sqlite',
    'database_file' => __DIR__ . '/newDB.db'
]));

$command = new CreateTablesCommand();
$tester = new CommandTester($command);
$tester->execute([]);

$model = new User();
$model->name = 'Sasha';
$model->age = 25;
$id = $model->getId();
$model->save();

$findModel = User::findById($id);
echo $findModel->name; // возвращает 'Sasha'
echo $findModel->age; // возвращает '25'
```
В примере, мы сначала инициализируем базу, при помощи класса `Connector`. Для этого, используем статичный метод `config`, в который передаем объект класса `Medoo`. [Документация Medoo](https://medoo.in/api/new).

Для того, что-бы модель сохранялась в базу, необходимо создать таблицу в базе, предварительно, инициализировав бд. Сделать это можно, используя консольную команду:
`php console.php db:create-tables` или как в примере
```
$command = new CreateTablesCommand();
$tester = new CommandTester($command);
$tester->execute([]);
```

После чего, можно создать объект класса `User`,`$model = new User()`, и установив в поля, необходимые значения `$model->name = 'Sasha'` и `$model->age = 25`, сохранить, используя `$model->save()`.

Для того, что-бы найти модель, можно воспользоваться одним из методов `find`, объявленных в классе `Model`. Подробнее об этих методах и их особенностях в соответствующем разделе.

### Класс реализующий [`PluginModelInterface`](#PluginModelInterface)

Класс `Model` сам использует [`ModelInterface`](#ModelInterface) и практически полностью реализует его.

Поэтому, в примере выше, реализуя класс `User`, использующий [`ModelInterface`](#ModelInterface), мы не указывали явно, что используем его.

Для остальных интерфейсов, необходимо явно указывать какой конкретно интерфейс ваш класс реализует, например: `class Post extends Model implements PluginModelInterface`.

В качестве примера, напишем класс `Post`, у которой, есть два поля: `$address` и `$postcode`:
```
class Post extends Model implements PluginModelInterface
{

    public string $address;
    public string $postcode;

    public function __construct(string $address, string $postcode)
    {
        $this->postcode = $postcode;
        $this->address = $address;
    }

    public function setId($uuid) {
        $this->id = $uuid;
    }

    /**
     * @inheritDoc
     */
    public static function schema(): array
    {
        return [
            'address' => ['VARCHAR(255)'],
            'postcode' => ['VARCHAR(255)'],
        ];
    }
    
    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }
}
```

Класс реализующий интерфейс [`PluginModelInterface`](#PluginModelInterface), не сильно отличается от реализации [`ModelInterface`](#ModelInterface).
Так происходит, из-за того, что [`PluginModelInterface`](#PluginModelInterface) наследуется от [`ModelInterface`](#ModelInterface) и не объявляет своих методов.
Единственное отличие, только в названии. Однако, именно из-за этого, данный интерфейс удобно использовать для плагинов.
Класс `Model`, отличает [`PluginModelInterface`](#PluginModelInterface) и для него создает дополнительные поля, а именно:
- `companyId`
- `pluginAlias`
- `pluginId`

Пример записи модели `Post` в базу и чтения из нее:
```
Connector::config(new Medoo([
    'database_type' => 'sqlite',
    'database_file' => __DIR__ . '/newDB.db'
]));
$companyId = 2;
Connector::setReference(new PluginReference($companyId, 'user', $pluginId));

$command = new CreateTablesCommand();
$tester = new CommandTester($command);
$tester->execute([]);

$postModel = new SimplePost('City', '123456');
$postModel->setId(Uuid::uuid4());
$postModel->save();
$id = $postModel->getId();
$findModel = SimplePost::findById($id);
echo $findModel->getAddress(); // возвращает 'City'
echo $findModel->getPostcode(); // возвращает '123456'
```

Аналогично примеру с `User`, инициализируем бд и создаем таблицу в ней, для нашей модели. Но теперь еще и устанавливаем ссылку на плагин.
Делаем это при помощи статичного метода `setReference` в классе `Connector`. Передаем в него объект `PluginReference`, который в себе содержит три поля.
Те что, класс `Model` добавляет для реализации [`PluginModelInterface`](#PluginModelInterface):
- `companyId` - Идентификатор компании, которая использует плагин.
- `pluginAlias` - Псевдоним плагина(от чьего имени исполняется)
- `pluginId` - Идентификатор плагина

Сохранение и поиск, выполняются аналогично.
Однако разница есть, а именно в содержимом базы:

  ![img.png](img.png)

На скриншоте видно, что в базе хранятся две, практически идентичные модели. Однако, `companyId` у них разные.
И если, попробовать найти модель, то найдена будет та, для которой ссылка на плагин соответствует установленной в `Connector::setReference()`.
То есть, в нашем случае, ту, у которой в базе `$companyId = 2`.
Происходит так из-за того, что для [`PluginModelInterface`](#PluginModelInterface), в качестве уникального идентификатора служат поля
`companyId + pluginAlias + pluginId + id`.

[comment]: <> (Если необходимо хранить сложную модель, можно переопределить методы: )

[comment]: <> (- `protected static function beforeWrite&#40;array $data&#41;: array`)

[comment]: <> (- `protected static function afterRead&#40;array $data&#41;: array`)

[comment]: <> (Эти методы реализованы и используются в [`ModelTrait`]&#40;#ModelTrait&#41;, в них, можно написать свою сериализацию данных в формат, поддерживаемый бд и десериализацию обратно.)

[comment]: <> (Так же в классе, обязательно, должна быть реализована возможность инициализации поля `id`, объявленного в `ModelTrait`. )

[comment]: <> (Например, при помощи сеттеров или в конструкторе.)

[comment]: <> (Пример реализации класса:)

[comment]: <> (```)

[comment]: <> (class SimpleModelClass implements ModelInterface)

[comment]: <> ({)

[comment]: <> (    use ModelTrait;)

[comment]: <> (    private int $value_1;)

[comment]: <> (    public string $value_2;)

[comment]: <> (    public function setValue1&#40;$value_1&#41;: void)

[comment]: <> (    {)

[comment]: <> (        $this->value_1 = $value_1;)

[comment]: <> (    })
    
[comment]: <> (    public function setId&#40;string $id&#41;: void)

[comment]: <> (    {)

[comment]: <> (        $this->id = $id;)

[comment]: <> (    })

[comment]: <> (    public static function schema&#40;&#41;: array)

[comment]: <> (    {)

[comment]: <> (        return [)

[comment]: <> (            'value_1' => ['INT'],)

[comment]: <> (            'value_2' => ['VARCHAR&#40;255&#41;'],)

[comment]: <> (        ];)

[comment]: <> (    })

[comment]: <> (})

[comment]: <> (```)

[comment]: <> (Подробнее о каждом компоненте, можно прочитать в соответствующем разделе данной документации.)

[comment]: <> (### Запись модели в базу <a name="WriteModelToDB"></a>)

[comment]: <> (Для записи модели в базу, необходимо сделать несколько действий.)

[comment]: <> (- Во-первых, должна быть инициализирована база. Для этого, можно воспользоваться статичным методом `config` класса `Connector`, в который передаем объект класса [`Medoo`]&#40;https://medoo.in/api/new&#41;. Пример инициализации базы `sqlite`: )

[comment]: <> (    ```)

[comment]: <> (    Connector::config&#40;new Medoo&#40;[)

[comment]: <> (        'database_type' => 'sqlite',)

[comment]: <> (        'database_file' => __DIR__ . '/newDB.db')

[comment]: <> (    ]&#41;&#41;;)

[comment]: <> (    ```)

[comment]: <> (  И установленна ссылка на плагин, если используется [`PluginModelInterface`]&#40;#PluginModelInterface&#41;. Для этого, можно использовать статичным методом `setReference` класса `Connector`, в который передаем объект класса `PluginReference`. Пример кода:)

[comment]: <> (    ```)

[comment]: <> (    Connector::setReference&#40;new PluginReference&#40;$companyId, $alias, $id&#41;&#41;;)

[comment]: <> (    ```)

[comment]: <> (- Во-вторых, должен быть создан объект, созданного класса и записаны в него данные, для сохранения.)

[comment]: <> (Пример кода:)

[comment]: <> (  ```)

[comment]: <> (  $model = new SimpleModelClass&#40;&#41;;)

[comment]: <> (  $model->setId&#40;$id&#41;;)

[comment]: <> (  $model->setValue1&#40;$myInt&#41;;)

[comment]: <> (  $model->value_2 = $myString;)

[comment]: <> (  ```)

[comment]: <> (- После чего, модель можно сохранить в базу:`$model->save&#40;&#41;;`)

[comment]: <> (### Поиск модели в базе)

[comment]: <> (Для поиска модели из базы, можно воспользоваться статичными методами, реализованными в трейте [`ModelTrait`]&#40;#ModelTrait&#41;:)

[comment]: <> (- `public static function findByCondition&#40;array $where&#41;: array`)

[comment]: <> (- `public static function findById&#40;string $id&#41;: ?self`)

[comment]: <> (- `public static function findByIds&#40;array $ids&#41;: array`)

[comment]: <> (Или статичным методом, реализованном в трейте [`SinglePluginModelTrait`]&#40;#SinglePluginModelTrait&#41;:)

[comment]: <> (- `public static function find&#40;&#41;: ?self`)

[comment]: <> (Подробнее о методах написано в [`ModelTrait`]&#40;#ModelTrait&#41; и [`SinglePluginModelTrait`]&#40;#SinglePluginModelTrait&#41;.)

[comment]: <> (Каждый из методов поиска, возвращает либо саму модель, либо массив моделей.)

[comment]: <> (Примечание: тип возвращаемой модели, соответствует классу, с помощью которого осуществляется поиск.)

[comment]: <> (Пример кода:)

[comment]: <> (```)

[comment]: <> (myModelsFindByCondition = SimpleModelClass::findByCondition&#40;['value_1' => $myInt]&#41;;)

[comment]: <> (myModelFindById = SimpleModelClass::findById&#40;$id&#41;;)

[comment]: <> (myModelsFindByIds = SimpleModelClass::findByIds&#40;[1, 2, 3, 4]&#41;;)

[comment]: <> (mySingleModelFind = SingleModelClass::find&#40;&#41;;)

[comment]: <> (```)

[comment]: <> (## ModelInterface<a name="ModelInterface"></a>)

[comment]: <> ([`ModelInterface`]&#40;#ModelInterface&#41;, является основным интерфейсом, все остальные дочерние от него.)

[comment]: <> (В нем объявлены методы, необходимые, для работы с базой данных. Как уже упоминалось, практически все методы реализованы в трейте [`ModelTrait`]&#40;#ModelTrait&#41;, а именно:)

[comment]: <> (- `public function save&#40;&#41;: void;`)

[comment]: <> (- `public function delete&#40;&#41;: void;`)

[comment]: <> (- `public static function findById&#40;string $id&#41;: ?self;`)

[comment]: <> (- `public static function findByIds&#40;array $ids&#41;: array;`)

[comment]: <> (- `public static function findByCondition&#40;array $where&#41;: array;`)

[comment]: <> (- `public static function tableName&#40;&#41;: string;`)

[comment]: <> (### Метод `schema&#40;&#41;`)

[comment]: <> (Единственный, не реализованный в трейте метод, это `public static function schema&#40;&#41;: array;`.)

[comment]: <> (Как уже упоминалось ранее, данный метод, должен возвращать массив схемы, по который буден создана таблица в базе данных.)

[comment]: <> (Пример элемента массива: `'value_1' => ['INT']`, тут `value_1` имя столбца, а `[INT]`, соответствующий ему тип.)

[comment]: <> (Тип может быть любым, который поддерживается базой данных.)

[comment]: <> (Для каждого столбца, в классе, должно быть создано поле, с тем же именем.)

[comment]: <> (Не используйте `AUTO_INCREMENT`, вместо этого используйте UUID `Ramsey\Uuid\Uuid::uuid4&#40;&#41;->toString&#40;&#41;` для идентификатора модели.)

[comment]: <> (Не используйте `PRIMARY KEY` в описании схемы. Он будет автоматически сгенерирован с помощью `id` или `companyId` + `pluginAlias` + `pluginId` + `id`)

[comment]: <> (Не используйте поля `id`, `companyId`, `pluginAlias` и `pluginId` в схеме. Они будут сгенерирован автоматически.)

[comment]: <> (## PluginModelInterface<a name="PluginModelInterface"></a>)

[comment]: <> ([`PluginModelInterface`]&#40;#PluginModelInterface&#41;, является дочерним от [`ModelInterface`]&#40;#ModelInterface&#41;, в нем не объявлен ни один метод.)

[comment]: <> (А отличается он от родительского, только названием. )

[comment]: <> (### Особенности интерфейса)

[comment]: <> (Интерфейс, отличается только именем, для того, что-бы [`ModelTrait`]&#40;#ModelTrait&#41;, понимал,)

[comment]: <> (что класс использует данный интерфейс, работал с дополнительными полями, которые, необходимы для работы плагина.)

[comment]: <> (А именно:)

[comment]: <> (- `companyId`)

[comment]: <> (- `pluginAlias`)

[comment]: <> (- `pluginId`)

[comment]: <> (Для того, что-бы использовать класс с данным интерфейсом, необходимо, установить ссылку на плагин, в `Connector`, как это сделать, было описано выше, в разделе: ['Запись модели в базу']&#40;#WriteModelToDB&#41;.)

[comment]: <> (После того, как будет установленна ссылка, любые операции с данной моделью, будут выполняться с учетом этих полей.)

[comment]: <> (То есть, если в базу, есть одинаковые записи, которые отличаются, например, только `companyId`, то при поиске или удалении модели, действия будут выполнено только для той записи, у которой соответствует `companyId`, установленному в `Connector::setReference&#40;new PluginReference&#40;$companyId, $alias, $id&#41;&#41;;`.)

[comment]: <> (Происходит это автоматически, без необходимости дополнительно проверять соответствие. )


[comment]: <> (## SinglePluginModelInterface<a name="SinglePluginModelInterface"></a>)

[comment]: <> ([`SinglePluginModelInterface`]&#40;#SinglePluginModelInterface&#41;, является дочерним от [`PluginModelInterface`]&#40;#PluginModelInterface&#41;, и повторяет его поведение.)

[comment]: <> (### Особенности интерфейса)

[comment]: <> (В данном интерфейсе объявлен дополнительный метод `public static function find&#40;&#41;: ?self`.)

[comment]: <> (Данный метод реализован в трейте [`SinglePluginModelTrait`]&#40;#SinglePluginModelTrait&#41;.)

[comment]: <> (При создании класса, использующего данный интерфейс, рекомендуется использовать оба трейта вместе, [`SinglePluginModelTrait`]&#40;#SinglePluginModelTrait&#41; и [`ModelTrait`]&#40;#ModelTrait&#41;.)

[comment]: <> (## ModelTrait<a name="ModelTrait"></a>)

[comment]: <> (Данный трейт рекомендуется использовать с каждым интерфейсом.)

[comment]: <> (В нем реализованы методы, объявленные в основном интерфейсе [`ModelInterface`]&#40;#ModelInterface&#41;.)

[comment]: <> (### Метод `save&#40;&#41;`)

[comment]: <> (Данный метод, сохраняет модель в базу.)

[comment]: <> (Пример кода: `$model->save&#40;&#41;;`)

[comment]: <> (### Метод `delete&#40;&#41;`)

[comment]: <> (Данный метод, удаляет модель из базы.)

[comment]: <> (Пример кода: `$model->delete&#40;&#41;;`)

[comment]: <> (### Метод `findById&#40;&#41;`)

[comment]: <> (Данный метод является статическим, он позволяет найти модель в базе, по ее `id`.)

[comment]: <> (Метод возвращает одну модель.)

[comment]: <> (Пример кода: `myModelFindById = SimpleModelClass::findById&#40;$id&#41;;`)

[comment]: <> (### Метод `findByIds&#40;&#41;`)

[comment]: <> (Данный метод является статическим, он позволяет найти несколько моделей в базе, по их `id`.)

[comment]: <> (Метод принимает массив `id`, а возвращает массив моделей.)

[comment]: <> (Пример кода: `myModelFindByIds = SimpleModelClass::findByIds&#40;[$id, lastId]&#41;;`)

[comment]: <> (### Метод `findByCondition&#40;&#41;`)

[comment]: <> (Данный метод является статическим, он позволяет найти несколько моделей в базе, по их состоянию.)

[comment]: <> (Метод принимает массив `where`, а возвращает массив моделей.)

[comment]: <> (Подробнее о синтаксисе `where`, можно прочитать в документации [Medoo]&#40;https://medoo.in/api/where&#41;.)

[comment]: <> (Пример кода: `myModelsFindByCondition = SimpleModelClass::findByCondition&#40;['value_1' => $myInt]&#41;;`)

[comment]: <> (### Метод `tableName&#40;&#41;`)

[comment]: <> (Данный метод, возвращает строку с именем таблицы.)

[comment]: <> (Пример кода: `SimpleModelClass::tableName&#40;&#41;;`)

[comment]: <> (### Метод `getId&#40;&#41;`)

[comment]: <> (Данный метод, возвращает `id` модели в виде строки.)

[comment]: <> (Пример кода: `$model->getId&#40;&#41;;`)

[comment]: <> (### Метод `beforeSave&#40;bool $isNew&#41;`)

[comment]: <> (Данный метод пустой.)

[comment]: <> (Однако, в своем классе его можно переопределить.)

[comment]: <> (В него можно добавить необходимый код, который будет выполнен после `beforeWrite&#40;&#41;`, и перед сохранением модели.)

[comment]: <> (### Метод `afterFind&#40;&#41;`)

[comment]: <> (Аналогично методу `beforeSave`.)

[comment]: <> (В него можно добавить необходимый код, который будет выполнен после `afterRead&#40;&#41;`.)

[comment]: <> (### Метод `afterRead&#40;array $data&#41;`)

[comment]: <> (Данный метод возвращает исходные данные, не меняя их.)

[comment]: <> (Однако, в своем классе его можно переопределить.)

[comment]: <> (В него можно добавить необходимый код, который будет выполнен после чтения модели из базы.)

[comment]: <> (Например, тут можно производить десериализацию данных.)

[comment]: <> (### Метод `beforeWrite&#40;array $data&#41;`)

[comment]: <> (Аналогично методу `afterRead`.)

[comment]: <> (В него можно добавить необходимый код, который будет выполнен перед записью модели в базу.)

[comment]: <> (Например, для сериализацию данных.)

[comment]: <> (### Отличие методов `beforeWrite`, `beforeSave` и `afterRead`, `afterFind`)

[comment]: <> (Методы `beforeWrite` и `afterRead` принимаю данные на вход и возвращают их. Данные методы можно переопределить, например, для изменения данных.)

[comment]: <> (Методы `beforeSave` и `afterFind` ничего не возвращают. Данные методы можно переопределить для выполнения любого, необходимого кода.)

[comment]: <> (При выполнении метода `save&#40;&#41;` или одного из `find`, сначала вызывается `beforeWrite` или `afterRead`, после чего `beforeSave` или `afterFind`, соответственно.)

[comment]: <> (## SinglePluginModelTrait<a name="SinglePluginModelTrait"></a>)

[comment]: <> (Данный трейт рекомендуется использовать с интерфейсом [`SinglePluginModelInterface`]&#40;#SinglePluginModelInterface&#41;, так как в нем реализованы метод `find&#40;&#41;`, объявленные в интерфейсе.)

[comment]: <> (### Метод `find&#40;&#41;`)

[comment]: <> (Данный метод является статическим, он позволяет найти модель в базе, по ее `id`, без явной передачи `id`.)

[comment]: <> (Так как такая модель работает с одним плагином, `id` модели равен `pluginId`. И при поиске, передается автоматически.)

[comment]: <> (Метод возвращает одну модель.)

[comment]: <> (Пример кода: `mySingleModelFind = SingleModelClass::find&#40;&#41;;`)

[comment]: <> (## Компоненты)

[comment]: <> (Для работы с базой данных, можно использовать компоненты:)

[comment]: <> (- [`Connector`]&#40;#Connector&#41;)

[comment]: <> (- [`PluginReference`]&#40;#PluginReference&#41;)

[comment]: <> (### Connector<a name="Connector"></a>)

[comment]: <> (Класс `Connector` позволяет настроить базу и установить ссылку на плагин.)

[comment]: <> (В нем реализованы методы:)

[comment]: <> (- `public static function config&#40;Medoo $medoo&#41;: void`, статичный метод, который принимает объект `Medoo` и инициализирует статическое поле `$db`)

[comment]: <> (- `public static function db&#40;&#41;: Medoo`, статичный метод, который возвращает статическое поле `$db`)

[comment]: <> (- `public static function setReference&#40;PluginReference $reference&#41;`, статичный сеттер для поля `$reference`, который принимает объект `PluginReference`)

[comment]: <> (- `public static function getReference&#40;&#41;: PluginReference`, статичный геттер для поля `$reference`, который принимает объект `PluginReference`)

[comment]: <> (- `public static function hasReference&#40;&#41;: bool`, статичный метод, который отвечает, установлена ли ссылка на плагин)


[comment]: <> (### PluginReference<a name="PluginReference"></a>)

[comment]: <> (Класс `PluginReference` ссылка на плагин.)

[comment]: <> (В конструкторе он принимает:)

[comment]: <> (- `string $companyId`)

[comment]: <> (- `string $alias`)

[comment]: <> (- `string $id`)

[comment]: <> (И содержит три геттера:)

[comment]: <> (- `getCompanyId`)

[comment]: <> (- `getAlias`)

[comment]: <> (- `getId`)

[comment]: <> (## Консольный команды)

[comment]: <> (- Создание таблиц)

[comment]: <> (- Очистка таблиц)

[comment]: <> (### Создание таблиц)

[comment]: <> (Для того, что бы создать таблица в инициализированной базе данных, можно воспользоваться консольной командой:)

[comment]: <> (`php console.php db:create-tables`)

[comment]: <> (Команда рекурсивно пробегает по классам, и создает таблицы, для всех, которые используют интерфейсы.)

[comment]: <> (### Очистка таблиц)

[comment]: <> (TODO :&#41;)
