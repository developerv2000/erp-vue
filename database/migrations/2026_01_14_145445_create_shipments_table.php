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
        Schema::create('shipments', function (Blueprint $table) {
            $table->unsignedMediumInteger('id')->autoIncrement();

            $table->unsignedInteger('manufacturer_id')
                ->index()
                ->foreign()
                ->references('id')
                ->on('manufacturers');

            $table->unsignedTinyInteger('transportation_method_id') // 'Auto', 'Air' or 'Sea'
                ->index()
                ->foreign()
                ->references('id')
                ->on('transportation_methods')
                ->nullable();

            $table->unsignedTinyInteger('destination_id') // 'Riga' or 'Destination country'
                ->index()
                ->foreign()
                ->references('id')
                ->on('shipment_destinations')
                ->nullable();

            $table->unsignedMediumInteger('pallets_quantity')->nullable();
            $table->unsignedMediumInteger('volume')->nullable();
            $table->date('transportation_requested_at')->nullable();
            $table->string('forwarder')->nullable();
            $table->unsignedMediumInteger('price')->nullable();

            $table->unsignedTinyInteger('currency_id')
                ->index()
                ->foreign()
                ->references('id')
                ->on('currencies')
                ->nullable();

            $table->date('rate_approved_at')->nullable();
            $table->date('confirmed_at')->nullable();

            $table->timestamp('completed_at')->nullable(); // action
            $table->timestamp('arrived_at_warehouse')->nullable(); // action

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
