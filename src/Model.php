<?php
/**
 * Created for plugin-component-db
 * Datetime: 05.02.2020 16:23
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db;


use DateTimeImmutable;
use Leadvertex\Plugin\Components\Db\Components\Connector;

abstract class Model
{

    /** @var string */
    protected $companyId;

    /** @var string */
    protected $id;

    /** @var string */
    protected $groupId;

    /** @var DateTimeImmutable */
    protected $createdAt;

    /** @var DateTimeImmutable */
    protected $updatedAt;

    /** @var array */
    protected $data = [];

    /** @var bool */
    private $isNew;

    private static $select = ['companyId', 'groupId', 'id', 'createdAt', 'updatedAt', 'data'];

    public function __construct(string $companyId, string $groupId, string $id)
    {
        $this->companyId = $companyId;
        $this->groupId = $groupId;
        $this->id = $id;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->isNew = true;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function save(): bool
    {
        $db = Connector::db();

        if ($this->isNew) {
            $db->insert(
                static::tableName(),
                [
                    'model' => $this::modelName(),
                    'companyId' => $this->companyId,
                    'groupId' => $this->groupId,
                    'createdAt' => $this->createdAt->getTimestamp(),
                    'updatedAt' => $this->updatedAt->getTimestamp(),
                    'data' => json_encode($this->data),
                ]
            );
        } else {
            $db->update(
                static::tableName(),
                [
                    'updatedAt' => $this->updatedAt->getTimestamp(),
                    'data' => json_encode($this->data),
                ],
                [
                    'model' => $this::modelName(),
                    'companyId' => $this->companyId,
                    'groupId' => $this->groupId,
                    'id' => $this->id,
                ]
            );
        }

        $this->isNew = false;
        return true;
    }

    public function delete(): bool
    {
        if ($this->isNew) {
            return false;
        }

        $db = Connector::db();
        $db->delete(
            static::tableName(),
            [
                'model' => $this::modelName(),
                'companyId' => $this->companyId,
                'groupId' => $this->groupId,
                'id' => $this->id,
            ]
        );

        $this->isNew = true;
        return true;
    }

    public static function findOne(string $companyId, string $groupId, string $id): ?self
    {
        $db = Connector::db();
        $data = $db->select(
            static::tableName(),
            self::$select,
            [
                'model' => static::modelName(),
                'companyId' => $companyId,
                'groupId' => $groupId,
                'id' => $id,
            ]
        );

        if (empty($data)) {
            return null;
        }

        return static::hydrate($data[0]);
    }

    public static function findMany(string $companyId, string $groupId, array $ids, $sort = null): array
    {
        $db = Connector::db();

        $where = [
            'model' => static::modelName(),
            'companyId' => $companyId,
            'groupId' => $groupId,
            'id' => $ids
        ];

        if ($sort) {
            $where['ORDER'] = $sort;
        }

        $data = $db->select(
            static::tableName(),
            self::$select,
            $where
        );

        return array_map(function (array $data) {
            return static::hydrate($data);
        }, $data);
    }

    public static function findInGroup(string $companyId, string $groupId, $limit = null, $sort = null): array
    {
        $db = Connector::db();

        $where = [
            'model' => static::modelName(),
            'companyId' => $companyId,
            'groupId' => $groupId,
        ];

        if ($limit) {
            $where['LIMIT'] = $limit;
        }

        if ($sort) {
            $where['ORDER'] = $sort;
        }

        $data = $db->select(
            static::tableName(),
            self::$select,
            $where
        );

        return array_map(function (array $data) {
            return static::hydrate($data);
        }, $data);
    }

    protected static function hydrate($data): self
    {
        $model = new static(
            $data['companyId'],
            $data['groupId'],
            $data['id']
        );

        $model->createdAt = new DateTimeImmutable($data['createdAt']);
        $model->updatedAt = new DateTimeImmutable($data['updatedAt']);
        $model->data = json_decode($data['data'], true);
        $model->isNew = false;

        return $model;
    }

    protected static function tableName(): string
    {
        return 'models';
    }

    protected static function modelName(): string
    {
        return array_pop(explode('\\', static::class));
    }

}