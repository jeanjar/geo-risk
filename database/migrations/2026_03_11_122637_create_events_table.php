<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('type')->index();
            $table->float('lat');
            $table->float('lng');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->float('severity')->nullable();
            $table->string('source');
            $table->timestamp('timestamp');
            $table->timestamps();

            $table->index(['lat', 'lng']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
