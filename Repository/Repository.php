<?php

namespace Pouzor\MongoDBBundle\Repository;


use MongoDB\BulkWriteResult;
use MongoDB\Collection;
use MongoDB\Operation\InsertOne;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Pouzor\MongoDBBundle\Constants\DriverClasses;
use Pouzor\MongoDBBundle\Constants\Query;
use Pouzor\MongoDBBundle\Exception\MalformedOperationException;
use Pouzor\MongoDBBundle\Services\ArrayAccessor;


class Repository
{
    /**
     * @var array
     */
    protected $indexes = [];

    /**
     * @var string
     */
    private $id_field = '_id';

    /**
     * @var string
     */
    private $name;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var NullLogger
     */
    private $logger;


    private $persistence = [];

    /**
     * @var
     */
    private $transformers = [];

    /**
     * Repository constructor.
     * @param $name
     * @param $manager
     */
    public function __construct($name, $manager)
    {

        $this->collection = $manager->getDatabase()->selectCollection($name);

        $this->name = $name;

        $this->logger = $manager->getLogger() ?: new NullLogger();

        $this->transformers = $manager->getTransformers();

        $this->persistence = [];

    }

    /**
     * @param $fields
     * @param array $options
     * @param null $callback
     */
    public function ensureIndex($fields, array $options = ['maxTimeMS' => 0], $rebuild = false, $callback = null)
    {
        $this->logger->info('Creating index with fields ', $fields);

        try {
            $name = $this->collection->createIndex($fields, $options);

            if (is_callable($callback)) {
                call_user_func($callback, $name);
            }
        } catch (\Exception $re) {
            $this->logger->warning($re->getMessage());

            $regex = '/Index with name: (?<name>\w+) already exists with different options/';

            if (preg_match($regex, $re->getMessage(), $output)) {
                $name = $output['name'];

                $this->logger->info('Dropping index ' . $name);
                $result = $this->collection->dropIndex($name);

                $this->logger->info('Result from dropping index ' . $name, $result);

                $this->ensureIndex($fields, $options, $callback);
            }

        }

    }

    /**
     * @param null $callback
     */
    public function buildIndexes($rebuild = false, $callback = null)
    {
        echo $this->name;

        if ($rebuild) {
            $this->collection->dropIndexes();
        }

        foreach ($this->indexes as $name => $conf) {
            if (is_array($conf)) {
                $this->ensureIndex(
                    $conf['fields'],
                    isset($conf['options']) ? $conf['options'] : [],
                    $rebuild,
                    $callback
                );
            } else {
                $this->ensureIndex([$name => $conf], [], $rebuild, $callback);
            }
        }
    }


    /**
     * @return int|void
     */
    public function countPendingOperations()
    {
        return count($this->persistence);
    }


    /**
     * Persist a document
     *
     * @param $document
     * @return null | BulkWriteResult
     */
    public function persist($document, $andFlush = false)
    {

        $this->persistence += [$document];

        if ($andFlush) {
            return $this->flush();
        }
    }

    /**
     * @return BulkWriteResult
     */
    public function flush()
    {
        $result = $this->bulk($this->persistence);

        $this->persistence = [];

        return $result;
    }


    /**
     * Write many operations in bulk
     * Ex:
     * $result = $repository->bulk([
     *            [
     *                "insertOne" => [
     *                    [
     *                        "name" => "Hannes Magnusson",
     *                        "company" => "10gen",
     *                    ]
     *                ],
     *            ],
     *            [
     *                "insertOne" => [
     *                    [
     *                        "name" => "Jeremy Mikola",
     *                        "company" => "10gen",
     *                    ]
     *                ],
     *            ],
     *            [
     *                "updateMany" => [
     *                    ["company" => "10gen"],
     *                    ['$set' => ["company" => "MongoDB"]],
     *                ],
     *            ],
     *            [
     *                "updateOne" => [
     *                    ["name" => "Hannes Magnusson"],
     *                    ['$set' => ["viking" => true]],
     *                ],
     *            ],
     * ]);
     * @param $operations
     * @return \MongoDB\BulkWriteResult
     * @throws MalformedOperationException
     */
    public function bulk($operations)
    {

        $this->validateBulkOperations($operations);

        $this->logger->info(
            'Bulk operations ',
            [
                'operations' => $operations,
                'col' => $this->name
            ]
        );

        $result = $this->collection->bulkWrite($operations);

        $this->logger->info(
            'Bulk operations result',
            [
                'inserted' => $result->getInsertedCount(),
                'upserted' => $result->getUpsertedCount(),
                'updated' => $result->getModifiedCount(),
                'deleted' => $result->getDeletedCount(),
                'col' => $this->name
            ]
        );

        return $result;
    }

