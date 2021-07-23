<?php
/**
 * Created for plugin-component-db
 * Date: 26.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Db;


use BadMethodCallException;
use InvalidArgumentException;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Exceptions\DatabaseException;
use Medoo\Medoo;
use ReflectionClass;
use ReflectionException;

abstract class Model implements ModelInterface
{

    protected string $id;

    private bool $isNew = true;

    private static array $onSaveHandlers = [];

    private static array $loaded = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function save(): void
    {
        $db = static::db();
        $data = static::serialize($this);

        $this->beforeSave($this->isNew);
        if ($this->isNew) {
            $db->insert(static::tableName(), $data);
            static::$loaded[static::calcHash($data)] = $this;
            $this->isNew = false;
        } else {
            $where = [
                'id' => $this->id
            ];

            unset($data['id']);

            if ($this instanceof PluginModelInterface) {
                $where['companyId'] = Connector::getReference()->getCompanyId();
                $where['pluginAlias'] = Connector::getReference()->getAlias();
                $where['pluginId'] = Connector::getReference()->getId();

                unset($data['companyId']);
                unset($data['pluginAlias']);
                unset($data['pluginId']);
            }

            $db->update(static::tableName(), $data, $where);
        }

        DatabaseException::guard($db);

        $onSaveHandlers = static::$onSaveHandlers[static::class] ?? [];
        foreach ($onSaveHandlers as $handler) {
            $handler($this);
        }
    }

    public function delete(): void
    {
        $where = [
            'id' => $this->id
        ];

        if ($this instanceof PluginModelInterface) {
            $where['companyId'] = Connector::getReference()->getCompanyId();
            $where['pluginAlias'] = Connector::getReference()->getAlias();
            $where['pluginId'] = Connector::getReference()->getId();
        }

        static::db()->delete(static::tableName(), $where);
        DatabaseException::guard(static::db());
    }

    public function isNewModel(): bool
    {
        return $this->isNew;
    }

    protected function beforeSave(bool $isNew): void
    {
    }

    protected function afterFind(): void
    {
    }

    public static function findById(string $id): ?self
    {
        $models = static::findByCondition([
            'id' => $id,
            'LIMIT' => 1,
        ]);

        if (empty($models)) {
            return null;
        }

        return $models[$id];
    }

    public static function findByIds(array $ids): array
    {
        return static::findByCondition([
            'id' => $ids,
        ]);
    }

    /**
     * @link https://medoo.in/api/where
     * @param array $where
     * @return array
     * @throws ReflectionException
     * @throws DatabaseException
     */
    public static function findByCondition(array $where): array
    {
        if (is_a(static::class, PluginModelInterface::class, true)) {
            $where['companyId'] = Connector::getReference()->getCompanyId();
            $where['pluginAlias'] = Connector::getReference()->getAlias();
            $where['pluginId'] = Connector::getReference()->getId();
        }

        $data = static::db()->select(
            static::tableName(),
            '*',
            $where
        );

        DatabaseException::guard(static::db());

        $models = [];
        foreach ($data as $item) {
            $model = static::deserialize($item);
            $model->isNew = false;
            $model->afterFind();
            $models[$item['id']] = $model;
        }

        return $models;
    }

    public static function find(): ?Model
    {
        if (!is_a(static::class, SinglePluginModelInterface::class, true)) {
            throw new BadMethodCallException('Model::find() can work only with interface ' . SinglePluginModelInterface::class);
        }
        return static::findById(Connector::getReference()->getId());
    }

    public static function addOnSaveHandler(callable $handler, string $name = null): void
    {
        static::$onSaveHandlers[static::class][$name ?? uniqid()] = $handler;
    }

    public static function removeOnSaveHandler(string $name): void
    {
        unset(static::$onSaveHandlers[static::class][$name]);
    }

    public static function tableName(): string
    {
        $parts = explode('\\', static::class);
        return end($parts);
    }

    /**
     * Attention!
     * - DO NOT USE `AUTO_INCREMENT`, instead please use UUID `Ramsey\Uuid\Uuid::uuid4()->toString()` for model id
     * - DO NOT USE `PRIMARY KEY` in schema description. It will be generated automatically by `id` or
     *   `companyId` + `pluginAlias` + `pluginId` + `id`
     * - DO NOT USE fields `id`, `companyId`, `pluginAlias` and `pluginId` in schema. It will be generated automatically.
     *
     * @link https://medoo.in/api/create
     * @return array[]
     */
    abstract public static function schema(): array;

    public static function freeUpMemory(): void
    {
        static::$loaded = [];
    }

    protected static function afterRead(array $data): array
    {
        return $data;
    }

    protected static function beforeWrite(array $data): array
    {
        return $data;
    }

    /**
     * @param Model $model
     * @return array
     */
    protected static function serialize(self $model): array
    {
        $fields = array_keys(
            array_filter(static::schema(), fn($value) => is_array($value))
        );
        $fields[] = 'id';

        $data = [];
        foreach ($fields as $field) {

            if ($field === 'id' && $model instanceof SinglePluginModelInterface) {
                $value = Connector::getReference()->getId();
            } else {
                $value = $model->{$field};
            }

            $data[$field] = $value;
        }

        $data = static::beforeWrite($data);
        foreach ($data as $field => $value) {
            if (!is_null($value) && !is_scalar($value)) {
                throw new InvalidArgumentException("Field '{$field}' of '" . get_class($model) . "' should be scalar or null");
            }
        }

        if (is_a(static::class, PluginModelInterface::class, true)) {
            $data['companyId'] = Connector::getReference()->getCompanyId();
            $data['pluginAlias'] = Connector::getReference()->getAlias();
            $data['pluginId'] = Connector::getReference()->getId();
        }

        if (is_a(static::class, SinglePluginModelInterface::class, true)) {
            $data['id'] = $data['pluginId'];
        }

        return $data;
    }

    /**
     * @param array $data
     * @return static
     * @throws ReflectionException
     */
    protected static function deserialize(array $data): self
    {
        $hash = static::calcHash($data);
        if (isset(static::$loaded[$hash])) {
            return static::$loaded[$hash];
        }

        $system = ['id' => $data['id']];
        if (is_a(static::class, PluginModelInterface::class, true)) {
            $system['companyId'] = $data['companyId'];
            $system['pluginAlias'] = $data['pluginAlias'];
            $system['pluginId'] = $data['pluginId'];
        }

        $data = array_merge(static::afterRead($data), $system);

        $fields = array_keys(
            array_filter(static::schema(), fn($value) => is_array($value))
        );
        $fields[] = 'id';

        $class = static::class;
        $reflection = new ReflectionClass($class);

        /** @var ModelInterface|PluginModelInterface|SinglePluginModelInterface|Model $model */
        $model = $reflection->newInstanceWithoutConstructor();

        foreach ($fields as $field) {
            $model->{$field} = $data[$field];
        }

        static::$loaded[$hash] = $model;
        return $model;
    }

    private static function calcHash(array $data): string
    {
        $system = [
            'db' => spl_object_hash(static::db()),
            'table' => static::tableName(),
            'id' => $data['id']
        ];

        if (is_a(static::class, PluginModelInterface::class, true)) {
            $system['companyId'] = $data['companyId'];
            $system['pluginAlias'] = $data['pluginAlias'];
            $system['pluginId'] = $data['pluginId'];
        }

        return md5(implode('|', $system));
    }

    protected static function db(): Medoo
    {
        return Connector::db();
    }

}