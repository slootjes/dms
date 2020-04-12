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
        $this->repository->setup();

        $output->writeln('Setup complete, you can now index your documents');

        return 0;
    }
}
