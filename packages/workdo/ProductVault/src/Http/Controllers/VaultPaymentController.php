<?php

namespace Workdo\ProductVault\Http\Controllers;

use Workdo\ProductVault\Entities\VaultProduct;
use Workdo\ProductVault\Entities\VaultPurchase;
use Workdo\ProductVault\Entities\VaultNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VaultPaymentController extends \App\Http\Controllers\Controller
{
    /**
     * Checkout page - shows product + ALL enabled payment methods
     */
    public function checkout($id)
    {
        $product = VaultProduct::where("status", "active")->findOrFail($id);

        // Check if already purchased
        $alreadyPurchased = VaultPurchase::where("product_id", $id)
            ->where("user_id", Auth::id())
            ->where("payment_status", "approved")
            ->exists();

        if ($alreadyPurchased) {
            return redirect()->route("vault-library.index")
                ->with("info", __("You already own this product."));
        }

        // Check if pending purchase exists
        $pendingPurchase = VaultPurchase::where("product_id", $id)
            ->where("user_id", Auth::id())
            ->whereIn("payment_status", ["pending", "bank_pending"])
            ->first();

        if ($pendingPurchase) {
            return redirect()->route("vault-library.index")
                ->with("info", __("You already have a pending purchase for this product."));
        }

        // Load ALL payment settings (same as plan checkout)
        $admin_payments_details = [];
        if (function_exists("getSuperAdminAllSetting")) {
            $admin_payments_details = getSuperAdminAllSetting();
        } else {
            $rows = \DB::table("settings")->get();
            foreach ($rows as $row) {
                $admin_payments_details[$row->name] = $row->value;
            }
        }

        // Currency
        $currency = $admin_payments_details["CURRENCY"] ?? "$";
        $currencyName = $admin_payments_details["CURRENCY_NAME"] ?? "USD";

        // Build list of enabled gateways
        $enabledGateways = $this->getEnabledGateways($admin_payments_details);

        if (empty($enabledGateways)) {
            return redirect()->back()
                ->with("error", __("No payment method available. Please contact admin."));
        }

        return view("productvault::merchant.checkout", compact(
            "product", "admin_payments_details", "currency", "currencyName", "enabledGateways"
        ));
    }

    /**
     * Bank Transfer Payment (receipt upload)
     */
    public function payWithBank(Request $request, $id)
    {
        $product = VaultProduct::where("status", "active")->findOrFail($id);

        $request->validate([
            "payer_name"  => "required|string|max:255",
            "payer_email" => "required|email|max:255",
            "receipt"     => "required|file|mimes:png,jpg,jpeg,pdf|max:5120",
            "notes"       => "nullable|string|max:500",
        ]);

        $receiptPath = null;
        if ($request->hasFile("receipt")) {
            $file = $request->file("receipt");
            $fileName = time() . "_" . $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $file->move(storage_path("app/public/vault_receipts"), $fileName);
            $receiptPath = "vault_receipts/" . $fileName;
        }

        VaultPurchase::create([
            "product_id"     => $id,
            "user_id"        => Auth::id(),
            "store_id"       => Auth::user()->current_store ?? 0,
            "price_paid"     => $product->price,
            "payment_type"   => "Bank Transfer",
            "payment_status" => "bank_pending",
            "payer_name"     => $request->payer_name,
            "payer_email"    => $request->payer_email,
            "receipt"        => $receiptPath,
            "notes"          => $request->notes,
            "purchased_at"   => now(),
        ]);

        $this->notifySuperAdmins(
            __("New Bank Transfer"),
            __("Merchant :name uploaded a receipt for: :product (:price)",
                ["name" => Auth::user()->name, "product" => $product->name, "price" => number_format($product->price, 2)]),
            route("product-vault.purchases"),
            "building-bank", "info"
        );

        VaultNotification::notify(
            Auth::id(), "merchant",
            __("Receipt Submitted"),
            __("Your bank transfer receipt for \":product\" has been submitted. Waiting for review.", ["product" => $product->name]),
            route("vault-library.index"), "clock", "info"
        );

        return redirect()->route("vault-library.index")
            ->with("success", __("Receipt submitted successfully! Admin will review it shortly."));
    }

    /**
     * Manual Payment Request
     */
    public function payManualRequest(Request $request, $id)
    {
        $product = VaultProduct::where("status", "active")->findOrFail($id);

        VaultPurchase::create([
            "product_id"     => $id,
            "user_id"        => Auth::id(),
            "store_id"       => Auth::user()->current_store ?? 0,
            "price_paid"     => $product->price,
            "payment_type"   => "Manual Request",
            "payment_status" => "pending",
            "payer_name"     => Auth::user()->name,
            "payer_email"    => Auth::user()->email,
            "notes"          => $request->notes ?? "",
            "purchased_at"   => now(),
        ]);

        $this->notifySuperAdmins(
            __("New Purchase Request"),
            __("Merchant :name requested: :product (:price)", ["name" => Auth::user()->name, "product" => $product->name, "price" => number_format($product->price, 2)]),
            route("product-vault.purchases"), "send", "warning"
        );

        return redirect()->route("vault-library.index")
            ->with("success", __("Purchase request sent! Waiting for admin approval."));
    }

    /**
     * Process Online Payment (generic handler for any gateway)
     * Creates pending purchase, then merchant can complete payment
     */
    public function processOnlinePayment(Request $request, $id)
    {
        $product = VaultProduct::where("status", "active")->findOrFail($id);
        $gateway = $request->input("payment_gateway", "unknown");

        // Create purchase as "pending" - admin will verify payment
        VaultPurchase::create([
            "product_id"     => $id,
            "user_id"        => Auth::id(),
            "store_id"       => Auth::user()->current_store ?? 0,
            "price_paid"     => $product->price,
            "payment_type"   => $gateway,
            "payment_status" => "pending",
            "payer_name"     => Auth::user()->name,
            "payer_email"    => Auth::user()->email,
            "notes"          => "Online payment via " . $gateway,
            "purchased_at"   => now(),
        ]);

        $this->notifySuperAdmins(
            __("New Online Payment"),
            __("Merchant :name paid via :gateway for: :product (:price)",
                ["name" => Auth::user()->name, "gateway" => $gateway, "product" => $product->name, "price" => number_format($product->price, 2)]),
            route("product-vault.purchases"), "credit-card", "success"
        );

        return redirect()->route("vault-library.index")
            ->with("success", __("Payment submitted via :gateway! Admin will verify and approve.", ["gateway" => $gateway]));
    }

    /**
     * Get list of enabled payment gateways from settings
     */
    private function getEnabledGateways($settings)
    {
        $gateways = [];

        // Bank Transfer
        if (isset($settings["is_bank_transfer_enabled"]) && $settings["is_bank_transfer_enabled"] == "on") {
            $gateways["bank_transfer"] = [
                "id" => "bank_transfer",
                "name" => __("Bank Transfer"),
                "icon" => "building-bank",
                "type" => "upload",
                "enabled" => true,
            ];
        }

        // Manual
        if (isset($settings["is_manually_enabled"]) && $settings["is_manually_enabled"] == "on") {
            $gateways["manually"] = [
                "id" => "manually",
                "name" => __("Manual Request"),
                "icon" => "send",
                "type" => "manual",
                "enabled" => true,
            ];
        }

        // Stripe
        if (isset($settings["is_stripe_enabled"]) && $settings["is_stripe_enabled"] == "on") {
            $gateways["stripe"] = [
                "id" => "stripe",
                "name" => __("Stripe"),
                "icon" => "brand-stripe",
                "type" => "online",
                "enabled" => !empty($settings["stripe_publishable_key"]) && !empty($settings["stripe_secret_key"]),
                "publishable_key" => $settings["stripe_publishable_key"] ?? "",
            ];
        }

        // Razorpay
        if (isset($settings["is_razorpay_enabled"]) && $settings["is_razorpay_enabled"] == "on") {
            $gateways["razorpay"] = [
                "id" => "razorpay",
                "name" => __("Razorpay"),
                "icon" => "brand-razorpay",
                "type" => "online",
                "enabled" => !empty($settings["razorpay_key"]) && !empty($settings["razorpay_secret"]),
            ];
        }

        // PayPal
        if (isset($settings["is_paypal_enabled"]) && $settings["is_paypal_enabled"] == "on") {
            $gateways["paypal"] = [
                "id" => "paypal",
                "name" => __("PayPal"),
                "icon" => "brand-paypal",
                "type" => "online",
                "enabled" => !empty($settings["paypal_client_id"]),
            ];
        }

        // Paystack
        if (isset($settings["is_paystack_enabled"]) && $settings["is_paystack_enabled"] == "on") {
            $gateways["paystack"] = [
                "id" => "paystack",
                "name" => __("Paystack"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["paystack_public_key"]),
            ];
        }

        // Skrill
        if (isset($settings["is_skrill_enabled"]) && $settings["is_skrill_enabled"] == "on") {
            $gateways["skrill"] = [
                "id" => "skrill",
                "name" => __("Skrill"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["skrill_email"]),
            ];
        }

        // Mollie
        if (isset($settings["is_mollie_enabled"]) && $settings["is_mollie_enabled"] == "on") {
            $gateways["mollie"] = [
                "id" => "mollie",
                "name" => __("Mollie"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["mollie_api_key"]),
            ];
        }

        // Paytabs
        if (isset($settings["is_paytabs_enabled"]) && $settings["is_paytabs_enabled"] == "on") {
            $gateways["paytabs"] = [
                "id" => "paytabs",
                "name" => __("PayTabs"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["paytabs_profile_id"]),
            ];
        }

        // MyFatoorah
        if (isset($settings["is_myfatoorah_enabled"]) && $settings["is_myfatoorah_enabled"] == "on") {
            $gateways["myfatoorah"] = [
                "id" => "myfatoorah",
                "name" => __("MyFatoorah"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["myfatoorah_pay_api_key"]),
            ];
        }

        // Mercado
        if (isset($settings["is_mercado_enabled"]) && $settings["is_mercado_enabled"] == "on") {
            $gateways["mercado"] = [
                "id" => "mercado",
                "name" => __("Mercado Pago"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["mercado_access_token"]),
            ];
        }

        // PayTR
        if (isset($settings["is_paytr_enabled"]) && $settings["is_paytr_enabled"] == "on") {
            $gateways["paytr"] = [
                "id" => "paytr",
                "name" => __("PayTR"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["paytr_merchant_id"]),
            ];
        }

        // YooKassa
        if (isset($settings["is_yookassa_enabled"]) && $settings["is_yookassa_enabled"] == "on") {
            $gateways["yookassa"] = [
                "id" => "yookassa",
                "name" => __("YooKassa"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["yookassa_shop_id_key"]),
            ];
        }

        // PhonePe
        if (isset($settings["is_phonepe_enabled"]) && $settings["is_phonepe_enabled"] == "on") {
            $gateways["phonepe"] = [
                "id" => "phonepe",
                "name" => __("PhonePe"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["phonepe_merchant_key"]),
            ];
        }

        // Tap
        if (isset($settings["is_tap_enabled"]) && $settings["is_tap_enabled"] == "on") {
            $gateways["tap"] = [
                "id" => "tap",
                "name" => __("Tap"),
                "icon" => "credit-card",
                "type" => "online",
                "enabled" => !empty($settings["tap_secret_key"]),
            ];
        }

        // COD
        if (isset($settings["is_cod_enabled"]) && $settings["is_cod_enabled"] == "on") {
            $gateways["cod"] = [
                "id" => "cod",
                "name" => __("Cash on Delivery"),
                "icon" => "cash",
                "type" => "manual",
                "enabled" => true,
            ];
        }

        // Always include bank transfer as fallback
        if (!isset($gateways["bank_transfer"])) {
            $gateways["bank_transfer"] = [
                "id" => "bank_transfer",
                "name" => __("Bank Transfer"),
                "icon" => "building-bank",
                "type" => "upload",
                "enabled" => true,
            ];
        }

        // Always include manual as fallback
        if (!isset($gateways["manually"])) {
            $gateways["manually"] = [
                "id" => "manually",
                "name" => __("Manual Request"),
                "icon" => "send",
                "type" => "manual",
                "enabled" => true,
            ];
        }

        return $gateways;
    }

    private function notifySuperAdmins($title, $message, $link, $icon = "bell", $iconColor = "primary")
    {
        $superAdmins = \DB::table("users")->where("type", "super admin")->get();
        foreach ($superAdmins as $admin) {
            VaultNotification::notify($admin->id, "admin", $title, $message, $link, $icon, $iconColor);
        }
    }
}
