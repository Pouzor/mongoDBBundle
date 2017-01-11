<?php

namespace Pouzor\MongoDBBundle\DocumentManager;

use MongoDB\Client;
use MongoDB\Database;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Pouzor\MongoDBBundle\Constants\Query;
use Pouzor\MongoDBBundle\Repository\Repository;
use Pouzor\MongoDBBundle\Types\MongoTransformerInterface;

/**
 * Class DocumentManager
 * @package Pouzor\MongoDBBundle\DocumentManager
 */
class DocumentManager
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var Repository[]
     */
    private $repositories = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $transformers = [];

    /**
     * @var array
     */
    private $configuration = [];

    /**
     * @param array $configuration
     * @throws \Exception
     */
    public function __construct(array $configuration = array(), LoggerInterface $logger = null)
    {
        /**
         * host:          %mongo_host%
         * port:          %mongo_port%
         * db:            %mongo_database%
         * password:      %mongo_password%
         * username:      %mongo_user%
         * schema:        "%kernel.root_dir%/config/mongo/default.yml"
         * options:       ~
         */

        $this->validateConfiguration($configuration);

        $this->configuration = $configuration;

        $dsn = null;

        if ($configuration['username']) {
            $dsn = sprintf(
                'mongodb://%s:%s@%s:/%s',
                $configuration['username'],
                $configuration['password'],
                $configuration['host'],
                $configuration['db']
            );
        } else {
            $dsn = sprintf(
                'mongodb://%s/%s',
                $configuration['host'],
                $configuration['db']
            );
        }

        if (isset($configuration['options']['replicaSet']) && $configuration['options']['replicaSet']) {
            $dsn .= "?replicaSet=".$configuration['options']['replicaSet'];
        }

        $this->client = new Client(
            $dsn, $configuration['options'], [
            'typeMap' => [
                'root' => 'array',
                'document' => 'array'
            ],
        ]
        );

        $this->database = $this->client->selectDatabase($configuration['db']);

        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param MongoTransformerInterface $transformer
     */
    public function addTransformer(MongoTransformerInterface $transformer)
    {
        $this->transformers[] = $transformer;
    }

    /**
     * @param $name
     * @return Repository
     */
    public function getRepository($name)
    {
        if (!array_key_exists($name, $this->repositories)) {
            /**
             * lazy creation
             */
            $repo = new Repository(
                $name,
                $this
            );

            if (isset($this->configuration['schema'][$name]['indexes'])) {
                $repo->setIndexes($this->configuration['schema'][$name]['indexes']);
            }

            $this->repositories[$name] = $repo;
        }

        return $this->repositories[$name];
    }

    /**
     * @param $collection
     * @param $id
     * @param array $options
     * @return null|object
     */
    public function find($collection, $id, array $options = [])
    {
        return $this->getRepository($collection)->find($id);
    }

    /**
     * @param $collectionName
     * @param array $filter
     * @return \MongoDB\DeleteResult
     */
    public function removeAll($collectionName, array $filter = [])
    {
        return $this->getRepository($collectionName)->deleteMany(
            $filter,
            [
                Query::NO_CURSOR_TIMEOUT
            ]
        );
    }

    /**
     *
     */
    public function createIndexes()
    {
        // TODO
        throw new \Exception('Not implemented yet');
    }

    /**
     * @param $configuration
     * @throws \Exception
     */
    private function validateConfiguration($configuration)
    {
        /**
         * validating keys
         */
        foreach (['host', 'db', 'password', 'username', 'schema', 'options'] as $key) {
            if (!array_key_exists($key, $configuration)) {
                throw new \Exception(sprintf('%s must be present in configuration', $key));
            }
        }

        foreach (['host', 'db'] as $key) {
            if (!is_string($configuration[$key]) || empty($configuration[$key])) {
                throw new \Exception(sprintf('%s must be a not empty string', $key));
            }
        }

    }

    /**
     * Build all indexes
     *
     * @param null $callback
     */
    public function buildIndexes($rebuild = false, $callback = null)
    {
        foreach ($this->configuration['schema'] as $col => $conf) {
            $this->getRepository($col)->buildIndexes($rebuild, $callback);
        }
    }

    /**
     * @return Database
     */
    public function getDatabase() {

        return $this->database;
    }

    /**
     * @return LoggerInterface|NullLogger
     */
    public function getLogger() {

        return $this->logger;
    }

    /**
     * @return array
     */
    public function getTransformers() {

        return $this->transformers;
    }
}