    /**
     * @param array $query
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function findBy(array $query = [], array $options = ['maxTimeMS' => 0])
    {
        $this->validateQueryOptions($options);

        $this->logger->info(
            'Find many by',
            [
                'query' => $query,
                'col' => $this->name,
            ]
        );

        return $this->collection->find($query, $options);
    }


    /**
     * Find one document
     *
     * @param array $filter
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function findOneBy(array $filter = [], array $options = ['maxTimeMS' => 0])
    {
        $this->validateQueryOptions($options);

        $this->logger->info(
            'Find one ',
            [
                'query' => $filter,
                'col' => $this->name,
            ]
        );

        return $this->collection->findOne($filter, $options);
    }


    /**
     * @param $id
     * @param array $options
     * @return null|object
     */
    public function find($id, array $options = ['maxTimeMS' => 0])
    {
        $this->validateQueryOptions($options);

        $this->logger->info(
            'Mongo find by id',
            [
                'id' => $id,
                'col' => $this->name
            ]
        );

        return $this->collection->findOne([$this->getIdField() => self::createObjectId($id)], $options);
    }

    /**
     * @param array $documents
     * @return \MongoDB\InsertManyResult
     */
    public function insertMany(array $documents = [])
    {
        $result = $this->collection->insertMany($documents);

        $this->logger->info(
            'Insert many result',
            [
                'result' => $result,
                'col' => $this->name,
            ]
        );

        return $result;
    }

    /**
     * @param $document
     * @return \MongoDB\InsertOneResult
     */
    public function insertOne($document)
    {
        $this->logger->info(
            'Insert one',
            [
                'document' => $document,
                'col' => $this->name,
            ]
        );

        return $this->collection->insertOne($document);
    }

    public function update($id, array $modifications = [], array $options = [])
    {

        $this->updateOne(
            [$this->getIdField() => self::createObjectId($id)],
            $modifications,
            $options
        );
    }


    /**
     * Update one document
     *
     * @param array $where
     * @param array $modifications
     * @param array $options
     * @return \MongoDB\UpdateResult
     */
    public function updateOne(array $where = [], array $modifications = [], array $options = [])
    {

        $this->logger->info(
            'Update one ',
            [
                'where' => $where,
                'update' => $modifications,
                'col' => $this->name,
            ]
        );

        return $this->collection->updateOne($where, $modifications, $options);
    }


    /**
     * Update many documents at the same time
     *
     * @param array $where
     * @param array $modifications
     * @param array $options
     * @return \MongoDB\UpdateResult
     */
    public function updateMany(array $where = [], array $modifications = [], array $options = [])
    {

        $this->logger->info(
            'Update many ',
            [
                'where' => $where,
                'update' => $modifications,
                'col' => $this->name,
            ]
        );

        return $this->collection->updateMany($where, $modifications, $options);
    }

    /**
     * Update many documents at the same time
     *
     * @param array $where
     * @param array $modifications
     * @param array $options
     * @return \MongoDB\UpdateResult
     */
    public function replaceOne(array $where = [], array $modifications = [], array $options = [])
    {

        $this->logger->info(
            'Replace one ',
            [
                'where' => $where,
                'update' => $modifications,
                'col' => $this->name,
            ]
        );

        return $this->collection->replaceOne($where, $modifications, $options);
    }


    /**
     * @param $id
     * @param array $options
     * @return \MongoDB\DeleteResult
     */
    public function delete($id, array $options = [])
    {
        return $this->deleteOne(
            [$this->getIdField() => self::createObjectId($id)],
            $options
        );
    }

    /**
     * Delete one document
     *
     * @param array $where
     * @param array $options
     * @return \MongoDB\DeleteResult
     */
    public function deleteOne(array $where = [], array $options = [])
    {

        $this->logger->info(
            'Delete one ',
            [
                'where' => $where,
                'col' => $this->name,
            ]
        );

        return $this->collection->deleteOne($where, $options);
    }

    /**
     * @param array $where
     * @param array $options
     * @return \MongoDB\DeleteResult
     */
    public function deleteMany(array $where = [], array $options = [])
    {

        $this->logger->info(
            'Delete many ',
            [
                'where' => $where,
                'col' => $this->name,
            ]
        );

        return $this->collection->deleteMany($where, $options);
    }

    /**
     * @param array $pipelines
     * @param array $options
     * @return \Traversable
     */
    public function aggregate(array $pipelines = [], array $options = [])
    {

        $this->logger->info(
            'Aggregate ',
            [
                'pipelines' => $pipelines,
                'options' => $options,
                'col' => $this->name,

            ]
        );

        return $this->collection->aggregate($pipelines, $options);
    }

    /**
     * @param $field
     * @param null $min
     * @param null $max
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function findBetween($field, $min = null, $max = null, array $options = [])
    {
        $query = [
            $field => ['$gt' => $min, '$lt' => $max]
        ];

        return $this->findBy($query, $options);
    }

    /**
     * @param $field
     * @param $min
     * @param $max
     * @param array $options
     * @return int
     */
    public function countBetween($field, $min = null, $max = null, array $options = ['maxTimeMS' => 0])
    {
        $query = [$field => ['$gt' => $min, '$lte' => $max]];

        $this->logger->info(
            'Counting  ' . $field,
            [
                'filter' => [
                    $field => ['$gt' => $min, '$lte' => $max]
                ],
                'options' => $options,
                'col' => $this->name,
            ]
        );

        $result = $this->collection->count(
            $query,
            [
                Query::NO_CURSOR_TIMEOUT,
                Query::PROJECTION => [
                    $this->getIdField() => true
                ]
            ]
        );

        return $result;
    }

