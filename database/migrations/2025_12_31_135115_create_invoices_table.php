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
        Schema::create('invoices', function (Blueprint $table) {
            $table->unsignedMediumInteger('id')->autoIncrement();

            $table->unsignedInteger('invoiceable_id')->index();
            $table->string('invoiceable_type')->index();

            // CMD / ELD part

            // 'Production', 'Delivery to warehouse' or 'Export'
            $table->unsignedTinyInteger('type_id')
                ->foreign()
                ->references('id')
                ->on('invoice_types');

            // 'Prepayment', 'Final payment' or 'Full payment'
            // Always 'Full payment' for invoices of 'Delivery to warehouse' and 'Export' types
            $table->unsignedTinyInteger('payment_type_id')
                ->index()
                ->foreign()
                ->references('id')
                ->on('invoice_payment_types');

            $table->timestamp('receive_date')->nullable();
            $table->string('pdf_file');

            $table->timestamp('sent_for_payment_date')->nullable(); // action

            // PRD part
            $table->timestamp('accepted_by_financier_date')->nullable();
            $table->timestamp('payment_request_date_by_financier')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('number')->nullable();
            $table->string('payment_confirmation_document')->nullable(); // SWIFT
            $table->timestamp('payment_completed_date')->nullable(); // action

            $table->timestamps();
            $table->softDeletes();
        });

        // Only for invoices of 'Production' type
        Schema::create('invoice_order_product', function (Blueprint $table) {
            $table->unsignedMediumInteger('invoice_id')
                ->foreign()
                ->references('id')
                ->on('invoices');

            $table->unsignedMediumInteger('order_product_id')
                ->foreign()
                ->references('id')
                ->on('order_products');

            $table->primary(['invoice_id', 'order_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_order_product');
    }
};
