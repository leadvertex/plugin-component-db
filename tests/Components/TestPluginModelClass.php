<?php
/**
 * Created for plugin-component-db
 * Date: 17.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Db\Components;


use Leadvertex\Plugin\Components\Db\ModelTrait;
use Leadvertex\Plugin\Components\Db\PluginModelInterface;

class TestPluginModelClass implements PluginModelInterface
{

    use ModelTrait;

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public static function schema(): array
    {
        return [
            'value_1' => ['INT'],
            'value_2' => ['VARCHAR(255)'],
        ];
    }
}