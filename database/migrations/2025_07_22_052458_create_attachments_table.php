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
        Schema::create('attachments', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('filename');
            $table->string('folder');
            $table->decimal('file_size_in_mb', 6, 2);
            $table->unsignedInteger('attachable_id')->index();
            $table->string('attachable_type')->index();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
