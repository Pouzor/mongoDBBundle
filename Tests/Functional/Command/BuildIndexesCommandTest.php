<?php

namespace Pouzor\MongoDBBundle\Tests\Functional\Command;

use Pouzor\MongoDBBundle\Command\BuildIndexesCommand;
use Pouzor\MongoDBBundle\Tests\MongoTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BuildIndexesCommandTest extends MongoTestCase {

    public function testExecute() {

        $command = new BuildIndexesCommand();
        $command->setManager($this->manager);

        $indexes = $this->testRepo->listIndexes();
        $base = ['_id_'];
        foreach ($indexes as $i) {
            $this->assertContains($i['name'], $base);
        }

        $app = new Application($this->k);

        $app->add($command);

        $tester = new CommandTester($command);

        $tester->execute(array(
            '-vvv' => null,
        ));

        $indexes = $this->testRepo->listIndexes();
        $base = ['_id_', 'bar_-1'];

        foreach ($indexes as $i) {
            $this->assertContains($i['name'], $base);
        }

    }
}