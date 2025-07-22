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
        Schema::create('comments', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('body', 6000);

            $table->unsignedInteger('commentable_id')->index();
            $table->string('commentable_type')->index();

            $table->unsignedSmallInteger('user_id')
                // ->index() // non-index because used rarely
                ->foreign()
                ->references('id')
                ->on('users');

            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
