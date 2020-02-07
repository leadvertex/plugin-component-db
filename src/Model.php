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
use Ramsey\Uuid\Uuid;

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

    /** @var string */
    private $tag_1;

    /** @var string */
    private $tag_2;

    /** @var string */
    private $tag_3;

    /** @var array */
    private $data = [];

    /** @var bool */
    private $isNew;

    private static $select = ['companyId', 'feature', 'id', 'createdAt', 'updatedAt', 'tag_1', 'tag_2', 'tag_3', 'data'];

    public function __construct(string $companyId, string $id = null, string $feature = '')
    {
        $this->companyId = $companyId;
        $this->feature = $feature;

        $this->id = $id;
        if ($id === '' || is_null($id)) {
            $this->id = Uuid::uuid4()->toString();
        }

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

    public function setUpdatedAt(DateTimeImmutable $dateTime)
    {
        $this->updatedAt = $dateTime;
    }

    public function getTag1(): string
    {
        return $this->tag_1;
    }

    public function setTag1(string $tag_1): void
    {
        $this->tag_1 = $tag_1;
    }

    public function getTag2(): string
    {
        return $this->tag_2;
    }

    public function setTag2(string $tag_2): void
    {
        $this->tag_2 = $tag_2;
    }

    public function getTag3(): string
    {
        return $this->tag_3;
    }

    public function setTag3(string $tag_3): void
    {
        $this->tag_3 = $tag_3;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
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
                    'id' => $this->id,
                    'createdAt' => $this->createdAt->getTimestamp(),
                    'updatedAt' => $this->updatedAt->getTimestamp(),
                    'tag_1' => $this->tag_1,
                    'tag_2' => $this->tag_2,
                    'tag_3' => $this->tag_3,
                    'data' => json_encode($this->data),
                ]
            );
        } else {
            $db->update(
                static::tableName(),
                [
                    'tag_1' => $this->tag_1,
                    'tag_2' => $this->tag_2,
                    'tag_3' => $this->tag_3,
                    'data' => json_encode($this->data),
                    'updatedAt' => $this->updatedAt->getTimestamp(),
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

    public static function findById(string $companyId, string $id, string $feature = ''): ?self
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

    public static function findByIds(string $companyId, array $ids, string $feature = ''): array
    {
        $db = Connector::db();

        $where = [
            'companyId' => $companyId,
            'feature' => $feature,
            'id' => $ids
        ];

        $data = $db->select(
            static::tableName(),
            self::$select,
            $where
        );

        return array_map(function (array $data) {
            return static::hydrate($data);
        }, $data);
    }

    public static function findMany(
        string $companyId,
        array $feature = [],
        array $tag_1 = [],
        array $tag_2 = [],
        array $tag_3 = [],
        Limit $limit = null,
        Sort $sort = null
    ): array
    {
        $db = Connector::db();

        $where = [
            'companyId' => $companyId,
            'feature' => $feature,
            'tag_1' => $tag_1,
            'tag_2' => $tag_2,
            'tag_3' => $tag_3,
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
            $data['id'],
            $data['feature']
        );

        $model->createdAt = new DateTimeImmutable("@{$data['createdAt']}");
        $model->updatedAt = new DateTimeImmutable("@{$data['updatedAt']}");
        $model->tag_1 = $data['tag_1'];
        $model->tag_2 = $data['tag_2'];
        $model->tag_3 = $data['tag_3'];
        $model->data = json_decode($data['data'], true);
        $model->isNew = false;

        return $model;
    }

    protected static function tableName(): string
    {
        return array_pop(explode('\\', static::class));
    }

}