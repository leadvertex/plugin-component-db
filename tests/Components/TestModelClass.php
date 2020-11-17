<?php
/**
 * Created for plugin-component-db
 * Date: 17.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Db\Components;


use Leadvertex\Plugin\Components\Db\ModelInterface;
use Leadvertex\Plugin\Components\Db\ModelTrait;

class TestModelClass implements ModelInterface
{

    use ModelTrait;

    public static function schema(): array
    {
        return [];
    }
}