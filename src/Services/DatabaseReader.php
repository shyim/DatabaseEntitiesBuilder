<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder\Services;

use Doctrine\DBAL\Connection;
use Shyim\DatabaseEntitiesBuilder\Structs\Column;
use Shyim\DatabaseEntitiesBuilder\Structs\Request;
use Shyim\DatabaseEntitiesBuilder\Structs\Table;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DatabaseReader
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * DatabaseReader constructor.
     *
     * @param Connection $connection
     * @param ContainerInterface $container
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(Connection $connection, ContainerInterface $container)
    {
        $this->connection = $connection;
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @return array
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function buildSchema(Request $request): array
    {
        if ($request->filter !== null) {
            $tables = $this->connection->fetchAll(
                'SHOW TABLES WHERE Tables_in_' . $this->connection->getDatabase() . ' LIKE ?',
                ['%' . $request->filter . '%']
            );
        } else {
            $tables = $this->connection->fetchAll('SHOW TABLES');
        }
        $result = [];
        $configuration = [];

        if ($this->container->hasParameter('model_generator')) {
            $configuration = $this->container->getParameter('model_generator');
        }

        foreach ($tables as $tableRow) {
            $table = new Table();
            $table->name = array_values($tableRow)[0];

            if ($request->removePrefixEntity) {
                $table->camelCaseName = $this->camelCase(str_replace($request->removePrefixEntity, '', $table->name));
            } else {
                $table->camelCaseName = $this->camelCase($table->name);
            }

            $table->columns = [];

            $columnsRaw = $this->connection->fetchAll('SHOW COLUMNS FROM ' . $table->name);

            foreach ($columnsRaw as $item) {
                $column = new Column();
                $column->name = $item['Field'];
                $column->camelCaseName = $this->camelCase($item['Field']);
                $column->type = $item['Type'];
                $column->normalizedType = $this->normalizeType($item['Type']);
                $column->default = $item['Default'];
                $column->auto_increment = $item['Extra'] === 'auto_increment';
                $column->nullable = $item['Null'] === 'YES';

                if (isset($configuration[$table->name][$column->name]) && $configuration[$table->name][$column->name] === 'json_array') {
                    $column->normalizedType = 'array';
                    $column->type = 'json';
                }

                if ($item['Key'] === 'PRI') {
                    $table->primaryColumn = $column;
                }

                $table->columns[] = $column;
            }

            if ($table->primaryColumn === null) {
                throw new \RuntimeException(sprintf('Table with name %s has no primary column', $table->name));
            }

            $result[] = $table;
        }

        return $result;
    }

    public function camelCase($str, array $noStrip = [])
    {
        $str = ucwords(trim(preg_replace('/[^a-z0-9' . implode('', $noStrip) . ']+/i', ' ', $str)));
        $str = ucfirst(str_replace(' ', '', $str));

        return $str;
    }

    /**
     * @param string $type
     * @return string
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function normalizeType(string $type)
    {
        if (0 === strpos($type, 'int')) {
            return 'int';
        } elseif ($type === 'date' || $type === 'datetime') {
            return '\DateTime';
        } elseif (0 === strpos($type, 'varchar')) {
            return 'string';
        } else {
            return 'string';
        }
    }
}
