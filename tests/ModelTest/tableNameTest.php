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
        $result = TestModelClass::tableName();
        $expected = "TestModelClass";
        $this->assertEquals($expected, $result);
    }

    public function testTableNameTestPluginModelClass()
    {
        $result = TestPluginModelClass::tableName();
        $expected = "TestPluginModelClass";
        $this->assertEquals($expected, $result);
    }

    public function testTableNameTestSinglePluginModelClass()
    {
        $result = TestSinglePluginModelClass::tableName();
        $expected = "TestSinglePluginModelClass";
        $this->assertEquals($expected, $result);
    }
}