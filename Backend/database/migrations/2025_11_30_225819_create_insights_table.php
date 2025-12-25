<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insights', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id')->unique();

            $table->integer('impulsivity_score')->default(0);
            $table->integer('hesitation_score')->default(0);
            $table->integer('premium_tendency')->default(0);
            $table->boolean('night_user')->default(false);
            $table->integer('likely_to_book')->default(0);
            $table->integer('risk_churn')->default(0);

            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
