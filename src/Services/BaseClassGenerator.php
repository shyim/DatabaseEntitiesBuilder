<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder\Services;

use JsonSerializable;
use Nette\PhpGenerator\PhpNamespace;

/**
 * Class BaseClassGenerator
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class BaseClassGenerator
{
    /**
     * @param string $namespace
     * @param string $targetDir
     * @author Soner Sayakci <shyim@posteo.de>
     * @throws \Nette\InvalidArgumentException
     */
    public function generate(string $namespace, string $targetDir): void
    {
        file_put_contents(
            $targetDir . '/AbstractEntity.php',
            '<?php' . PHP_EOL . PHP_EOL . $this->generateAbstractEntity($namespace),
            LOCK_EX
        );
        file_put_contents(
            $targetDir . '/AbstractRepository.php',
            '<?php' . PHP_EOL . PHP_EOL . $this->generateAbstractRepository($namespace),
            LOCK_EX
        );
        file_put_contents(
            $targetDir . '/AbstractService.php',
            '<?php' . PHP_EOL . PHP_EOL . $this->generateAbstractService($namespace),
            LOCK_EX
        );
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     * @param string $namespace
     * @return PhpNamespace
     * @throws \Nette\InvalidArgumentException
     */
    private function generateAbstractEntity(string $namespace): PhpNamespace
    {
        $phpNamespace = new PhpNamespace($namespace);

        $class = $phpNamespace->addClass('AbstractEntity')
            ->addComment('Abstract entity')
            ->addImplement(JsonSerializable::class)
            ->setAbstract(true);

        $fromArray = $class->addMethod('fromArray')
            ->addComment('Sets all properties from array.')
            ->addComment('@param array $data')
            ->setReturnType('void');

        $fromArray->addParameter('data')
            ->setTypeHint('array');


        $fromArray
            ->setBody(
                'foreach ($data as $key => $value) {
    if (!property_exists($this, $key)) {
        continue;
    }
    $this->{$key} = $value;
}'
            );

        $class->addMethod('toArray')
            ->setReturnType('array')
            ->addComment('Returns all properties as array.')
            ->setBody('return get_object_vars($this);');

        $class
            ->addMethod('jsonSerialize')
            ->setReturnType('array')
            ->addComment('Returns all properties as array.')
            ->setBody('return $this->toArray();');

        return $phpNamespace;
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     * @param string $namespace
     * @return PhpNamespace
     * @throws \Nette\InvalidArgumentException
     */
    private function generateAbstractService(string $namespace): PhpNamespace
    {
        $phpNamespace = new PhpNamespace($namespace);

        $phpNamespace->addClass('AbstractService')
            ->addComment('Abstract service')
            ->setAbstract(true);

        return $phpNamespace;
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     * @param string $namespace
     * @return PhpNamespace
     * @throws \Nette\InvalidArgumentException
     */
    private function generateAbstractRepository(string $namespace): PhpNamespace
    {
        $phpNamespace = new PhpNamespace($namespace);

        $phpNamespace->addClass('AbstractRepository')
            ->addComment('Abstract repository')
            ->setAbstract(true);

        return $phpNamespace;
    }
}
