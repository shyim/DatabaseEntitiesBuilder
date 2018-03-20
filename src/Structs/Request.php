<?php


namespace Shyim\DatabaseEntitiesBuilder\Structs;
use Shyim\DatabaseEntitiesBuilder\Generator;

/**
 * Class Request
 * @author Soner Sayakci <shyim@posteo.de>
 */
class Request
{
    // Required
    public $namespace;
    public $folder;

    // Optional
    public $phpVersion = Generator::PHP71;
    public $filter;
    public $removePrefixEntity;
}