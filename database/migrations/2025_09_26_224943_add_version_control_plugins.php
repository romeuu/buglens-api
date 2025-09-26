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
        Schema::table('plugins', function (Blueprint $table) {
            $table->timestamp('last_analyzed_at')->nullable();
            $table->string('last_analyzed_version')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn('last_analyzed_at');
            $table->dropColumn('last_analyzed_version');
        });
    }
};
