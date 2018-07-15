<?php
declare(strict_types=1);

namespace Iqoption\Test\TestUtility;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SymfonyDbTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        parent::setUp();

        $clientOptions['environment'] = getenv('CLIENT_ENV');
        $this->client = static::createClient($clientOptions);
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $schema = new SchemaTool($entityManager);
        $schema->createSchema(
            $entityManager->getMetadataFactory()->getAllMetadata()
        );
    }

    protected function tearDown()
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $schema = new SchemaTool($entityManager);
        $schema->dropSchema(
            $entityManager->getMetadataFactory()->getAllMetadata()
        );

        parent::tearDown();
    }
}
