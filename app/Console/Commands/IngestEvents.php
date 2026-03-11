<?php

namespace App\Console\Commands;

use App\Services\EventIngestionService;
use Illuminate\Console\Command;

class IngestEvents extends Command
{
    protected $signature = 'events:ingest';

    protected $description = 'Ingest events from all external data sources';

    public function handle(EventIngestionService $service): int
    {
        $this->info('Starting event ingestion...');

        $count = $service->ingestAll();

        $this->info("Done. Ingested {$count} events.");

        return self::SUCCESS;
    }
}
