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

class isNewModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUpBeforeClass();
        Connector::config(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );

        Connector::setReference(new PluginReference(1, 'user', 2));
    }

    public function testIsNewModelTestModelClass()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $id = 11;
        $model = new TestModelClass();
        $model->setId($id);
        $model->value_1 = 1;
        $model->value_2 = "2";
        $result = $model->isNewModel();
        $expected = true;
        $this->assertEquals($expected, $result);
        $model->save();
        TestModelClass::freeUpMemory();
        $model = TestModelClass::findById($id);
        $result = $model->isNewModel();
        $expected = false;
        $this->assertEquals($expected, $result);
    }

    public function testIsNewModelTestPluginModelClass()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $id = 11;
        $model = new TestPluginModelClass();
        $model->setId($id);
        $model->value_1 = 1;
        $model->value_2 = "2";
        $result = $model->isNewModel();
        $expected = true;
        $this->assertEquals($expected, $result);
        $model->save();
        TestPluginModelClass::freeUpMemory();
        $model = TestPluginModelClass::findById($id);
        $result = $model->isNewModel();
        $expected = false;
        $this->assertEquals($expected, $result);
    }

    public function testIsNewModelTestSinglePluginModelClass()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $model = new TestSinglePluginModelClass();
        $model->value_1 = 1;
        $model->value_2 = "2";
        $result = $model->isNewModel();
        $expected = true;
        $this->assertEquals($expected, $result);
        $model->save();
        TestSinglePluginModelClass::freeUpMemory();
        $model = TestSinglePluginModelClass::find();
        $result = $model->isNewModel();
        $expected = false;
        $this->assertEquals($expected, $result);
    }
}