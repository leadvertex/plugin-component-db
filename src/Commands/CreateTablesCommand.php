<?php
/**
 * Created for plugin-component-db
 * Datetime: 07.02.2020 17:14
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db\Commands;


use HaydenPierce\ClassFinder\ClassFinder;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Exceptions\DatabaseException;
use Leadvertex\Plugin\Components\Db\Model;
use Leadvertex\Plugin\Components\Db\ModelInterface;
use Leadvertex\Plugin\Components\Db\PluginModelInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTablesCommand extends Command
{

    public function __construct()
    {
        parent::__construct('db:create-tables');
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

            if (is_a($class, ModelInterface::class, true) && $class !== Model::class) {
                $table = $class::tableName();
                $output->writeln("Creating table '{$table}'");

                $schema = $class::schema();
                unset($schema['id']);

                $default = [
                    'id' => ['VARCHAR(255)', 'NOT NULL', 'PRIMARY KEY'],
                ];

                if (is_a($class, PluginModelInterface::class, true)) {
                    $default = [
                        'companyId' => ['INT', 'NOT NULL'],
                        'pluginAlias' => ['VARCHAR(255)', 'NOT NULL'],
                        'pluginId' => ['INT', 'NOT NULL'],
                        'id' => ['VARCHAR(255)', 'NOT NULL'],
                    ];

                    unset($schema['companyId']);
                    unset($schema['pluginAlias']);
                    unset($schema['pluginId']);
                    $schema[] = "PRIMARY KEY (<companyId>, <pluginAlias>, <pluginId>, <id>)";
                }

                $schema = array_merge($default, $schema);
                $db->create($table, $schema);
                DatabaseException::guard($db);
            }
        }

        return 0;
    }

}