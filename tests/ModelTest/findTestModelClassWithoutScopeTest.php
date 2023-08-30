<?php

namespace Leadvertex\Plugin\Components\Db\ModelTest;

use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\TestModelClass;
use Medoo\Medoo;

class findTestModelClassWithoutScopeTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Connector::config(new Medoo([
            'database_type' => 'sqlite',
            'database_file' => __DIR__ . '/../../testDB.db'
        ]));
    }

    public function testFindByCondition()
    {
        $results = TestModelClass::findByCondition(['value_1' => '11'], false);
        $this->assertArrayHasKey('1', $results);
        $this->assertCount(1, $results);
        foreach ($results as $key => $result) {
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestModelClass', $result);
            $this->assertEquals(1, $result->getId());
            $this->assertEquals(11, $result->value_1);
            $this->assertEquals('Hello world 1', $result->value_2);
        }
    }

    public function testFindByConditionValue2()
    {
        $results = TestModelClass::findByCondition(['value_2' => 'Hello world'], false);
        $this->assertCount(2, $results);
        foreach ($results as $key => $result)
        {
            $this->assertArrayHasKey($key, $results);
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestModelClass', $result);
            $this->assertEquals($key, $result->getId());
            $this->assertEquals($key.$key, $result->value_1);
            $this->assertEquals('Hello world', $result->value_2);
        }
    }

    public function testFindByConditionNotFound()
    {
        $this->assertEmpty(TestModelClass::findByCondition(['value_1' => '111'], false));
    }

    public function testFindByConditionTwoFilter()
    {
        $this->assertEmpty(TestModelClass::findByCondition(['value_1' => '11', 'value_2' => 'Hello world'], false));
    }

    public function testFindByConditionAllModel()
    {
        $results = TestModelClass::findByCondition([], false);
        $this->assertCount(3, $results);
        foreach ($results as $key => $result)
        {
            $this->assertArrayHasKey($key, $results);
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestModelClass', $result);
            $this->assertEquals($key, $result->getId());
            $this->assertEquals($key.$key, $result->value_1);
            if ($key === 1) {
                $this->assertEquals('Hello world 1', $result->value_2);
            } else {
                $this->assertEquals('Hello world', $result->value_2);
            }
        }
    }

    public function testFindByConditionNull()
    {
        $result = var_export(TestModelClass::findByCondition([null], false), true);
        $expected = "array (
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }
}