<?php
// bootstrap.php
// Include Composer Autoload (relative to project root).
$dbParams = [];
/**
 * The dev home dir name
 *
 * @var string
 */
$devdir = 'zf2';
$cwd = getcwd();
$isDevMode = strstr($cwd, "/{$devdir}/");

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/" . ($isDevMode ? "development" : "local") . ".db.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__ . '/../../vendor/autoload.php';
Zend\Loader\AutoloaderFactory::factory(
		array(
				'Zend\Loader\StandardAutoloader' => array(
						Zend\Loader\StandardAutoloader::LOAD_NS => array(
								"Application" => __DIR__ .
										 "/../../module/Application/src/Application"
						)
				)
		));

AnnotationRegistry::registerLoader(array(
		$loader,
		'loadClass'
));

$paths = [
		realpath('module/Lead/src/Lead/Entity'),
		realpath('module/Account/src/Account/Entity'),
		realpath('module/User/src/User/Entity'),
		realpath('module/Event/src/Event/Entity'),
		realpath('module/Api/src/Api/Entity')
];

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, 
		null, false);
$entityManager = EntityManager::create($dbParams, $config);