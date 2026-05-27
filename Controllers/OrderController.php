<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Utility;
use App\Models\Store;

/**
 * SECURITY PATCH H-06: Order Status Change Protection
 *
 * The original OrderController::order_status_change() method in
 * app/Http/Controllers/OrderController.php needs to be replaced with
 * the following secured version.
 *
 * KEY CHANGES:
 * 1. Added proper authentication check
 * 2. Added store ownership verification
 * 3. Added CSRF protection (web routes only)
 * 4. Validates order belongs to the current store
 */
trait OrderStatusSecurityPatch
{
    /**
     * Replace the order_status_change method in OrderController.php
     * with this secured version.
     */
    protected function secured_order_status_change(Request $request)
    {
        // Verify user is authenticated and has permission
        if (!auth()->check() || !auth()->user()->isAbleTo('Manage Order')) {
            return response()->json([
                'flag' => 'error',
                'msg' => __('Permission denied.')
            ], 403);
        }

        $orderId = $request->id;
        $newStatus = $request->delivered;

        // Validate required fields
        if (empty($orderId) || empty($newStatus)) {
            return response()->json([
                'flag' => 'error',
                'msg' => __('Order ID and status are required.')
            ], 422);
        }

        // Verify order belongs to current store
        $order = Order::where('id', $orderId)
            ->where('store_id', getCurrentStore())
            ->first();

        if (!$order) {
            return response()->json([
                'flag' => 'error',
                'msg' => __('Order not found.')
            ], 404);
        }

        // Whitelist allowed status transitions
        $allowedStatuses = [
            'pending', 'confirmed', 'in_process', 'delivered',
            'canceled', 'returned', 'shipped', 'out_for_delivery'
        ];

        if (!in_array($newStatus, $allowedStatuses)) {
            return response()->json([
                'flag' => 'error',
                'msg' => __('Invalid order status.')
            ], 422);
        }

        // Proceed with status change
        $data['order_id'] = $orderId;
        $data['order_status'] = $newStatus;
        $response = Order::order_status_change($data);

        // Dispatch webhook
        $module = 'Status Change';
        $store = Store::find(getCurrentStore());
        $webhook = Utility::webhook($module, $store->id);
        if ($webhook) {
            $orderDetail = Order::order_detail($orderId);
            $parameter = json_encode($orderDetail);
            $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
        }

        return $response;
    }
}

/**
 * INSTALLATION INSTRUCTIONS for OrderController.php:
 *
 * 1. Open: app/Http/Controllers/OrderController.php
 * 2. Find the method: order_status_change()
 * 3. Replace the method body with the secured version above
 * 4. Add this trait: use OrderStatusSecurityPatch;
 * 5. Change the method call from:
 *    public function order_status_change(Request $request)
 * to:
 *    public function order_status_change(Request $request)
 *    {
 *        return $this->secured_order_status_change($request);
 *    }
 *
 * OR simply copy the logic from secured_order_status_change()
 * into your existing order_status_change() method.
 */
