<?php


namespace Leadvertex\Plugin\Components\Db\ModelTest;


use Exception;
use Leadvertex\Plugin\Components\Db\Commands\CreateTablesCommand;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Leadvertex\Plugin\Components\Db\Components\TestModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class updateModelsTest extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );

        Connector::setReference(new PluginReference(1, 'user', 2));
    }

    public function testUpdateTestModelClassAndFindById()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $model = new TestModelClass();
        $model->setId(11);
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';
        $model->save();
        $model = TestModelClass::findById( 11);
        $model->value_1 = 12;
        $model->value_2 = "new text 3";
        $result = var_export(TestModelClass::findByCondition( ['value_2' => 'Hello world 11'])[11], true);
        $expected = "Leadvertex\Plugin\Components\Db\Components\TestModelClass::__set_state(array(
   'value_1' => 12,
   'value_2' => 'new text 3',
   'id' => '11',
   '_isNew' => false,
))";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testUpdateTestPluginModelClassAndFindById()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $model = new TestPluginModelClass();
        $model->setId(11);
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';
        $model->save();
        $model = TestPluginModelClass::findById( 11);
        $model->value_1 = 12;
        $model->value_2 = "new text 3";
        $model->save();
        $result = var_export(TestPluginModelClass::findById( 11), true);
        $expected = "Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass::__set_state(array(
   'value_1' => 12,
   'value_2' => 'new text 3',
   'id' => '11',
   '_isNew' => false,
))";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testUpdateTestSinglePluginModelClassAndFindById()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $model = new TestSinglePluginModelClass();
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';
        $model->save();
        TestSinglePluginModelClass::freeUpMemory();
        $model = TestSinglePluginModelClass::find();
        $model->value_1 = 12;
        $model->value_2 = "new text 3";
        $model->save();
        $result = var_export(TestSinglePluginModelClass::find(), true);
        $expected = "Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass::__set_state(array(
   'value_1' => 12,
   'value_2' => 'new text 3',
   'id' => '2',
   '_isNew' => false,
))";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

}