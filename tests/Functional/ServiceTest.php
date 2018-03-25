<?php


/**
 * Class ServiceTest
 * @author Soner Sayakci <shyim@posteo.de>
 */
class ServiceTest extends RepositoryTest
{
    /**
     * @var \DatabaseEntitiesBuilder\Models\Test1\Test1Service
     */
    protected $repository;

    /**
     * @var int
     */
    protected $id = 2;

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function setUp()
    {
        parent::setUp();
        $this->repository = new \DatabaseEntitiesBuilder\Models\Test1\Test1Service($this->buildService(\DatabaseEntitiesBuilder\Models\Test1\Test1Repository::class));
    }
}