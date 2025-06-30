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
        Schema::create('permissions', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->autoIncrement();
            $table->string('name')->unique();

            $table->unsignedSmallInteger('department_id') // Department admins can attach only global and department permissions to users.
                ->foreign()
                ->references('id')
                ->on('departments')
                ->nullable();

            $table->boolean('global')->default(false); // Global permissions can be attached to any department users.
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedSmallInteger('user_id')
                ->foreign()
                ->references('id')
                ->on('users');

            $table->unsignedSmallInteger('permission_id')
                ->foreign()
                ->references('id')
                ->on('permissions');

            $table->primary(['user_id', 'permission_id']);
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedSmallInteger('permission_id')
                ->foreign()
                ->references('id')
                ->on('permissions');

            $table->unsignedSmallInteger('role_id')
                ->foreign()
                ->references('id')
                ->on('roles');

            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
