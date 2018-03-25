<?php


use DatabaseEntitiesBuilder\Models\Test1\Test1;

class EntityTest extends AbstractBuilderTest
{
    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function testProperties()
    {
        $entity = new Test1();

        foreach (DatabaseReaderTest::COLUMN_NAMES as $name) {
            $this->assertTrue(property_exists($entity, $name));
        }
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function testMethodes()
    {
        $entity = new Test1();

        foreach (DatabaseReaderTest::COLUMN_NAMES as $name) {
            $this->assertTrue(method_exists($entity, 'get' . ucfirst($name)));
            $this->assertTrue(method_exists($entity, 'set' . ucfirst($name)));
        }
    }

    public function testDefaultValue()
    {
        $entity = new Test1();

        $this->assertEquals(':3', $entity->getNameWithDefault());
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     * @expectedException TypeError
     */
    public function testDateColumnsWithError()
    {
        $entity = new Test1;

        $this->assertNull($entity->getSomedate());
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function testDateColumnsWithoutError()
    {
        $entity = new Test1;

        $this->assertNull($entity->getSomedateNullable());
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     * @expectedException TypeError
     */
    public function testDateColumnsInvalidData()
    {
        $entity = new Test1();

        $entity->setSomedate("abc");
        $entity->setSomedateNullable("abc");
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function testGetterSetter()
    {
        $entity = new Test1();

        $data = [
            'id' => [
                'in' => 1,
                'out' => 1
            ],
            'name' => [
                'in' => 'Hayy',
                'out' => 'Hayy'
            ],
            'someDate' => [
                'in' => new DateTime('2018-03-25 00:00:00'),
                'out' => new DateTime('2018-03-25 00:00:00')
            ]
        ];

        foreach ($data as $column => $item) {
            $entity->{'set' . ucfirst($column)}($item['in']);
            $this->assertEquals($item['out'], $entity->{'get' . ucfirst($column)}());
        }
    }
}