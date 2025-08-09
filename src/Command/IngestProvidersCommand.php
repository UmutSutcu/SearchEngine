<?php
namespace App\Command;

use App\Service\IngestionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:ingest', description: 'Tüm providerlardan içeri aktar')]
final class IngestProvidersCommand extends Command
{
    public function __construct(private IngestionService $svc)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $n = $this->svc->ingestAll();
        $output->writeln("Ingested: $n");
        return Command::SUCCESS;
    }
}
