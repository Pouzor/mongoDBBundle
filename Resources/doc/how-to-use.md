Using the bundle
----------------

Acces to document manager is really simple. For each configured connection, the bundle 
creates a new document manager service. For the configuration showed in install.md, that will be: `document.manager.master` and `document.manager.backup`.
If a default connection is stablished, then a default document manager is created as an alias of the corresponding connection, in this 
case `document.manager` is an alias de `document.manager.master`. 


So, with symfony we use it like this: 

```php

    // we will use the default manager for the example
    $dm = $this->container->get('document.manager');
    
    // find a document by id:
    $client = $dm->find('Client', $id);
    
    // using repositories
    $repository = $dm->getRepository('Client');
    
    // find by id
    $repository->find($id);
    
    // update by id
    $repository->update($id, [
        '$set' => [ 'status' => 'Active']
    ];
    
    // delete by id
    $repository->delete($id);
    
    // find one
    
    $repository->findOneBy([ 'status' => 'Active'], [
        Query::SORT => [ 'lastLogin' => -1 ],
        Query::PROJECTION => [
            'email' => true
        ],
        Query::NO_CURSOR_TIMEOUT,
        //... and others. take a look at the Query class
    ]);   

```

The main advantage of the new mongo version is the facilities to create batch multi-operations, which is included in the repository: 

```php

    $result = $repository->bulk(
    [
        'insertOne' => [
            'name' => 'developper',
            'lastName' => 'operationnel' 
        ],
        'updateOne' => [
            [
                'status' => 'Active'
            ],
            [
                '$currentDate' => [ 'lastlogin' => true ]
            ]
        ],
        'deleteMany' => [
            [
                'status' => 'Inactive'
            ]
        ]
    ]);
    
    $result->getMatchedCount();
    $result->getUpsertedCount();
    $result->getUpdatedCount();
    //...

```