<?php
/**
 * Created for plugin-component-db
 * Datetime: 06.02.2020 16:53
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db\Commands;

use Leadvertex\Plugin\Components\Db\Components\Connector;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTableCommandTest extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Connector::init(new Medoo([
            'database_type' => 'sqlite',
            'database_file' => ':memory:'
        ]));
    }

    public function testExecute()
    {
        $db = Connector::db();
        $command = new CreateTableCommand();
        $tester = new CommandTester($command);
        $tester->execute(['table' => 'models']);

        $this->assertSame(["00000", null, null], $db->error());

        $db->insert('models', [
            'companyId' => '1',
            'feature' => '2',
            'id' => '3',
            'data' => '4',
            'createdAt' => '5',
            'updatedAt' => '6',
        ]);

        $this->assertSame(["00000", null, null], $db->error());
    }

}
