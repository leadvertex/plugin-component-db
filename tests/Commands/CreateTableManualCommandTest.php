<?php
/**
 * Created for plugin-component-db
 * Datetime: 06.02.2020 16:53
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Test\Leadvertex\Plugin\Components\Db\Commands;

use Leadvertex\Plugin\Components\Db\Commands\CreateTableManualCommand;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTableManualCommandTest extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ]),
            1
        );
    }

    public function testExecute()
    {
        $db = Connector::db();
        $command = new CreateTableManualCommand();
        $tester = new CommandTester($command);
        $tester->execute(['table' => 'models']);

        $this->assertSame(["00000", null, null], $db->error());

        $db->insert('models', [
            'companyId' => '1',
            'feature' => '2',
            'id' => '3',
            'tag_1' => '4',
            'tag_2' => '5',
            'tag_3' => '6',
            'data' => '7',
            'createdAt' => '8',
            'updatedAt' => '9',
        ]);

        $this->assertSame(["00000", null, null], $db->error());
    }

}
