<?php


namespace Leadvertex\Plugin\Components\Db\ModelTest;


use Leadvertex\Plugin\Components\Db\Components\TestModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{

    public function testSchemaTestModelClass()
    {
        $result = TestModelClass::schema();
        $expected = [
            'value_1' => ['INT'],
            'value_2' => ['VARCHAR(255)'],
        ];
        $this->assertSame($expected, $result);
    }

    public function testSchemaTestPluginModelClass()
    {
        $result = TestPluginModelClass::schema();
        $expected = [
            'value_1' => ['INT'],
            'value_2' => ['VARCHAR(255)'],
        ];
        $this->assertSame($expected, $result);
    }

    public function testSchemaTestSinglePluginModelClass()
    {
        $result = TestSinglePluginModelClass::schema();
        $expected = [
            'value_1' => ['INT'],
            'value_2' => ['VARCHAR(255)'],
        ];
        $this->assertSame($expected, $result);
    }
}