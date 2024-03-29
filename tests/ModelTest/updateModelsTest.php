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
        Connector::config(
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
        TestModelClass::freeUpMemory();
        $model = TestModelClass::findById( 11);
        $model->value_1 = 12;
        $model->value_2 = "new text 3";
        $model->save();
        TestModelClass::freeUpMemory();
        $results = TestModelClass::findByCondition( ['value_2' => 'new text 3']);
        $this->assertArrayHasKey(11, $results);
        foreach ($results as $result)
        {
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestModelClass', $result);
            $this->assertEquals(11, $result->getId());
            $this->assertEquals(12, $result->value_1);
            $this->assertEquals('new text 3', $result->value_2);
        }
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
        TestPluginModelClass::freeUpMemory();
        $model = TestPluginModelClass::findById( 11);
        $model->value_1 = 12;
        $model->value_2 = "new text 3";
        $model->save();
        TestPluginModelClass::freeUpMemory();
        $result = TestPluginModelClass::findById( 11);
        $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass', $result);
        $this->assertEquals(11, $result->getId());
        $this->assertEquals(12, $result->value_1);
        $this->assertEquals('new text 3', $result->value_2);
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
        TestSinglePluginModelClass::freeUpMemory();
        $result = TestSinglePluginModelClass::find();
        $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass', $result);
        $this->assertEquals(2, $result->getId());
        $this->assertEquals(12, $result->value_1);
        $this->assertEquals('new text 3', $result->value_2);
    }

}