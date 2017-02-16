Pouzor MongoDB Bundle
=================
[![Build Status](https://travis-ci.org/Pouzor/mongoDBBundle.svg?branch=master)](https://travis-ci.org/Pouzor/mongoDBBundle) [![Code Coverage](https://scrutinizer-ci.com/g/Pouzor/mongoDBBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Pouzor/mongoDBBundle/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Pouzor/mongoDBBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Pouzor/mongoDBBundle/?branch=master)

MongoDBBundle is the easiest and fastest way to integrate MongoDB in your project with the new php driver (Support php >=5.4 and php 7.x). 

It's not an ODM : you don't need to declare all your data model. You will work with array object same as you can get/set in mongoDB (after json encode/decode)
#### /!\ This bundle is only working with mongodb.so driver. 
For the legacy driver (mongo.so), please check [Pouzor/MongoBundle] (https://github.com/Pouzor/mongobundle)

It use mongodb.so + https://github.com/mongodb/mongo-php-library

### Usage

When the bundle is configured, you can access to the mongo manager service, who provide acces to Repository Service (aka Mongo collection manager). 
Then, is pretty easy to request this Repository
````php
//document.manager.my_project is specific mongodb (db, host, port...) configuration in app/config/config.yml
$manager = $this->getContainer()->get('document.manager.my_project');


$repository = $manager->getRepository('User');


$user = $repository->find($id);
$users = $repository->findBy(['location' => "Paris"]);


$repository->deleteMany(['gift' => ['$lte' => 0]]);
````

### Documentation

1.  [Installation + configuration] (Resources/doc/install.md).
    Documentation about installation and configuration features.

2.  [How to use] (Resources/doc/how-to-use.md).
    How to use mongodbbundle in your symfony project
    
    
### Contribution
    
- Thx to Ibrael Espinosa for the huge contribution to this bundle.
