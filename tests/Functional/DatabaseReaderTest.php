<?php


use Shyim\DatabaseEntitiesBuilder\Services\DatabaseReader;
use Shyim\DatabaseEntitiesBuilder\Structs\Request;
use Symfony\Component\DependencyInjection\Container;

class DatabaseReaderTest extends AbstractBuilderTest
{
    /**
     * @var DatabaseReader
     */
    private $reader;

    /**
     * @var \Shyim\DatabaseEntitiesBuilder\Structs\Table[]
     */
    private $tables;

    const COLUMN_NAMES = [
        'id',
        'name',
        'somedate',
        'nameNullable',
        'somedateNullable',
        'nameWithDefault',
    ];

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function setUp()
    {
        parent::setUp();
        $this->reader = new DatabaseReader($this->connection, new Container());
        $this->tables = $this->reader->buildSchema(new Request());
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function testColumnsCount()
    {
        $this->assertCount(6, $this->tables[0]->columns);
        $this->assertGreaterThanOrEqual(1, count($this->tables));
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function testColumnDefiniton()
    {
        // normalizedType
        $this->assertEquals('int', $this->tables[0]->columns[0]->normalizedType);
        $this->assertEquals('string', $this->tables[0]->columns[1]->normalizedType);
        $this->assertEquals('\DateTime', $this->tables[0]->columns[2]->normalizedType);
        $this->assertEquals('string', $this->tables[0]->columns[3]->normalizedType);
        $this->assertEquals('\DateTime', $this->tables[0]->columns[4]->normalizedType);
        $this->assertEquals('string', $this->tables[0]->columns[5]->normalizedType);

        // nullable
        $this->assertEquals(false, $this->tables[0]->columns[0]->nullable);
        $this->assertEquals(false, $this->tables[0]->columns[1]->nullable);
        $this->assertEquals(false, $this->tables[0]->columns[2]->nullable);
        $this->assertEquals(true, $this->tables[0]->columns[3]->nullable);
        $this->assertEquals(true, $this->tables[0]->columns[4]->nullable);
        $this->assertEquals(true, $this->tables[0]->columns[5]->nullable);

        // default value
        for ($i = 0; $i <= 4; $i++) {
            $this->assertEquals(null, $this->tables[0]->columns[$i]->default);
        }
        $this->assertEquals(':3', $this->tables[0]->columns[5]->default);

        // column names
        foreach (self::COLUMN_NAMES as $i => $name) {
            $this->assertEquals($name, $this->tables[0]->columns[$i]->name);
        }
    }
}