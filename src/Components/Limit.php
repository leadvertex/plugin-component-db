<?php
/**
 * Created for plugin-component-db
 * Date: 07.02.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Db\Components;


class Limit
{

    /**
     * @var int
     */
    private $limit;
    /**
     * @var int|null
     */
    private $offset;

    public function __construct(int $limit, int $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * @return array|int
     */
    public function get()
    {
        if ($this->offset) {
            return [$this->limit, $this->offset];
        }
        return $this->limit;
    }

}