<?php

namespace Workdo\ProductVault\Listeners;

use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    public function handle(SuperAdminMenuEvent $event): void
    {
        $menu = $event->menu;

        // Parent menu
        $menu->add([
            "title"      => __("Product Vault"),
            "icon"       => "vault",
            "name"       => "product-vault",
            "parent"     => null,
            "order"      => 70,
            "ignore_if"  => [],
            "depend_on"  => [],
            "route"      => "",
            "module"     => "ProductVault",
            "permission" => "Manage ProductVault",
        ]);

        // Sub: Manage Products
        $menu->add([
            "title"      => __("Manage Products"),
            "icon"       => "package",
            "name"       => "pv-manage",
            "parent"     => "product-vault",
            "order"      => 1,
            "ignore_if"  => [],
            "depend_on"  => [],
            "route"      => "product-vault.index",
            "module"     => "ProductVault",
            "permission" => "Manage ProductVault",
        ]);

        // Sub: Purchases
        $menu->add([
            "title"      => __("Purchases"),
            "icon"       => "receipt",
            "name"       => "pv-purchases",
            "parent"     => "product-vault",
            "order"      => 2,
            "ignore_if"  => [],
            "depend_on"  => [],
            "route"      => "product-vault.purchases.index",
            "module"     => "ProductVault",
            "permission" => "Manage ProductVault",
        ]);
    }
}