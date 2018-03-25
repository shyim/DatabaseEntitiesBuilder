<?php

use Shyim\DatabaseEntitiesBuilder\Generator;
use Shyim\DatabaseEntitiesBuilder\Services\BaseClassGenerator;
use Shyim\DatabaseEntitiesBuilder\Services\DatabaseReader;
use Shyim\DatabaseEntitiesBuilder\Services\ModelGenerator;
use Shyim\DatabaseEntitiesBuilder\Services\RepositoryGenerator;
use Shyim\DatabaseEntitiesBuilder\Services\ServiceGenerator;
use Shyim\DatabaseEntitiesBuilder\Structs\Request;
use Symfony\Component\DependencyInjection\Container;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/container.php';

$connection = \Doctrine\DBAL\DriverManager::getConnection([
    'dbname' => getenv('DB_NAME'),
    'user' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'host' => getenv('DB_HOST'),
    'driver' => 'pdo_mysql'
]);

$reader = new DatabaseReader($connection, new Container());
$generator = new Generator($reader, new ModelGenerator(), new BaseClassGenerator(), new RepositoryGenerator(), new ServiceGenerator());

$request = new Request();
$request->folder = __DIR__ . '/generated';
$request->namespace = 'DatabaseEntitiesBuilder\\Models';
echo 'Generating test models' . PHP_EOL;
$generator->generateModels($request);

