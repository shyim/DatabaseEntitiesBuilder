<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder\Services;

use Shyim\DatabaseEntitiesBuilder\Structs\Table;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

/**
 * Class ServiceGenerator
 * @author Soner Sayakci <shyim@posteo.de>
 */
class ServiceGenerator
{
    /**
     * @param string $namespace
     * @param Table $table
     * @return PhpNamespace
     * @author Soner Sayakci <shyim@posteo.de>
     * @throws \Nette\InvalidArgumentException
     */
    public function generate(string $namespace, Table $table) : PhpNamespace
    {
        $phpNamespace = new PhpNamespace($namespace . '\\' . $table->camelCaseName);
        $phpNamespace->addUse($namespace . '\AbstractService');

        $class = $phpNamespace->addClass($table->camelCaseName . 'Service');
        $class->setExtends($namespace . '\AbstractService');
        $class->addComment(sprintf('Service for table %s', $table->name));

        $class->addProperty('repository')
            ->addComment('@var ' . $table->camelCaseName . 'Repository')
            ->setVisibility('private');

        $constructor = $class->addMethod('__construct')
            ->setBody('$this->repository = $repository;')
            ->addComment($class->getName() . ' constructor.')
            ->addComment('@param ' . $table->camelCaseName . 'Repository $repository');

        $con = $constructor->addParameter('repository');
        $con->setTypeHint($namespace . '\\' . $table->camelCaseName . '\\' . $table->camelCaseName . 'Repository');

        $this->findBy($table, $class);
        $this->findOneBy($table, $class);
        $this->find($table, $class);
        $this->create($table, $class);
        $this->update($table, $class);
        $this->remove($table, $class);

        return $phpNamespace;
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function findBy(Table $table, ClassType $class): void
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

        $method->setBody('return $this->repository->findBy($offset, $limit, $where, $sorters);');
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function findOneBy(Table $table, ClassType $class): void
    {
        $method = $class->addMethod('findOneBy')
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName)
            ->addComment('@param array $where')
            ->addComment('@return ' . $table->camelCaseName);

        $method->addParameter('where')
            ->setTypeHint('array');

        $method->setBody('return $this->repository->findOneBy($where);');
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function find(Table $table, ClassType $class): void
    {
        $method = $class->addMethod('find')
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName)
            ->addComment('@param ' . $table->primaryColumn->normalizedType . ' $' . $table->primaryColumn->name)
            ->addComment('@return ' . $table->camelCaseName);

        $method->addParameter($table->primaryColumn->name)
            ->setTypeHint($table->primaryColumn->normalizedType);


        $method->setBody('return $this->repository->find($' . $table->primaryColumn->name . ');');
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function create(Table $table, ClassType $class): void
    {
        $method = $class->addMethod('create')
            ->addComment('Creates a record in the database.')
            ->addComment('@param ' . $table->camelCaseName . ' $entity')
            ->addComment('@return ' . $table->camelCaseName)
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->addParameter('entity')
            ->setTypeHint($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->setBody('return $this->repository->create($entity);');
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function update(Table $table, ClassType $class): void
    {
        $method = $class->addMethod('update')
            ->addComment('Update a record in the database.')
            ->addComment('@param ' . $table->camelCaseName . ' $entity')
            ->addComment('@return ' . $table->camelCaseName)
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->addParameter('entity')
            ->setTypeHint($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->setBody('return $this->repository->update($entity);');
    }

    /**
     * @param Table $table
     * @param ClassType $class
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function remove(Table $table, ClassType $class): void
    {
        $method = $class->addMethod('remove')
            ->addComment('Remove a record in the database.')
            ->addComment('@param ' . $table->camelCaseName . ' $entity')
            ->addComment('@return ' . $table->camelCaseName)
            ->addComment('@throws \Doctrine\DBAL\Exception\InvalidArgumentException')
            ->setReturnType($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->addParameter('entity')
            ->setTypeHint($class->getNamespace()->getName() . '\\' . $table->camelCaseName);

        $method->setBody('return $this->repository->remove($entity);');
    }
}
