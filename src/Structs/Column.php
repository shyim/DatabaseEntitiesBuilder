<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder\Structs;

/**
 * Class Column
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class Column
{
    /**
     * Table name
     *
     * @var string
     */
    public $name;

    /**
     * CamelCase Table name
     *
     * @var string
     */
    public $camelCaseName;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $normalizedType;

    /**
     * @var bool
     */
    public $nullable;

    /**
     * @var string
     */
    public $default;

    /**
     * @var bool
     */
    public $auto_increment;
}
