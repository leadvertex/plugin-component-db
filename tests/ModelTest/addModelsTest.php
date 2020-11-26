<?php


namespace Leadvertex\Plugin\Components\Db\ModelTest;


use InvalidArgumentException;
use Leadvertex\Plugin\Components\Db\Commands\CreateTablesCommand;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Leadvertex\Plugin\Components\Db\Components\TestModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestModelWithAfterAndBeforeClass;
use Leadvertex\Plugin\Components\Db\Components\TestModelWithArrayClass;
use Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass;
use Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass;
use Leadvertex\Plugin\Components\Db\Exceptions\DatabaseException;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class addModelsTest extends TestCase
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

    public function testAddTestModelClassAndFindById()
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
        $result = var_export(TestModelClass::findById( 11), true);
        $expected = "Leadvertex\Plugin\Components\Db\Components\TestModelClass::__set_state(array(
   'value_1' => 11,
   'value_2' => 'Hello world 11',
   'id' => '11',
   '_isNew' => false,
))";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testAddTestModelWithArrayClass()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);

        $model = new TestModelWithArrayClass();
        $model->setId(11);
        $model->value_1 = 11;
        $model->value_2 = 'hello';

        $result = '';
        TestModelWithArrayClass::freeUpMemory();
        try {
            $model->save();
        } catch (InvalidArgumentException $e) {
            $result = $e->getMessage();
        }

        $expected = "Field 'value_2' of 'Leadvertex\Plugin\Components\Db\Components\TestModelWithArrayClass' should be scalar or null";
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testAddTestModelClassGetId()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $id = 11;
        $model = new TestModelClass();
        $model->setId($id);
        $result = $model->getId();
        $expected = $id;
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testAddTestModelWithAfterAndBeforeClass()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $id = 11;
        $model = new TestModelWithAfterAndBeforeClass();
        $model->setId($id);
        $model->value_1 = 1;
        $model->value_2 = '2';
        $model->save();
        $this->assertEquals('Start save', TestModelWithAfterAndBeforeClass::$message);
        TestModelWithAfterAndBeforeClass::freeUpMemory();
        $result = var_export(TestModelWithAfterAndBeforeClass::findById($id), true);
        $this->assertEquals('Find complete', TestModelWithAfterAndBeforeClass::$message);
        $expected = "Leadvertex\Plugin\Components\Db\Components\TestModelWithAfterAndBeforeClass::__set_state(array(
   'value_1' => 1,
   'value_2' => '2',
   'id' => '11',
   '_isNew' => false,
))";
        $this->assertEquals(str_replace("\r", '', $expected), $result);
    }

    public function testAddTestModelClassWithNotUniqueId()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);

        $model = new TestModelClass();
        $model->setId(22);
        $model->value_1 = 22;
        $model->value_2 = 'Hello world 22';
        $model->save();

        TestModelClass::freeUpMemory();
        $newModel = new TestModelClass();
        $newModel->setId(22);
        $newModel->value_1 = 1;
        $newModel->value_2 = 'Hello world 1';

        $result = '';
        try {
            $newModel->save();
        } catch (DatabaseException $e) {
            $result = $e->getMessage();
        }
        $expected = '23000: UNIQUE constraint failed: TestModelClass.id
INSERT INTO "TestModelClass" ("value_1", "value_2", "id") VALUES (1, \'Hello world 1\', \'22\')';
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testAddTestPluginModelClassWithNotUniqueId()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);

        $model = new TestPluginModelClass();
        $model->setId(22);
        $model->value_1 = 22;
        $model->value_2 = 'Hello world 22';
        $model->save();

        TestPluginModelClass::freeUpMemory();
        $newModel = new TestPluginModelClass();
        $newModel->setId(22);
        $newModel->value_1 = 1;
        $newModel->value_2 = 'Hello world 1';

        $result = '';
        try {
            $newModel->save();
        } catch (DatabaseException $e) {
            $result = $e->getMessage();
        }
        $expected = '23000: UNIQUE constraint failed: TestPluginModelClass.companyId, TestPluginModelClass.pluginAlias, TestPluginModelClass.pluginId, TestPluginModelClass.id
INSERT INTO "TestPluginModelClass" ("value_1", "value_2", "id", "companyId", "pluginAlias", "pluginId") VALUES (1, \'Hello world 1\', \'22\', \'1\', \'user\', \'2\')';
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testAddTestSinglePluginModelClassWithNotUniqueId()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);

        $model = new TestSinglePluginModelClass();
        $model->value_1 = 6;
        $model->value_2 = 'Hello world 7';
        $model->save();

        TestPluginModelClass::freeUpMemory();
        $newModel = new TestSinglePluginModelClass();
        $newModel->value_1 = 1;
        $newModel->value_2 = 'Hello world 1';

        $result = '';
        try {
            $newModel->save();
        } catch (DatabaseException $e) {
            $result = $e->getMessage();
        }
        $expected = '23000: UNIQUE constraint failed: TestSinglePluginModelClass.companyId, TestSinglePluginModelClass.pluginAlias, TestSinglePluginModelClass.pluginId, TestSinglePluginModelClass.id
INSERT INTO "TestSinglePluginModelClass" ("value_1", "value_2", "id", "companyId", "pluginAlias", "pluginId") VALUES (1, \'Hello world 1\', \'2\', \'1\', \'user\', \'2\')';
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

}