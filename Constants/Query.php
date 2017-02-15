<?php

namespace Pouzor\MongoDBBundle\Constants;

/**
 * Query options for mongo cursors
 *
 * Class Query
 * @package Pouzor\MongoDBBundle\Constants
 */
final class Query
{
    /**
     * The number of documents to skip before returning
     */
    const OFFSET = 'skip';

    /**
     * The number of documents to be returned
     */
    const LIMIT = 'limit';

    /**
     * The order in which to return matching documents
     */
    const SORT = 'sort';

    /**
     * The number of documents to return per batch
     */
    const BATCH_SIZE = 'batchSize';

    /**
     * Stream the data down full blast in multiple "reply" packets.
     * Faster when you are pulling down a lot of data and you know
     * you want to retrieve it all
     */
    const EXHAUST = 'exhaust';

    /**
     * Block rather than returning no data. After a period, time out.
     * Useful for tailable cursor
     */
    const AWAIT_DATA = 'awaitData';

    /**
     * Do not timeout a cursor that has been idle for more then 10 minutes
     */
    const NO_CURSOR_TIMEOUT = 'noCursorTimeout';

    /**
     * Specifies the fields to return using booleans or projection operators
     */
    const PROJECTION = 'projection';

    /**
     * Cursor will not be closed when the last data is retrieved.
     * You can resume this cursor later
     */
    const TAILABLE = 'tailable';

    /**
     * maxTimeMS (integer): The maximum amount of time to allow the query to
     * run. If "$maxTimeMS" also exists in the modifiers document, this
     * option will take precedence.
     */
    const MAX_TIME_MS = 'maxTimeMS';


    /**
     * @param string | int $time
     * @return MongoDB\BSON\UTCDateTime
     * @throws \Exception
     */
    static function createDate($time = null)
    {
        $dateClass = DriverClasses::DATE_CLASS;

        if (!$time) {
            return new $dateClass(time() * 1000);
        }

        if (is_int($time)) {
            return new $dateClass($time * 1000);
        }

        if (strtotime($time) === false) {
            throw new \Exception('Invalid time');
        }

        $utime = (new \DateTime($time));

        return new $dateClass($utime);
    }

}
