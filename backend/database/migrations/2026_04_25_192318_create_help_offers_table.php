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
        Schema::create('help_offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->constrained('requests')
                ->onDelete('cascade');

            $table->foreignId('helper_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->text('message')->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected'
            ])->default('pending');
            $table->decimal('proposed_budget', 8, 2)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_offers');
    }
};
