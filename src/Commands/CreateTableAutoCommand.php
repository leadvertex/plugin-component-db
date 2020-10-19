<?php
/**
 * Created for plugin-component-db
 * Datetime: 07.02.2020 17:14
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db\Commands;


use HaydenPierce\ClassFinder\ClassFinder;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Model;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTableAutoCommand extends Command
{

    public function __construct()
    {
        parent::__construct('db:create-table-auto');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Model[] $classes */
        $classes = ClassFinder::getClassesInNamespace(
            'Leadvertex\Plugin',
            ClassFinder::RECURSIVE_MODE
        );

        foreach ($classes as $class) {
            if ($class === Model::class) {
                continue;
            }

            if (is_a($class, Model::class, true)) {
                $table = $class::tableName();
                $output->writeln("Creating table '{$table}'");
                $this->createTable($table);
            }
        }

        return 0;
    }

    private function createTable(string $name)
    {
        $db = Connector::db();
        $db->create(
            $name,
            [
                'companyId' => [
                    'INT',
                    'NOT NULL'
                ],
                'feature' => [
                    'VARCHAR(255)',
                    'NOT NULL'
                ],
                'id' => [
                    'VARCHAR(255)',
                    'NOT NULL'
                ],
                'tag_1' => [
                    'VARCHAR(255)',
                ],
                'tag_2' => [
                    'VARCHAR(255)',
                ],
                'tag_3' => [
                    'VARCHAR(255)',
                ],
                'data' => [
                    'TEXT',
                ],
                'createdAt' => [
                    'INT',
                    'NOT NULL'
                ],
                'updatedAt' => [
                    'INT',
                ],
                'PRIMARY KEY (<companyId>, <feature>, <id>)'
            ]
        );
    }

}