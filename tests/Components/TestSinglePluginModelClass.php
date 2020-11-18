<?php
/**
 * Created for plugin-component-db
 * Date: 17.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Db\Components;


use Leadvertex\Plugin\Components\Db\ModelTrait;
use Leadvertex\Plugin\Components\Db\SinglePluginModelInterface;
use Leadvertex\Plugin\Components\Db\SinglePluginModelTrait;

class TestSinglePluginModelClass implements SinglePluginModelInterface
{

    use ModelTrait, SinglePluginModelTrait;

    public static function schema(): array
    {
        return [
            'value_1' => ['INT'],
            'value_2' => ['VARCHAR(255)'],
        ];
    }
}