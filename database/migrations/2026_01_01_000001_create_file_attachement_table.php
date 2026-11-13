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
        $tableName = config('laravel-attachment.table', 'file-attachement');

        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('model')->comment('Fully qualified model class name, e.g. App\\Models\\Senior');
                $table->unsignedBigInteger('model_id')->nullable()->comment('Target model record ID');
                $table->string('file_name')->comment('Original uploaded file name');
                $table->string('file_url', 128)->unique()->comment('Hashed URL token serving as the single access point for view and delete');
                $table->unsignedBigInteger('user_id')->nullable()->index()->comment('Authenticated user ID who uploaded the attachment');
                $table->timestamp('created_at')->useCurrent();

                $table->index(['model', 'model_id'], 'file_attachement_model_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('laravel-attachment.table', 'file-attachement');
        Schema::dropIfExists($tableName);
    }
};
