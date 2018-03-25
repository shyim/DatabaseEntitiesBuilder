<?php


/**
 * Class RepositoryTest
 * @author Soner Sayakci <shyim@posteo.de>
 */
class RepositoryTest extends AbstractBuilderTest
{
    /**
     * @var \DatabaseEntitiesBuilder\Models\Test1\Test1Repository
     */
    protected $repository;

    /**
     * @var int
     */
    protected $id = 1;

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function setUp()
    {
        parent::setUp();
        $this->repository = $this->buildService(\DatabaseEntitiesBuilder\Models\Test1\Test1Repository::class);
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function testFindByWithNoData()
    {
        $this->assertCount(0, $this->repository->findBy());
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
   public function testInsertEntity()
   {
       $entity = new \DatabaseEntitiesBuilder\Models\Test1\Test1();
       $entity->setName('Hayy');
       $entity->setSomedate(new DateTime());

       $this->assertNull($entity->getId());
       $this->repository->create($entity);

       $this->assertNotNull($entity->getId());
   }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     * @depends testInsertEntity
     */
   public function testFetchEntity()
   {
       $entity = $this->repository->find($this->id);

       $this->assertInstanceOf(\DatabaseEntitiesBuilder\Models\Test1\Test1::class, $entity);
       $this->assertEquals($this->id, $entity->getId());
       $this->assertEquals('Hayy', $entity->getName());
   }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     * @depends testFetchEntity
     */
   public function testDeleteEntity()
   {
       $entity = $this->repository->find($this->id);

       $this->assertInstanceOf(\DatabaseEntitiesBuilder\Models\Test1\Test1::class, $entity);

       $this->repository->remove($entity);

       $this->assertNull($this->repository->find($this->id));
   }
}