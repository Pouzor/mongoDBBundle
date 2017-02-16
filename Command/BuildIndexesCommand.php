<?php

namespace Pouzor\MongoDBBundle\Command;

use MongoDB\Client;
use MongoDB\Collection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class BuildIndexesCommand extends ContainerAwareCommand
{

    private $manager;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mongo:indexes:build')
            ->addOption('collection', 'c', InputOption::VALUE_OPTIONAL, 'Build indexes just for this collection')
            ->addOption('rebuild', 'r', InputOption::VALUE_NONE, 'Drop and create indexes')
            ->setDescription('Build indexes in a mongo database');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $rebuild = $input->getOption('rebuild');
        $collection = $input->getOption('collection');

        $callback = function ($name) use ($style) {
            $style->comment(sprintf('%s : Ok', $name));
        };

        if ($collection) {
            $this->manager->getRepository($collection)->buildIndexes($callback);
        } else {
            $this->manager->buildIndexes($rebuild, $callback);
        }

    }

    public function setManager($manager) {

        $this->manager = $manager;
    }
}