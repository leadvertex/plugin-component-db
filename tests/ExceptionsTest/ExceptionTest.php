<?php


namespace Leadvertex\Plugin\Components\Db\ExceptionsTest;


use Leadvertex\Plugin\Components\Db\Commands\CreateTablesCommand;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Leadvertex\Plugin\Components\Db\Components\TestModelClass;
use Leadvertex\Plugin\Components\Db\Exceptions\DatabaseException;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ExceptionTest extends TestCase
{

    public function setUp(): void
    {
        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );

        Connector::setReference(new PluginReference(1, 'user', 2));
    }

    public function testExceptionNotUniqueId()
    {
        $command = new CreateTablesCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        $model = new TestModelClass();
        $model->setId(11);
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';
        $model->save();
        $newModel = new TestModelClass();
        $newModel->setId(11);
        $newModel->value_1 = 1;
        $newModel->value_2 = 'Hello world 1';
        $result = '';
        try {
            $newModel->save();
        } catch (DatabaseException $e) {
            $result = $e->getMessage();
        }
        $expected = '23000: UNIQUE constraint failed: TestModelClass.id
INSERT INTO "TestModelClass" ("value_1", "value_2", "id") VALUES (1, \'Hello world 1\', \'11\')';
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testExceptionNoTable()
    {
        $model = new TestModelClass();
        $model->setId(11);
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';
        $result = '';
        try {
            $model->save();
        } catch (DatabaseException $e) {
            $result = $e->getMessage();
        }
        $expected = 'HY000: no such table: TestModelClass
INSERT INTO "TestModelClass" ("value_1", "value_2", "id") VALUES (11, \'Hello world 11\', \'11\')';
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }

    public function testExceptionTableIncorrectScheme()
    {
        Connector::db()->create('TestModelClass', ['value_1' => ['INT']]);

        $model = new TestModelClass();
        $model->setId(11);
        $model->value_1 = 11;
        $model->value_2 = 'Hello world 11';

        $result = '';
        TestModelClass::freeUpMemory();
        try {
            $model->save();
        } catch (DatabaseException $e) {
            $result = $e->getMessage();
            echo $result;
        }
        $expected = 'HY000: table TestModelClass has no column named value_2
INSERT INTO "TestModelClass" ("value_1", "value_2", "id") VALUES (11, \'Hello world 11\', \'11\')';
        $this->assertSame(str_replace("\r", '', $expected), $result);
    }
}