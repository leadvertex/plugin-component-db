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

    /** @var string */
    protected static $companyId;

    private function __construct() {}

    public static function init(Medoo $medoo, string $companyId)
    {
        self::$db = $medoo;
        self::$companyId = $companyId;
    }

    public static function db(): Medoo
    {
        return static::$db;
    }

    public static function companyId(): string
    {
        return static::$companyId;
    }

}