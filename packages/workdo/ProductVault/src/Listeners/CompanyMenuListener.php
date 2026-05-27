<?php
namespace Workdo\ProductVault\Listeners;
use App\Events\CompanyMenuEvent;
class CompanyMenuListener
{
    public function handle(CompanyMenuEvent $event): void
    {
        $menu = $event->menu;
        $menu->add([
            "title"     => __("Product Vault"),
            "icon"      => "vault",
            "name"      => "product-vault",
            "parent"    => null,
            "order"     => 70,
            "ignore_if" => [],
            "depend_on" => [],
            "route"     => "",
            "module"    => "ProductVault",
        ]);
        $menu->add([
            "title"     => __("Marketplace"),
            "icon"      => "shopping-cart",
            "name"      => "pv-market",
            "parent"    => "product-vault",
            "order"     => 1,
            "ignore_if" => [],
            "depend_on" => [],
            "route"     => "vault-marketplace.index",
            "module"    => "ProductVault",
        ]);
        $menu->add([
            "title"     => __("My Library"),
            "icon"      => "file-check",
            "name"      => "pv-library",
            "parent"    => "product-vault",
            "order"     => 2,
            "ignore_if" => [],
            "depend_on" => [],
            "route"     => "vault-library.index",
            "module"    => "ProductVault",
        ]);
    }
}