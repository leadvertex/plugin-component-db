<?php

namespace Test\Leadvertex\Plugin\Components\Db\Components;

use Leadvertex\Plugin\Components\Db\Model;

/**
 * Class TestModelClass
 * @package Test\Leadvertex\Plugin\Components\Db\Components
 *
 * @property $testData
 * @property $newData
 * @property $nameData
 * @property $phoneData
 */
class TestModelClass extends Model
{
    public static function tableName(): string
    {
        return 'TestingTable';
    }
}