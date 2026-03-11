<?php

namespace App\Jobs;

use App\Services\EventIngestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IngestEventsJob implements ShouldQueue
{
    use Queueable;

    public function handle(EventIngestionService $service): void
    {
        $service->ingestAll();
    }
}
