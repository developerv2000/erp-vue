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
        Schema::create('countries', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->autoIncrement();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->unsignedMediumInteger('database_processes_count')->default(0);
        });

        Schema::create('responsible_country_user', function (Blueprint $table) {
            $table->unsignedSmallInteger('country_id')
                ->foreign()
                ->references('id')
                ->on('country_codes');

            $table->unsignedSmallInteger('user_id')
                ->foreign()
                ->references('id')
                ->on('users');

            $table->primary(['country_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
        Schema::dropIfExists('responsible_country_user');
    }
};
