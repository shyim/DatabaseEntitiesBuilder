<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder;

use Shyim\DatabaseEntitiesBuilder\Services\BaseClassGenerator;
use Shyim\DatabaseEntitiesBuilder\Services\DatabaseReader;
use Shyim\DatabaseEntitiesBuilder\Services\ModelGenerator;
use Shyim\DatabaseEntitiesBuilder\Services\RepositoryGenerator;
use Shyim\DatabaseEntitiesBuilder\Services\ServiceGenerator;
use Shyim\DatabaseEntitiesBuilder\Structs\Request;
use Shyim\DatabaseEntitiesBuilder\Structs\Table;
use RuntimeException;

/**
 * Class Generator
 * @author Soner Sayakci <shyim@posteo.de>
 */
class Generator
{
    public const PHP70 = 'php70';
    public const PHP71 = 'php71';

    /**
     * @var DatabaseReader
     */
    private $reader;

    /**
     * @var ModelGenerator
     */
    private $modelGenerator;

    /**
     * @var BaseClassGenerator
     */
    private $baseClassGenerator;

    /**
     * @var RepositoryGenerator
     */
    private $repositoryGenerator;

    /**
     * @var ServiceGenerator
     */
    private $serviceGenerator;

    /**
     * Generator constructor.
     * @author Soner Sayakci <shyim@posteo.de>
     * @param DatabaseReader $reader
     * @param ModelGenerator $modelGenerator
     * @param BaseClassGenerator $baseClassGenerator
     * @param RepositoryGenerator $repositoryGenerator
     * @param ServiceGenerator $serviceGenerator
     */
    public function __construct(
        DatabaseReader $reader,
        ModelGenerator $modelGenerator,
        BaseClassGenerator $baseClassGenerator,
        RepositoryGenerator $repositoryGenerator,
        ServiceGenerator $serviceGenerator
    ){
        $this->reader = $reader;
        $this->modelGenerator = $modelGenerator;
        $this->baseClassGenerator = $baseClassGenerator;
        $this->repositoryGenerator = $repositoryGenerator;
        $this->serviceGenerator = $serviceGenerator;
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     * @param Request $request
     */
    public function generateModels(Request $request): void
    {
        $tables = $this->reader->buildSchema($request);
        $folder = realpath($request->folder);

        if ($folder === false) {
            throw new RuntimeException(sprintf('Directory at path "%s" does not exist', $request->folder));
        }

        $this->baseClassGenerator->generate($request->namespace, $folder);

        /** @var Table $table */
        foreach ($tables as $table) {
            $modelDir = $folder . '/' . $table->camelCaseName;
            if (!file_exists($modelDir)) {
                if (!mkdir($modelDir) && !is_dir($modelDir)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $modelDir));
                }
            }

            file_put_contents($modelDir . '/' . $table->camelCaseName . '.php', '<?php' . PHP_EOL . PHP_EOL . $this->modelGenerator->generate($request->namespace, $table, $request->phpVersion), LOCK_EX);
            file_put_contents($modelDir . '/' . $table->camelCaseName . 'Repository.php', '<?php' . PHP_EOL . PHP_EOL . $this->repositoryGenerator->generate($request, $table), LOCK_EX);
            file_put_contents($modelDir . '/' . $table->camelCaseName . 'Service.php', '<?php' . PHP_EOL . PHP_EOL . $this->serviceGenerator->generate($request, $table), LOCK_EX);
        }
    }
}
