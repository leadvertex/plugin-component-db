<?php


namespace Leadvertex\Plugin\Components\Db\ModelTest;


use Leadvertex\Plugin\Components\Db\Commands\CreateTablesCommand;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Leadvertex\Plugin\Components\Db\Components\TestModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class deleteModelsTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Connector::config(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );

        Connector::setReference(new PluginReference(1, 'user', 2));

        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testDeleteTestModelClass()
    {
        $model = new TestModelClass();
        $model->setId(11);
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';
        $model->save();
        TestPluginModelClass::freeUpMemory();
        $modelNew = new TestModelClass();
        $modelNew->setId(12);
        $modelNew->value_1 = 11;
        $modelNew->value_2 = 'Hello world 11';
        $modelNew->save();
        $model->delete();
        $result = var_export(TestModelClass::findByCondition(['value_2' => 'Hello world 11']), true);
        $expected = "array (
  12 => 
  Leadvertex\Plugin\Components\Db\Components\TestModelClass::__set_state(array(
     'value_1' => 11,
     'value_2' => 'Hello world 11',
     'id' => '12',
     '_isNew' => false,
  )),
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testDeleteTestPluginModelClass()
    {
        $model = new TestPluginModelClass();
        $model->setId(11);
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';
        $model->save();
        TestPluginModelClass::freeUpMemory();
        $modelNew = new TestPluginModelClass();
        $modelNew->setId(12);
        $modelNew->value_1 = 11;
        $modelNew->value_2 = 'Hello world 11';
        $modelNew->save();
        $model->delete();
        $result = var_export(TestPluginModelClass::findByCondition(['value_2' => 'Hello world 11']), true);
        $expected = "array (
  12 => 
  Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass::__set_state(array(
     'value_1' => 11,
     'value_2' => 'Hello world 11',
     'id' => '12',
     '_isNew' => false,
  )),
)";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testDeleteTestSinglePluginModelClass()
    {
        $model = new TestSinglePluginModelClass();
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';
        $model->save();
        TestSinglePluginModelClass::freeUpMemory();
        $modelNew = TestSinglePluginModelClass::find();
        $modelNew->delete();
        $result = var_export(TestSinglePluginModelClass::find(), true);
        $expected = "NULL";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

}