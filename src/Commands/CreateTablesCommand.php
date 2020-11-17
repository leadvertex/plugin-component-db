<?php
/**
 * Created for plugin-component-db
 * Datetime: 07.02.2020 17:14
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db\Commands;


use HaydenPierce\ClassFinder\ClassFinder;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\ModelInterface;
use Leadvertex\Plugin\Components\Db\PluginModelInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTablesCommand extends Command
{

    public function __construct()
    {
        parent::__construct('db:create-table-auto');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ModelInterface[] $classes */
        $classes = ClassFinder::getClassesInNamespace(
            'Leadvertex\Plugin',
            ClassFinder::RECURSIVE_MODE
        );

        $db = Connector::db();
        foreach ($classes as $class) {

            if (is_a($class, ModelInterface::class, true)) {
                $table = $class::tableName();
                $output->writeln("Creating table '{$table}'");

                $schema = $class::schema();
                $schema['id'] = [
                    'VARCHAR(255)',
                    'NOT NULL'
                ];

                if (is_a(static::class, PluginModelInterface::class, true)) {
                    $schema = array_merge($schema, [
                        'companyId' => ['INT', 'NOT NULL'],
                        'pluginAlias' => ['VARCHAR(255)', 'NOT NULL'],
                        'pluginId' => ['INT', 'NOT NULL'],
                    ]);
                }

                $db->create($table, $schema);
            }
        }

        return 0;
    }

}