    /**
     * Return the max value for a field in a collection
     *
     * @param $field
     * @param array $query
     * @param array $options
     * @return mixed
     */
    public function max($field, array $query = [], array $options = ['maxTimeMS' => 0])
    {
        $this->validateQueryOptions($options);

        $this->logger->info(
            sprintf('Maximum value in field %s', $field),
            [
                'filter' => $query,
                'options' => $options
            ]
        );

        $result = $this->findBy(
            $query,
            [
                Query::PROJECTION => [
                    $field => true
                ],
                Query::SORT => [
                    $field => -1
                ],
                Query::LIMIT => 1
            ]
        )->toArray();

        return ArrayAccessor::dget($result, '0.' . $field);

    }

    /**
     * Return the min value for a field in a collection
     *
     * @param $field
     * @param array $query
     * @param array $options
     * @return \Traversable
     */
    public function min($field, array $query = [], array $options = ['maxTimeMS' => 0])
    {
        $this->validateQueryOptions($options);

        $this->logger->info(
            sprintf('Minimum value in field %s', $field),
            [
                'filter' => $query,
                'options' => $options
            ]
        );

        $result = $this->findBy(
            $query,
            [
                Query::PROJECTION => [
                    $field => true
                ],
                Query::SORT => [
                    $field => 1
                ],
                Query::LIMIT => 1
            ]
        )->toArray();

        return ArrayAccessor::dget($result, '0.' . $field);
    }

    /**
     * @param $field
     * @param array $filter
     * @param array $options
     * @return \mixed[]
     */
    public function distinct($field, array $filter = [], array $options = ['maxTimeMS' => 0])
    {

        $this->logger->info(
            'Distinct over ' . $field,
            [
                'filter' => $filter,
                'options' => $options,
                'col' => $this->name,

            ]
        );

        return $this->collection->distinct($field, $filter, $options);
    }

    /**
     * @param array $filter
     * @param array $options
     * @return int
     */
    public function count(array $filter = [], array $options = ['maxTimeMS' => 0])
    {

        $this->logger->info(
            'Counting ',
            [
                'filter' => $filter,
                'options' => $options,
                'col' => $this->name,
            ]
        );

        return $this->collection->count($filter);
    }


    /**
     * @param array $options
     */
    private function validateQueryOptions(array $options = [])
    {
        /**
         * limit
         */
        if (isset($options[Query::SORT]) and !is_array($options[Query::SORT])) {
            throw new \InvalidArgumentException(Query::SORT . ' option must be an array');
        }

        /**
         * limit
         */
        if (isset($options[Query::PROJECTION]) and !is_array($options[Query::PROJECTION])) {
            throw new \InvalidArgumentException(Query::PROJECTION . ' option must be an array');
        }

        /**
         *  limit
         */
        if (isset($options[Query::LIMIT]) and !is_integer($options[Query::LIMIT])) {
            throw new \InvalidArgumentException(Query::LIMIT . " option must be an integer");
        }

        /**
         *  limit
         */
        if (isset($options[Query::OFFSET]) and !is_integer($options[Query::OFFSET])) {
            throw new \InvalidArgumentException(Query::OFFSET . ' option must be an array');
        }

        // TODO validate other options
    }


    /**
     * @param $id
     * @return MongoDB\BSON\ObjectID
     */
    static public function createObjectId($id)
    {
        $class = DriverClasses::ID_CLASS;

        if ($id instanceof $class) {
            return $id;
        }

        return new $class($id);
    }

    /**
     * @return string
     */
    public function getIdField()
    {
        return $this->id_field;
    }

    /**
     * @param string $id_field
     * @return Repository
     */
    public function setIdField($id_field)
    {
        $this->id_field = $id_field;

        return $this;
    }


    /**
     * @param $operations
     * @throws MalformedOperationException
     */
    private function validateBulkOperations($operations)
    {
        foreach ($operations as $op) {
            if (count($op) > 1) {
                throw new MalformedOperationException();
            }

            $key = array_keys($op)[0];

            if (!in_array(
                $key,
                [
                    'insertOne',
                    'insertMany',
                    'updateOne',
                    'updateMany',
                    'deleteOne',
                    'deleteMany'
                ]
            )
            ) {
                throw new MalformedOperationException(sprintf('%s is not a valid bulk operation'));
            }

            if (!is_array($op[$key])) {
                throw new MalformedOperationException(sprintf('%s argument must be an array'));
            }
        }
    }

    /**
     * @param array $transformers
     * @return $this
     */
    public function setTranformers(array $transformers = [])
    {
        $this->transformers = $transformers;

        return $this;
    }

    public function setIndexes(array $indexes)
    {
        $this->indexes = $indexes;
    }

    public function getIndexes() {

        return $this->indexes;
    }

    public function getName() {

        return $this->name;
    }
}
