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

```

Then, add the file ```app/config/mongo/default.yml```. This file is used to build indexes with the command ```mongo:indexes:build```


```
#app/config/mongo/default/yml
Game:
    fields: ~
    indexes:
        uuid: 1
        source_id:
            fields:
                source: 1
                idExt: 1
                uuid: 1
            options:
                unique: true
                sparse: false
        idExt: -1
        source: 1
```

