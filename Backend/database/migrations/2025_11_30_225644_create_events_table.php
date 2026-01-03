<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('session_id')->nullable();
            $table->uuid('user_id')->nullable();

            $table->string('type', 50);        // tap, view, scroll, error, navigation
            $table->string('name', 255);       // "provider.open", "button.book"

            $table->jsonb('value')->nullable(); // metadata
            $table->integer('device_x')->nullable();
            $table->integer('device_y')->nullable();

            $table->timestamp('timestamp')->useCurrent();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('session_id')->references('id')->on('user_sessions')->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('user_id');
            $table->index('session_id');
            $table->index('type');
            $table->index('name');
            $table->index('timestamp');
        });

        // Index GIN sur JSONB (Postgres)
        DB::statement('CREATE INDEX events_value_gin ON events USING GIN (value);');
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
