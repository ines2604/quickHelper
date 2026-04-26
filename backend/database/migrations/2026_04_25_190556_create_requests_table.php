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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('budget', 8, 2)->nullable();
            $table->enum('category', [
                'general',
                'transport',
                'education',
                'repair',
                'health',
                'housekeeping',
                'it_support',
                'other'
            ])->default('general');
            $table->enum('urgency', ['low', 'medium', 'high'])->default('medium');
            $table->timestamp('deadline')->nullable();
            $table->enum('status', ['open', 'accepted', 'in_progress', 'completed']) ->default('open');
            $table->string('location')->nullable();
            $table->foreignId('accepted_helper_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
