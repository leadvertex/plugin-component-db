<?php


namespace Leadvertex\Plugin\Components\Db\ModelTest;


use Leadvertex\Plugin\Components\Db\Components\TestModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass;
use PHPUnit\Framework\TestCase;

class tableNameTest extends TestCase
{

    public function testTableNameTestModelClass()
    {
        $this->assertEquals('TestModelClass', TestModelClass::tableName());
    }

    public function testTableNameTestPluginModelClass()
    {
        $this->assertEquals('TestPluginModelClass', TestPluginModelClass::tableName());
    }

    public function testTableNameTestSinglePluginModelClass()
    {
        $this->assertEquals('TestSinglePluginModelClass', TestSinglePluginModelClass::tableName());
    }
}