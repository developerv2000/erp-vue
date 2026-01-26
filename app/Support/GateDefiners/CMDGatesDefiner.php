<?php

namespace App\Support\GateDefiners;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;

class CMDGatesDefiner extends GateDefiner
{
    protected static function permissions(): array
    {
        return [
            Permission::CAN_VIEW_CMD_ORDERS_NAME,
            Permission::CAN_VIEW_CMD_ORDERS_OF_ALL_BDMS_NAME,
            Permission::CAN_VIEW_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_CMD_INVOICES_NAME,

            Permission::CAN_EDIT_CMD_ORDERS_NAME,
            Permission::CAN_EDIT_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_EDIT_CMD_INVOICES_NAME,
        ];
    }

    protected static function defineCustomGates(): void
    {
        /**
         * Determine whether the user can edit the currently selected CMD order.
         *
         * Rules:
         * 1. User must have the base permission to edit CMD orders.
         * 2. Users with global permission can edit orders of all BDMs.
         * 3. Otherwise, user can only edit orders that belong to their own BDM scope.
         */
        Gate::define('edit-current-CMD-order', function ($user) {

            // User must have the base permission to edit CMD orders
            if (Gate::denies('edit-CMD-orders')) {
                return false;
            }

            // Users with global permission can edit any CMD order
            if (Gate::allows('edit-CMD-orders-of-all-BDMs')) {
                return true;
            }

            // Retrieve the current order from the route parameter
            $order = request()->route('record');

            // If the order or manufacturer is missing, deny access
            if (!$order || !$order->manufacturer) {
                return false;
            }

            // Allow editing only if the order belongs to the user's BDM scope
            return $user->id === $order->manufacturer->bdm_user_id;
        });

        /**
         * Determine whether the user can edit the currently selected CMD order product.
         *
         * Rules:
         * 1. User must have the base permission to edit CMD order products.
         * 2. Users with global permission can edit order products of all BDMs.
         * 3. Otherwise, user can only edit order products that belong to their own BDM scope.
         */
        Gate::define('edit-current-CMD-order-product', function ($user) {
            // User must have the base permission to edit CMD order products
            if (Gate::denies('edit-CMD-order-products')) {
                return false;
            }

            // Users with global permission can edit any CMD order product
            if (Gate::allows('edit-CMD-orders-of-all-BDMs')) {
                return true;
            }

            // Retrieve the current order from the route parameter
            $productId = request()->route('record');
            $product = OrderProduct::find($productId);

            // If the product, order or manufacturer is missing, deny access
            if (!$product || !$product->order || !$product->order->manufacturer) {
                return false;
            }

            // Allow editing only if the order product belongs to the user's BDM scope
            return $user->id === $product->order->manufacturer->bdm_user_id;
        });

        /**
         * Determine whether the user can edit the currently selected CMD invoice.
         *
         * Rules:
         * 1. User must have the base permission to edit CMD invoices.
         * 2. Users with global permission can edit invoices of all BDMs.
         * 3. Otherwise, user can only edit invoices that belong to their own BDM scope.
         */
        Gate::define('edit-current-CMD-invoice', function ($user) {
            // User must have the base permission to edit CMD invoices
            if (Gate::denies('edit-CMD-invoices')) {
                return false;
            }

            // Users with global permission can edit any CMD invoices
            if (Gate::allows('edit-CMD-orders-of-all-BDMs')) {
                return true;
            }

            // Retrieve the current order from the route parameter
            $invoiceId = request()->route('record');
            $invoice = Invoice::onlyProductionType()->find($invoiceId);

            // If the invoice, order or manufacturer is missing, deny access
            if (!$invoice || !$invoice->invoiceable || !$invoice->invoiceable->manufacturer) {
                return false;
            }

            // Allow editing only if the invoice belongs to the user's BDM scope
            return $user->id === $invoice->invoiceable->manufacturer->bdm_user_id;
        });
    }
}
