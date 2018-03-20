# Database Entities Generator

This package generates simple entities, repository and service from a mysql database schema.

## Pro

* Lower overhead, its using dbal in background
* No magic, just the generated classes

## Contra

* Its too simple, so it can't resolve associations or so


## Example Usage

Retrive a entity
```
$cmsEntity = $cmsRepositoy->find($cmsId);
$cmsEntity = $cmsRepositoy->findOneBy(['name' => 'imprint']);
$cmsEntity = $cmsRepositoy->findBy(['status' => 1]);
```

Create a new entity
```
$cmsEntity = new Cms();
$cmsEntity->setName("lol");
$cmsEntity->setStatus(1);

$cmsEntity = $cmsRepository->create($cmsEntity);

var_dump($cmsEntity->getId());
```

Update a entity
```
$cmsEntity->setName("Lol");
$cmsEntity = $cmsRepository->update($cmsEntity);
```

Delete a entity
```
$cmsRepository->remove($cmsEntity);
```
