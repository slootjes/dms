<?php

namespace App\Console\Command;

use App\Repository\DocumentRepository;
use App\VO\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class IndexCommand extends Command
{
    const OPTION_FROM = 'from';

    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $path;

    /**
     * @param DocumentRepository $documentRepository
     * @param string $documentPath
     */
    public function __construct(DocumentRepository $documentRepository, string $documentPath)
    {
        $this->repository = $documentRepository;
        $this->path = $documentPath;

        parent::__construct('document:index');
    }

    protected function configure()
    {
        $this->addOption(self::OPTION_FROM, null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $from = null;
        if (!empty($input->getOption(self::OPTION_FROM))) {
            $from = new \DateTimeImmutable($input->getOption(self::OPTION_FROM));
        }

        $finder = new Finder();
        $finder->ignoreUnreadableDirs()->files()->in($this->path)->depth('> 1')->name('*.pdf');
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $useBody = true;
                try {
                    index:
                    $document = Document::fromFile($file);
                    if (!empty($from) && $document->getCreated() < $from) {
                        continue;
                    }
                    $output->writeln( $file->getRelativePathname());
                    $this->repository->index($document, $useBody);
                } catch (\Exception $e) {
                    $useBody = false;
                    $output->writeln('Error indexing: :' . $e->getMessage());
                    goto index;
                }
            }
        }
        return 0;
    }
}
