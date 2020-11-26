<?php
/**
 * Created for plugin-component-db
 * Date: 17.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Db\Components;


use Leadvertex\Plugin\Components\Db\Model;
use Leadvertex\Plugin\Components\Db\SinglePluginModelInterface;

class TestSinglePluginModelClass extends Model implements SinglePluginModelInterface
{
    public int $value_1;

    public string $value_2;

    public static function schema(): array
    {
        return [
            'value_1' => ['INT'],
            'value_2' => ['VARCHAR(255)'],
        ];
    }
}