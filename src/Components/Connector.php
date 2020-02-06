<?php
/**
 * Created for plugin-component-db
 * Datetime: 06.02.2020 14:36
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db\Components;


use Medoo\Medoo;

class Connector
{

    /** @var Medoo|null */
    protected static $db;

    private function __construct()
    {

    }

    public static function init(Medoo $medoo)
    {
        self::$db = $medoo;
    }

    public static function db(): Medoo
    {
        return static::$db;
    }

}