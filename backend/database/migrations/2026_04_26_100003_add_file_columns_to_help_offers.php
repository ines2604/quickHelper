<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('help_offers', function (Blueprint $table) {
            if (!Schema::hasColumn('help_offers', 'file_path')) {
                $table->string('file_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('help_offers', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('help_offers', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('help_offers', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_name', 'file_type']);
        });
    }
};
