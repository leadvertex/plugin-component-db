<?php
/**
 * Created for plugin-component-db
 * Datetime: 06.02.2020 14:36
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db\Components;


use Medoo\Medoo;
use RuntimeException;

class Connector
{

    /** @var Medoo|null */
    protected static $db;

    /** @var string */
    protected static $companyId;

    private function __construct() {}

    public static function init(Medoo $medoo)
    {
        self::$db = $medoo;
    }

    public static function db(): Medoo
    {
        if (is_null(static::$db)) {
            throw new RuntimeException('Medoo was not configured', 1000);
        }

        return static::$db;
    }

    public static function getCompanyId(): ?string
    {
        return static::$companyId;
    }

    public static function setCompanyId(string $companyId)
    {
        static::$companyId = $companyId;
    }

}