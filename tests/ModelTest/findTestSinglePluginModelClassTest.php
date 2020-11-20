<?php


namespace Leadvertex\Plugin\Components\Db\ModelTest;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;

class findTestSinglePluginModelClassTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Connector::init(new Medoo([
            'database_type' => 'sqlite',
            'database_file' => __DIR__ . '/../../testDB.db'
        ]));

        Connector::setReference(new PluginReference(1, 'user', 2));
    }

    public function testFindByCondition()
    {
        $result = var_export(TestSinglePluginModelClass::findByCondition(['value_1' => '10']), true);
        $expected = "array (
  2 => 
  Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass::__set_state(array(
     'value_1' => 10,
     'value_2' => 'Hello world',
     'id' => '2',
     '_isNew' => false,
  )),
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByConditionValue2()
    {
        $result = var_export(TestSinglePluginModelClass::findByCondition(['value_2' => 'Hello world']), true);
        $expected = "array (
  2 => 
  Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass::__set_state(array(
     'value_1' => 10,
     'value_2' => 'Hello world',
     'id' => '2',
     '_isNew' => false,
  )),
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByConditionNotFount()
    {
        $result = var_export(TestSinglePluginModelClass::findByCondition(['value_1' => '111']), true);
        $expected = "array (
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByConditionTwoFilter()
    {
        $result = var_export(TestSinglePluginModelClass::findByCondition(['value_1' => '11', 'value_2' => 'Hello world']), true);
        $expected = "array (
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByConditionEmpty()
    {
        $result = var_export(TestSinglePluginModelClass::findByCondition([]), true);
        $expected = "array (
  2 => 
  Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass::__set_state(array(
     'value_1' => 10,
     'value_2' => 'Hello world',
     'id' => '2',
     '_isNew' => false,
  )),
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByConditionNull()
    {
        $result = var_export(TestSinglePluginModelClass::findByCondition([null]), true);
        $expected = "array (
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByIds()
    {
        $result = var_export(TestSinglePluginModelClass::findByIds([2, 1]), true);
        $expected = "array (
  2 => 
  Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass::__set_state(array(
     'value_1' => 10,
     'value_2' => 'Hello world',
     'id' => '2',
     '_isNew' => false,
  )),
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByIdsWithNotExistId()
    {
        $result = var_export(TestSinglePluginModelClass::findByIds([2, 1, 11]), true);
        $expected = "array (
  2 => 
  Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass::__set_state(array(
     'value_1' => 10,
     'value_2' => 'Hello world',
     'id' => '2',
     '_isNew' => false,
  )),
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByIdsNotFound()
    {
        $result = var_export(TestSinglePluginModelClass::findByIds([11]), true);
        $expected = "array (
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByIdsEmpty()
    {
        $result = var_export(TestSinglePluginModelClass::findByIds([]), true);
        $expected = "array (
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByIdsNull()
    {
        $result = var_export(TestSinglePluginModelClass::findByIds([null]), true);
        $expected = "array (
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindById()
    {
        $result = var_export(TestSinglePluginModelClass::findById( 2), true);
        $expected = "Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass::__set_state(array(
   'value_1' => 10,
   'value_2' => 'Hello world',
   'id' => '2',
   '_isNew' => false,
))";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFindByIdNotFound()
    {
        $result = var_export(TestSinglePluginModelClass::findById( 11), true);
        $expected = 'NULL';
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testFind()
    {
        $result = var_export(TestSinglePluginModelClass::find(), true);
        $expected = "Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass::__set_state(array(
   'value_1' => 10,
   'value_2' => 'Hello world',
   'id' => '2',
   '_isNew' => false,
))";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }
}