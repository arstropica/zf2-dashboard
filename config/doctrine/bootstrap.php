<?php
// bootstrap.php
// Include Composer Autoload (relative to project root).
$dbParams = [];
require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/development.db.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__ . '/../../vendor/autoload.php';

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

/**
 * The dev home dir name
 * 
 * @var string
 */
$devdir = 'zf2';
$cwd = getcwd();
$isDevMode = strstr($cwd, "/{$devdir}/");

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, 
		null, false);
$entityManager = EntityManager::create($dbParams, $config);