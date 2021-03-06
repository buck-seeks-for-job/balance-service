<?php
declare(strict_types=1);

namespace Iqoption\Test\TestUtility;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use JMS\Serializer\Annotation\Type;
use PDO;
use PHPUnit\Framework\TestCase;

abstract class DoctrineSqliteTestCase extends TestCase
{
    const PDO_CONNECTION_STRING = 'sqlite::memory:';

    /**
     * @var EntityManager
     */
    protected static $entityManager;

    private static $pdo = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$entityManager = self::createEntityManager();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$entityManager = null;
    }

    protected function setUp()
    {
        parent::setUp();
        if (!self::$entityManager->isOpen()) {
            self::$entityManager = self::createEntityManager();
        }
        $schema = new SchemaTool(self::$entityManager);
        $schema->createSchema(
            self::$entityManager->getMetadataFactory()->getAllMetadata()
        );
    }

    protected function tearDown()
    {
        self::$entityManager->clear();
        $schema = new SchemaTool(self::$entityManager);
        $schema->dropSchema(
            self::$entityManager->getMetadataFactory()->getAllMetadata()
        );
    }

    protected static function getClassDirectory($className): string
    {
        $fileName = (new \ReflectionClass($className))->getFileName();

        return dirname($fileName);
    }

    protected function resetEntityManager()
    {
        self::$entityManager = self::createEntityManager();
    }

    abstract protected static function getAnnotationMetadataConfigurationPaths(): array;

    private static function createEntityManager(): EntityManager
    {
        $paths = static::getAnnotationMetadataConfigurationPaths();

        $config = Setup::createAnnotationMetadataConfiguration($paths, true);
        $annotationReader = new AnnotationReader();
        $annotationDriver = new AnnotationDriver(new CachedReader($annotationReader, new ArrayCache()), $paths);
        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            __DIR__ . '/../../vendor/jms/serializer/src'
        );
        $config->setMetadataDriverImpl($annotationDriver);

        if (!self::$pdo) {
            self::$pdo = new PDO(self::PDO_CONNECTION_STRING);
            self::$pdo->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        }

        return EntityManager::create(['pdo' => self::$pdo], $config);
    }
}
