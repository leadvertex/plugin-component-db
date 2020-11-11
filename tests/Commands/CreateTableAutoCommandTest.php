<?php
/**
 * Created for plugin-component-db
 * Datetime: 07.02.2020 17:19
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db\Commands;

use Leadvertex\Plugin\Components\Db\Components\Connector;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTableAutoCommandTest extends TestCase
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
    }

    public function testExecute()
    {
        $db = Connector::db();
        $command = new CreateTableAutoCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertSame(["00000", null, null], $db->error());
    }

}
