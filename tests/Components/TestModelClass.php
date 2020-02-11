<?php

namespace Test\Leadvertex\Plugin\Components\Db\Components;

use Leadvertex\Plugin\Components\Db\Model;

class TestModelClass extends Model
{
    public static function tableName(): string
    {
        return 'TestingTable';
    }
}