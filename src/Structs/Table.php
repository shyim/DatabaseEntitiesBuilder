<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder\Structs;

/**
 * Class Table
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class Table
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
     * @var Column[]
     */
    public $columns = [];

    /**
     * @var Column
     */
    public $primaryColumn;
}
