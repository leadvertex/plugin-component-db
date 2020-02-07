<?php
/**
 * Created for plugin-component-db
 * Date: 07.02.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Db\Components;


use InvalidArgumentException;

class Sort
{

    const ASC = 'ASC';
    const DESC = 'DESC';

    const BY_ID = 'id';
    const BY_FEATURE = 'feature';
    const BY_CREATED_AT = 'createdAt';
    const BY_UPDATED_AT = 'updatedAt';
    const BY_TAG_1 = 'tag_1';
    const BY_TAG_2 = 'tag_2';
    const BY_TAG_3 = 'tag_3';

    private $sort = [];

    public function __construct(string $field, string $direction)
    {
        $this->addSort($field, $direction);
    }

    public function addSort(string $field, string $direction)
    {
        $by = [
            self::BY_FEATURE, self::BY_ID, self::BY_CREATED_AT, self::BY_UPDATED_AT,
            self::BY_TAG_1, self::BY_TAG_2, self::BY_TAG_3
        ];

        if (!in_array($field, $by)) {
            throw new InvalidArgumentException("Invalid sort field '{$field}'");
        }

        if (!in_array($direction, [self::ASC, self::DESC])) {
            throw new InvalidArgumentException("Invalid sort direction '{$direction}'");
        }

        $this->sort[$field] = $direction;
    }

    public function get(): array
    {
        return $this->sort;
    }

}