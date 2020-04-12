<?php

namespace App\Console\Command;

use App\Repository\DocumentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * @param DocumentRepository $documentRepository
     */
    public function __construct(DocumentRepository $documentRepository)
    {
        $this->repository = $documentRepository;

        parent::__construct('document:setup');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Setting up, can take a few minutes...');

        $this->repository->setup();

        $output->writeln('Done! You can now proceed to index your documents.');

        return 0;
    }
}
