<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder\Services;

use Shyim\DatabaseEntitiesBuilder\Generator;
use Shyim\DatabaseEntitiesBuilder\Structs\Request;
use Shyim\DatabaseEntitiesBuilder\Structs\Table;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

/**
 * Class RepositoryGenerator
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class RepositoryGenerator
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     * @param Table $table
     * @return PhpNamespace
     * @author Soner Sayakci <shyim@posteo.de>
     * @throws \Nette\InvalidArgumentException
     */
    public function generate(Request $request, Table $table): PhpNamespace
    {
        $this->request = $request;

        $phpNamespace = new PhpNamespace($request->namespace . '\\' . $table->camelCaseName);
        $phpNamespace->addUse($request->namespace . '\AbstractRepository');
        $phpNamespace->addUse('Doctrine\DBAL\Connection');

        $class = $phpNamespace->addClass($table->camelCaseName . 'Repository');
        $class->setExtends($request->namespace . '\AbstractRepository');
        $class->addComment(sprintf('Repository for table %s', $table->name));
        $class->addConstant('TABLE', $table->name)
            ->addComment('Table name')
            ->addComment('@var string');

        $class->addProperty('connection')
            ->addComment('@var Connection')
            ->setVisibility('private');

        $constructor = $class->addMethod('__construct')
            ->setBody('$this->connection = $connection;')
            ->addComment($class->getName() . ' constructor.')
            ->addComment('@param Connection $connection');

        $con = $constructor->addParameter('connection');
        $con->setTypeHint('Doctrine\DBAL\Connection');

        $this->findBy($table, $class);
        $this->findOneBy($table, $class);
        $this->find($table, $class);
        $this->create($table, $class);
        $this->update($table, $class);
        $this->remove($table, $class);
        $this->getDatabaseArrayFromEntity($table, $class);
        $this->getEntityFromDatabaseArray($table, $class);

        return $phpNamespace;
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function findBy(Table $table, ClassType $class)
    {
        $method = $class->addMethod('findBy')
            ->setReturnType('array')
            ->addComment('Fetches all records.')
            ->addComment('@param int|null $offset')
            ->addComment('@param int|null $limit')
            ->addComment('@param array|null $where')
            ->addComment('@param array|null $sorters')
            ->addComment('@return ' . $table->camelCaseName . '[]');

        $method
            ->addParameter('offset')
            ->setTypeHint('int')
            ->setDefaultValue(null);

        $method
            ->addParameter('limit')
            ->setTypeHint('int')
            ->setDefaultValue(null);

        $method
            ->addParameter('where')
            ->setTypeHint('array')
            ->setDefaultValue(null);

        $method
            ->addParameter('sorters')
            ->setTypeHint('array')
            ->setDefaultValue(null);

        $method->setBody(
            '$qb = $this->connection->createQueryBuilder()
    ->select(\'*\')
    ->from(self::TABLE);

if ($offset) {
    $qb->setFirstResult($offset);
}

if ($limit) {
    $qb->setMaxResults($limit);
}

if ($where) {
    foreach ($where as $key => $value) {
        $qb->andWhere(sprintf(\'%s = %s\', $key, $qb->createNamedParameter($value)));
    }
}

if ($sorters) {
    foreach ($sorters as $field => $sort) {
        $qb->addOrderBy($field, $sort);
    }
}

$result = $qb->execute()->fetchAll();

$records = [];

foreach ($result as $item) {
    $records[] = $this->getEntityFromDatabaseArray($item);
}

return $records;'
        );
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function findOneBy(Table $table, ClassType $class)
    {
        $method = $class->addMethod('findOneBy')
            ->addComment('@param array $where')
            ->addComment('@return ' . $table->camelCaseName);

        if ($this->request->phpVersion !== Generator::PHP70) {
            $method
                ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName)
                ->setReturnNullable(true);
        }

        $method->addParameter('where')
            ->setTypeHint('array');

        $method->setBody(
            '$qb = $this->connection->createQueryBuilder();

$qb->select(\'*\')
   ->from(self::TABLE);

foreach ($where as $key => $value) {
   $qb->andWhere(sprintf(\'%s = %s\', $key, $qb->createNamedParameter($value)));
}

$result = $qb->execute()->fetch();

if (empty($result)) {
   return null;
}

return $this->getEntityFromDatabaseArray($result);'
        );
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function find(Table $table, ClassType $class)
    {
        $method = $class->addMethod('find')
            ->addComment('@param ' . $table->primaryColumn->normalizedType . ' $' . $table->primaryColumn->name)
            ->addComment('@return ' . $table->camelCaseName);

        if ($this->request->phpVersion !== Generator::PHP70) {
            $method
                ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName)
                ->setReturnNullable(true);
        }

        $method->addParameter($table->primaryColumn->name)
            ->setTypeHint($table->primaryColumn->normalizedType);

        $method->setBody(
            'return $this->findOneBy([\'' . $table->primaryColumn->name . '\' => $' . $table->primaryColumn->name . ']);'
        );
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function create(Table $table, ClassType $class)
    {
        $method = $class->addMethod('create')
            ->addComment('Creates a record in the database.')
            ->addComment('@param ' . $table->camelCaseName . ' $entity')
            ->addComment('@return ' . $table->camelCaseName)
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->addParameter('entity')
            ->setTypeHint($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->setBody(
            '$databaseArray = $this->getDatabaseArrayFromEntity($entity);

$this->connection->insert(
    self::TABLE,
    $databaseArray
);
'
        );
        $method->addBody('');

        if ($table->primaryColumn->auto_increment) {
            $method->addBody(
                '$entity->set' . $table->primaryColumn->camelCaseName . '($this->connection->lastInsertId());'
            );
            $method->addBody('');
        }

        $method->addBody('return $entity;');
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function update(Table $table, ClassType $class)
    {
        $method = $class->addMethod('update')
            ->addComment('Update a record in the database.')
            ->addComment('@param ' . $table->camelCaseName . ' $entity')
            ->addComment('@return ' . $table->camelCaseName)
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->addParameter('entity')
            ->setTypeHint($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->setBody(
            '$databaseArray = $this->getDatabaseArrayFromEntity($entity);

$this->connection->update(
    self::TABLE,
    $databaseArray,
    [\'' . $table->primaryColumn->name . '\' => $entity->get' . $table->primaryColumn->camelCaseName . '()]
);

return $entity;'
        );
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function remove(Table $table, ClassType $class)
    {
        $method = $class->addMethod('remove')
            ->addComment('Remove a record in the database.')
            ->addComment('@param ' . $table->camelCaseName . ' $entity')
            ->addComment('@return ' . $table->camelCaseName)
            ->addComment('@throws \Doctrine\DBAL\Exception\InvalidArgumentException')
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->addParameter('entity')
            ->setTypeHint($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->setBody(
            '$this->connection->delete(
    self::TABLE,
    [\'' . $table->primaryColumn->name . '\' => $entity->get' . $table->primaryColumn->camelCaseName . '()]
);

return $entity;'
        );
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function getDatabaseArrayFromEntity(Table $table, ClassType $class)
    {
        $method = $class->addMethod('getDatabaseArrayFromEntity')
            ->addComment('Maps the given entity to the database array.')
            ->addComment('@param ' . $table->camelCaseName . ' $entity')
            ->addComment('@return array')
            ->setReturnType('array');

        $method->addParameter('entity')
            ->setTypeHint($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->setBody(
            '$array = $entity->toArray();

foreach($array as &$item) {
    if ($item instanceof \DateTime) {
        $item = $item->format(\'Y-m-d H:i:s\');
    } elseif (is_array($item)) {
        $item = json_encode($item);
    }
}

return $array;'
        );
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function getEntityFromDatabaseArray(Table $table, ClassType $class)
    {
        $method = $class->addMethod('getEntityFromDatabaseArray')
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName)
            ->addComment('Prepares database array from properties.')
            ->addComment('@param array $data')
            ->addComment('@return ' . $table->camelCaseName);

        $method->addParameter('data')
            ->setTypeHint('array');

        $method->addBody('$entity = new ' . $table->camelCaseName . '();');

        foreach ($table->columns as $column) {
            if ($column->normalizedType === '\DateTime') {
                $method
                    ->addBody(
                        str_replace(
                            ['$camelCase$', '$name$', '$normType'],
                            [$column->camelCaseName, $column->name, $column->normalizedType],
                            '$entity->set$camelCase$(new \DateTime($data[\'$name$\']));'
                        )
                    );
            } elseif ($column->type === 'json') {
                $method
                    ->addBody(
                        str_replace(
                            ['$camelCase$', '$name$', '$normType'],
                            [$column->camelCaseName, $column->name, $column->normalizedType],
                            '$entity->set$camelCase$(empty($data[\'$name$\']) ? [] : json_decode($data[\'$name$\'], true));'
                        )
                    );
            } else {
                $method
                    ->addBody(
                        str_replace(
                            ['$camelCase$', '$name$', '$normType'],
                            [$column->camelCaseName, $column->name, $column->normalizedType],
                            '$entity->set$camelCase$(($normType) $data[\'$name$\']);'
                        )
                    );
            }
        }

        $method
            ->addBody('')
            ->addBody('return $entity;');
    }
}
