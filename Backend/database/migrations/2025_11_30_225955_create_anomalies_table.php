<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomalies', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id');

            $table->string('type', 100); // rage_click, fast_scroll, etc.
            $table->jsonb('details')->nullable();

            $table->timestamp('detected_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('user_id');
            $table->index('type');
        });

        DB::statement('CREATE INDEX anomalies_details_gin ON anomalies USING GIN (details);');
    }

    public function down(): void
    {
        Schema::dropIfExists('anomalies');
    }
};
