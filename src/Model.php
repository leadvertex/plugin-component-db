<?php
/**
 * Created for plugin-component-db
 * Datetime: 05.02.2020 16:23
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db;


use DateTimeImmutable;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\Limit;
use Leadvertex\Plugin\Components\Db\Components\Sort;

abstract class Model
{

    /** @var string */
    private $companyId;

    /** @var string */
    private $feature;

    /** @var string */
    private $id;

    /** @var DateTimeImmutable */
    private $createdAt;

    /** @var DateTimeImmutable */
    private $updatedAt;

    /** @var array */
    private $data = [];

    /** @var bool */
    private $isNew;

    private static $select = ['companyId', 'feature', 'id', 'createdAt', 'updatedAt', 'data'];

    public function __construct(string $companyId, string $feature, string $id)
    {
        $this->companyId = $companyId;
        $this->feature = $feature;
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

    public function getFeature(): string
    {
        return $this->feature;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function save(): bool
    {
        $db = Connector::db();

        if ($this->isNew) {
            $db->insert(
                static::tableName(),
                [
                    'companyId' => $this->companyId,
                    'feature' => $this->feature,
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
                    'companyId' => $this->companyId,
                    'feature' => $this->feature,
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
                'companyId' => $this->companyId,
                'feature' => $this->feature,
                'id' => $this->id,
            ]
        );

        $this->isNew = true;
        return true;
    }

    public static function findOne(string $companyId, string $feature, string $id): ?self
    {
        $db = Connector::db();
        $data = $db->select(
            static::tableName(),
            self::$select,
            [
                'companyId' => $companyId,
                'feature' => $feature,
                'id' => $id,
            ]
        );

        if (empty($data)) {
            return null;
        }

        return static::hydrate($data[0]);
    }

    public static function findMany(string $companyId, string $feature, array $ids, Sort $sort = null): array
    {
        $db = Connector::db();

        $where = [
            'companyId' => $companyId,
            'feature' => $feature,
            'id' => $ids
        ];

        if ($sort) {
            $where['ORDER'] = $sort->get();
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

    public static function findInGroup(string $companyId, string $feature, Limit $limit = null, Sort $sort = null): array
    {
        $db = Connector::db();

        $where = [
            'companyId' => $companyId,
            'feature' => $feature,
        ];

        if ($limit) {
            $where['LIMIT'] = $limit->get();
        }

        if ($sort) {
            $where['ORDER'] = $sort->get();
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
            $data['feature'],
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
        return array_pop(explode('\\', static::class));
    }

}