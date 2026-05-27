<?php

namespace Workdo\ProductVault\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Workdo\ProductVault\Entities\VaultProduct;
use Workdo\ProductVault\Entities\VaultPurchase;

class VaultMarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $categories = VaultProduct::where("status", "active")
            ->select("category")->distinct()->orderBy("category")->pluck("category")->filter()->values();

        $products = VaultProduct::where("status", "active")
            ->orderBy("created_at", "desc")->paginate(12);

        $purchasedIds = [];
        if (Auth::check()) {
            $purchasedIds = VaultPurchase::where("user_id", Auth::id())
                ->where("payment_status", "approved")->pluck("product_id")->toArray();
        }

        return view("productvault::merchant.marketplace", compact("categories", "products", "purchasedIds"));
    }

    public function show($id)
    {
        $product = VaultProduct::where("id", $id)->where("status", "active")->firstOrFail();
        $alreadyPurchased = false;
        if (Auth::check()) {
            $alreadyPurchased = VaultPurchase::where("user_id", Auth::id())
                ->where("product_id", $product->id)
                ->where("payment_status", "approved")->exists();
        }
        return view("productvault::merchant.product-detail", compact("product", "alreadyPurchased"));
    }

    public function checkout($id)
    {
        $product = VaultProduct::findOrFail($id);

        $alreadyPurchased = VaultPurchase::where("user_id", Auth::id())
            ->where("product_id", $product->id)
            ->where("payment_status", "approved")->exists();
        if ($alreadyPurchased) {
            return redirect()->route("vault-library.index")
                ->with("success", "You already own this product!");
        }

        // Currency
        $storeId = 1;
        $cs = session("current_store");
        if ($cs && is_numeric($cs)) $storeId = (int) $cs;

        $settings = DB::table("settings")->where("store_id", $storeId)->pluck("value", "name")->toArray();
        $currency = "$";
        if (!empty($settings["CURRENCYCURRENCY"])) $currency = $settings["CURRENCYCURRENCY"];
        elseif (!empty($settings["CURRENCY"])) $currency = $settings["CURRENCY"];
        elseif (!empty($settings["currency_symbol"])) $currency = $settings["currency_symbol"];

        return view("productvault::merchant.checkout", compact("product", "currency"));
    }

    public function processCheckout(Request $request, $id)
    {
        $product = VaultProduct::findOrFail($id);
        $user = Auth::user();

        $paymentMethod = $request->payment_method;

        // Free product - auto approve
        if ($product->price <= 0 || $paymentMethod === "free") {
            VaultPurchase::create([
                "user_id"       => $user->id,
                "product_id"    => $product->id,
                "store_id"      => 1,
                "price_paid"    => 0,
                "payment_type"  => "free",
                "payment_status"=> "approved",
                "payer_name"    => $user->name ?? "",
                "payer_email"   => $user->email ?? "",
            ]);
            return redirect()->route("vault-library.index")
                ->with("success", "Product added to your library!");
        }

        // Paid with external link - need receipt
        $request->validate([
            "receipt" => "required|file|mimes:jpg,jpeg,png,gif,pdf|max:5120",
        ]);

        $receiptPath = $request->file("receipt")->store("vault_receipts", "public");

        VaultPurchase::create([
            "user_id"       => $user->id,
            "product_id"    => $product->id,
            "store_id"      => 1,
            "price_paid"    => $product->price,
            "payment_type"  => "external",
            "payment_status"=> "pending",
            "payer_name"    => $user->name ?? "",
            "payer_email"   => $user->email ?? "",
            "receipt"       => $receiptPath,
            "notes"         => $request->notes ?? null,
        ]);

        return redirect()->route("vault-library.index")
            ->with("success", "Receipt submitted! Your purchase is pending review.");
    }

    public function library()
    {
        $purchases = VaultPurchase::where("user_id", Auth::id())
            ->with("product")->orderBy("created_at", "desc")->get();
        return view("productvault::merchant.purchases", compact("purchases"));
    }

    public function import($id)
    {
        $purchase = VaultPurchase::where("id", $id)
            ->where("user_id", Auth::id())
            ->where("payment_status", "approved")
            ->firstOrFail();

        $product = $purchase->product;
        if (!$product || !$product->file_path) {
            return redirect()->route("vault-library.index")->with("error", "File not available.");
        }

        $filePath = storage_path("app/" . $product->file_path);
        if (!file_exists($filePath)) {
            return redirect()->route("vault-library.index")->with("error", "File not found.");
        }

        $product->increment("downloads_count");
        return response()->download($filePath, basename($filePath));
    }
}
