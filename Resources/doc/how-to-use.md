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

## Repository as Service

You can declare Repository class as service, in order to store custom request or inject only repository instead of the whole manager.

Example :

```yml
#services.yml

parameters:
#Declare the name of the mongo collection
    user_collection_name: "User"

services:
    app.repository.user:
        parent: mongodbbundle.repository
        class: AppBundle\Repository\UserRepository
        arguments: ["%user_collection_name%", "@document.manager"]

```

Then the repository class:

```php

<?php
//AppBundle\Repository\UserRepository.php
namespace AppBundle\Repository;

use Pouzor\MongoDBBundle\Repository\Repository;

/**
 * Class UserRepository
 * @package AppBundle\Repository
 */
class UserRepository extends Repository {

    public function findOneByEmail($email) {

        return $this->findOneBy(["email" => $email, "activated" => true], []);
    }
}


```

So you can easily inject in your project class :

```yml
#services.yml

services:
    app.command.my_command:
        class: AppBundle\Command\IngestUserCommand
        calls:
          - [setUserRepository, ["@app.repository.user"]]
        tags:
            - { name: console.command }

```
