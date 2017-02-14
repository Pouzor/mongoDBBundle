Install the bundle
------------------

```json
//composer.json

    "repositories": [
      {
        "type": "vcs",
        "url": "git@github.com:Pouzor/mongodbbundle.git"
      }
    ],
    "require": {
        "pouzor/mongodbbundle": "^1.0.0"
    }

```

Configure the bundle
--------------------

First you need to configure the mongo_db service
```yaml
#config.yml
mongo_db:
    default_connection:   master
    connections:
        master:
            host:          %mongo_host%:%mongo_port%
            db:            %mongo_database%
            password:      %mongo_password%
            username:      %mongo_user%
            schema:        "%kernel.root_dir%/config/mongo/default.yml"
            options:       ~
        backup:
            host:          %mongo_host_backup%:%mongo_port%
            db:            %mongo_database%
            password:      %mongo_password%
            username:      %mongo_user%
            schema:        "%kernel.root_dir%/config/mongo/default.yml"
            options:       ~

```

For using with replicaSet, you need to complete the host field with list of your host, comma separated, and the name of your replicaSet

```yaml

#config.yml
mongo_db:
    default_connection:   master
    connections:
        master:
            host:          "localhost:27017,localhost:27018,localhost:27019"
            db:            %mongo_database%
            password:      %mongo_password%
            username:      %mongo_user%
            schema:        "%kernel.root_dir%/config/mongo/default.yml"
            options:
                replicaSet:    "res0"
                readPreference: "secondaryPreferred"                

```

Then, add the file ```app/config/mongo/default.yml```. This file is used to build indexes with the command ```mongo:indexes:build```


```
#app/config/mongo/default/yml
MyCollection:
    fields: ~
    indexes:
        my_field: 1 #index with same name than field
        my_specific_index: #index name on one or more field
            fields:
                my_field_2: 1
                my_field_3: 1
                my_field_4: -1
            options:
                unique: true
                sparse: false
        my_field_5: -1
        my_field_6: 1
```

