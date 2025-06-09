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
        Schema::create('vulnerabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->onDelete('cascade');

            $table->string('vuln_name');
            $table->string('vuln_type');
            $table->string('vuln_cwe')->nullable();
            $table->string('sink_name')->nullable();
            $table->unsignedInteger('sink_line')->nullable();
            $table->string('sink_file')->nullable();

            $table->json('source_name')->nullable();
            $table->json('source_line')->nullable();
            $table->json('source_file')->nullable();

            $table->string('vuln_hash')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vulnerabilities');
    }
};
