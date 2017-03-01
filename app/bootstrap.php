<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');
$configurator::detectDebugMode();

$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
		->addDirectory('../vendor')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');



$container = $configurator->createContainer();
/*$em = $container->getByType('Doctrine\ORM\EntityManager');
$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
$classes = array(
	$em->getClassMetadata('App\Entity\Article'),
	$em->getClassMetadata('App\Entity\Chat'),
	$em->getClassMetadata('App\Entity\Contact'),
	$em->getClassMetadata('App\Entity\Counter'),
	$em->getClassMetadata('App\Entity\Cup'),
	$em->getClassMetadata('App\Entity\Document'),
	$em->getClassMetadata('App\Entity\Event'),
	$em->getClassMetadata('App\Entity\Fire'),
	$em->getClassMetadata('App\Entity\News'),
	$em->getClassMetadata('App\Entity\Position'),
	$em->getClassMetadata('App\Entity\Reference'),
	$em->getClassMetadata('App\Entity\Role'),
	$em->getClassMetadata('App\Entity\Sponsor'),
	$em->getClassMetadata('App\Entity\Subject'),
	$em->getClassMetadata('App\Entity\Technology'),
	$em->getClassMetadata('App\Entity\Unit'),
	$em->getClassMetadata('App\Entity\User'),
);
$updated = $tool->updateSchema($classes,true);
*/
return $container;
