<?php
/**
 * Created for plugin-component-db
 * Datetime: 06.02.2020 15:53
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db\Commands;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTableCommand extends Command
{

    public function __construct()
    {
        parent::__construct('db:create-table');
        $this->addArgument(
            'table',
            InputArgument::REQUIRED,
            'Table will be created with passed name'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = Connector::db();
        $db->create(
            $input->getArgument('table'),
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
                'data' => [
                    'TEXT',
                ],
                'createdAt' => [
                    'INT',
                    'NOT NULL'
                ],
                'updatedAt' => [
                    'INT',
                    'NOT NULL'
                ],
                'PRIMARY KEY (<companyId>, <feature>, <id>)'
            ]
        );
        return 0;
    }

}