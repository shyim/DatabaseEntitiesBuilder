<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder\Services;

use Shyim\DatabaseEntitiesBuilder\Generator;
use Shyim\DatabaseEntitiesBuilder\Structs\Table;
use Nette\PhpGenerator\PhpNamespace;

/**
 * Class ModelGenerator
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class ModelGenerator
{
    /**
     * @param string $namespace
     * @param Table $table
     * @param string $phpVersion
     * @return PhpNamespace
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function generate(string $namespace, Table $table, $phpVersion = 'php71'): PhpNamespace
    {
        $phpNamespace = new PhpNamespace($namespace . '\\' . $table->camelCaseName);
        $phpNamespace->addUse($namespace . '\AbstractEntity');

        $class = $phpNamespace->addClass($table->camelCaseName);
        $class->setExtends($namespace . '\AbstractEntity');
        $class->addComment(sprintf('Entity for table %s', $table->name));

        foreach ($table->columns as $column) {
            $property = $class->addProperty($column->name);
            $property->addComment(sprintf('@var %s $%s', $column->normalizedType, $column->name));
            $property->setValue($column->default);
            $property->setVisibility('protected');

            // Getter
            $method = $class->addMethod('get' . ucfirst($column->camelCaseName));
            $method
                ->addComment('@return ' . $column->normalizedType)
                ->setBody('return $this->' . $column->name . ';');

            $nullable = $table->primaryColumn->name === $column->name ? true : ($column->nullable ?? false);

            if ($phpVersion === Generator::PHP71) {
                $method
                    ->setReturnType($column->normalizedType)
                    ->setReturnNullable($nullable);
            } elseif(!$nullable) {
                $method
                    ->setReturnType($column->normalizedType);
            }

            // Setter
            $method = $class->addMethod('set' . ucfirst($column->camelCaseName));
            $method
                ->addComment('@param ' . $column->normalizedType . ' $' . $column->name)
                ->addComment('@return self')
                ->setReturnType('self')
                ->addBody(sprintf('$this->%s = $%s;', $column->name, $column->name))
                ->addBody('return $this;');

            $parameter = $method->addParameter($column->name);

            if ($phpVersion === Generator::PHP71) {
                $parameter
                    ->setTypeHint($column->normalizedType)
                    ->setNullable($nullable);
            } elseif(!$nullable) {
                $parameter
                    ->setTypeHint($column->normalizedType);
            }
        }

        return $phpNamespace;
    }
}
