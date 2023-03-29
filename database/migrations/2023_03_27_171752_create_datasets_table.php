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
        Schema::create('datasets', function (Blueprint $table) {
            //result.results[].id
            $table->string('id');
            $table->foreignId('user_id');
            $table
                ->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            //result.results[].organization.name
            $table->string('executive_authority_name');
            //result.results[].state
            $table->string('state');
            //result.results[].name
            $table->string('name');
            //result.results[].title
            $table->string('title', 2000);
            //result.results[].metadata_modified
            $table->dateTime('last_updated_at');
            //result.results[].update_frequency
            $table->string('update_frequency');
            //calculated
            $table->dateTime('next_update_at')->nullable();
            //calculated
            $table->integer('days_to_update')->nullable();
            //result.results[].maintainer
            $table->string('maintainer_name');
            //result.results[].maintainer_email
            $table->string('maintainer_email');
            //calculated
            $table->enum('type', ['normal', 'debtor', 'reminder', 'inactive']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
