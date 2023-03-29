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
        Schema::create('resources', function (Blueprint $table) {
            //result.results[].resources[].id
            $table->string('id');
            $table->foreignId('user_id');
            $table
                ->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            //result.results[].resources[].package_id
            $table->string('dataset_id');
            //result.results[].resources[].state
            $table->string('state');
            //result.results[].resources[].name
            $table->string('name', 2000);
            //result.results[].resources[].description
            $table->string('description', 2000);
            //result.results[].resources[].format
            $table->string('format');
            //result.results[].resources[].url
            $table->string('url', 2000);
            //result.results[].resources[].validation_status
            $table->string('validation_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
