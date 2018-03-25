<?php


abstract class AbstractBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function setUp()
    {
        global $connection;

        $this->connection = $connection;
        parent::setUp();
    }

    /**
     * @param $className
     * @return mixed
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function buildService($className)
    {
        return new $className($this->connection);
    }
}