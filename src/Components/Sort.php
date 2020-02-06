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

    private $sort = [];

    public function __construct(string $field, string $direction)
    {
        $this->addSort($field, $direction);
    }

    public function addSort(string $field, string $direction)
    {
        if (!in_array($direction, [self::ASC, self::DESC])) {
            throw new InvalidArgumentException("Invalid direction '{$direction}'");
        }
        $this->sort[$field] = $direction;
    }

    public function get(): array
    {
        return $this->sort;
    }

}