<?php

use App\Jobs\IngestEventsJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new IngestEventsJob)->everyFifteenMinutes();
