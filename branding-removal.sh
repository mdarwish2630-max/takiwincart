#!/bin/bash
# =============================================================
# TakiwinCart - Branding Removal Script
# Removes all external links, credits, and third-party references
# (Max Cart, ERPGo, WorkDo) from the codebase
# =============================================================

echo "========================================="
echo "  TakiwinCart - Branding Removal Script"
echo "========================================="

# Get the project root (parent of this script's directory)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

APP_NAME="${APP_NAME:-TakiwinCart}"
APP_URL="${APP_URL:-}"

echo ""
echo "Using app name: $APP_NAME"
echo "Project root: $PROJECT_ROOT"
echo ""

# Count replacements
TOTAL=0

# -------------------------------------------------------
# 1. helper.php - Default settings
# -------------------------------------------------------
echo "[1/10] Fixing app/Helper/helper.php ..."

# Replace 'Max Cart' in default settings
if [ -f "app/Helper/helper.php" ]; then
    # Replace hardcoded 'Max Cart' fallbacks with env('APP_NAME')
    sed -i "s/'Max Cart'/env('APP_NAME', '$APP_NAME')/g" app/Helper/helper.php
    # Fix the footer copyright default
    sed -i "s|\"Copyright © \" . date('Y') . \" Max Cart. All rights reserved.\"|\"Copyright © \" . date('Y') . \" \" . env('APP_NAME', '$APP_NAME') . \". All rights reserved.\"|g" app/Helper/helper.php
    echo "  -> app/Helper/helper.php updated"
fi

# -------------------------------------------------------
# 2. layouts/app.blade.php - Admin layout
# -------------------------------------------------------
echo "[2/10] Fixing resources/views/layouts/app.blade.php ..."

if [ -f "resources/views/layouts/app.blade.php" ]; then
    # Replace Max Cart meta tags
    sed -i "s/content=\"Max Cart\"/content=\"{{ env('APP_NAME', '$APP_NAME') }}\"/g" resources/views/layouts/app.blade.php
    # Replace Open Graph / Twitter meta
    sed -i "s/: 'Max Cart'/': env('APP_NAME', '$APP_NAME')'/g" resources/views/layouts/app.blade.php
    sed -i "s/Max Cart, Store with Multi theme and Multi Store/{{ env('APP_NAME') }}, E-Commerce Platform/g" resources/views/layouts/app.blade.php
    sed -i "s/Max Cart - Powerful E-Commerce Platform for Your Online Store/{{ env('APP_NAME', '$APP_NAME') }} - E-Commerce Platform/g" resources/views/layouts/app.blade.php
    # Replace maxcart-preview.png
    sed -i "s/maxcart-preview.png/app-preview.png/g" resources/views/layouts/app.blade.php
    echo "  -> resources/views/layouts/app.blade.php updated"
fi

# -------------------------------------------------------
# 3. layouts/guest.blade.php - Guest layout
# -------------------------------------------------------
echo "[3/10] Fixing resources/views/layouts/guest.blade.php ..."

if [ -f "resources/views/layouts/guest.blade.php" ]; then
    sed -i "s/content=\"Max Cart\"/content=\"{{ env('APP_NAME', '$APP_NAME') }}\"/g" resources/views/layouts/guest.blade.php
    sed -i "s/: 'Max Cart'/': env('APP_NAME', '$APP_NAME')'/g" resources/views/layouts/guest.blade.php
    sed -i "s/Max Cart, Store with Multi theme and Multi Store/{{ env('APP_NAME') }}, E-Commerce Platform/g" resources/views/layouts/guest.blade.php
    sed -i "s/Max Cart - Powerful E-Commerce Platform for Your Online Store/{{ env('APP_NAME', '$APP_NAME') }} - E-Commerce Platform/g" resources/views/layouts/guest.blade.php
    sed -i "s/maxcart-preview.png/app-preview.png/g" resources/views/layouts/guest.blade.php
    # Footer fallback
    sed -i "s/config('app.name', 'Max Cart')/config('app.name', env('APP_NAME', '$APP_NAME'))/g" resources/views/layouts/guest.blade.php
    echo "  -> resources/views/layouts/guest.blade.php updated"
fi

# -------------------------------------------------------
# 4. marketplace/marketplace.blade.php
# -------------------------------------------------------
echo "[4/10] Fixing resources/views/marketplace/marketplace.blade.php ..."

if [ -f "resources/views/marketplace/marketplace.blade.php" ]; then
    sed -i "s/:'Max Cart'/': env('APP_NAME', '$APP_NAME')'/g" resources/views/marketplace/marketplace.blade.php
    sed -i "s/: 'Max Cart'/': env('APP_NAME', '$APP_NAME')'/g" resources/views/marketplace/marketplace.blade.php
    sed -i "s/content=\"Max Cart\"/content=\"{{ env('APP_NAME', '$APP_NAME') }}\"/g" resources/views/marketplace/marketplace.blade.php
    sed -i "s/Max Cart,SaaS solution,Multi-workspace/{{ env('APP_NAME') }}, E-Commerce Platform/g" resources/views/marketplace/marketplace.blade.php
    sed -i "s/Max Cart - Powerful E-Commerce Platform/{{ env('APP_NAME', '$APP_NAME') }} - E-Commerce Platform/g" resources/views/marketplace/marketplace.blade.php
    # Footer Max Cart link
    sed -i "s|<a href=\"#\">Max Cart</a>|<a href=\"#\">{{ env('APP_NAME', '$APP_NAME') }}</a>|g" resources/views/marketplace/marketplace.blade.php
    echo "  -> resources/views/marketplace/marketplace.blade.php updated"
fi

# -------------------------------------------------------
# 5. partision/head.blade.php & add.blade.php
# -------------------------------------------------------
echo "[5/10] Fixing resources/views/partision/ ..."

if [ -f "resources/views/partision/head.blade.php" ]; then
    sed -i "s/:'Max Cart'/': env('APP_NAME', '$APP_NAME')'/g" resources/views/partision/head.blade.php
    sed -i "s/: 'Max Cart'/': env('APP_NAME', '$APP_NAME')'/g" resources/views/partision/head.blade.php
    sed -i "s/content=\"Max Cart\"/content=\"{{ env('APP_NAME', '$APP_NAME') }}\"/g" resources/views/partision/head.blade.php
    sed -i "s/Max Cart,SaaS solution,Multi-workspace/{{ env('APP_NAME') }}, E-Commerce Platform/g" resources/views/partision/head.blade.php
    sed -i "s/Max Cart - Powerful E-Commerce Platform/{{ env('APP_NAME', '$APP_NAME') }} - E-Commerce Platform/g" resources/views/partision/head.blade.php
    echo "  -> resources/views/partision/head.blade.php updated"
fi

if [ -f "resources/views/partision/add.blade.php" ]; then
    sed -i "s/Max Cart/{{ env('APP_NAME', '$APP_NAME') }}/g" resources/views/partision/add.blade.php
    echo "  -> resources/views/partision/add.blade.php updated"
fi

# -------------------------------------------------------
# 6. Theme verification.php files
# -------------------------------------------------------
echo "[6/10] Fixing theme verification files ..."

for THEME in techzonix greentic stylique; do
    if [ -f "themes/$THEME/verification.php" ]; then
        sed -i "s/'Max Cart'/'$APP_NAME'/g" "themes/$THEME/verification.php"
        echo "  -> themes/$THEME/verification.php updated"
    fi
done

# -------------------------------------------------------
# 7. Theme default settings (copyright text)
# -------------------------------------------------------
echo "[7/10] Fixing theme default settings ..."

for THEME in techzonix greentic stylique; do
    SETTINGS_FILE="themes/$THEME/default_data/settings.php"
    if [ -f "$SETTINGS_FILE" ]; then
        # Replace the copyright description - use dynamic approach
        sed -i "s|© 2025 Max Cart. جميع الحقوق محفوظة.|© \" . date('Y') . \" $APP_NAME. جميع الحقوق محفوظة.|g" "$SETTINGS_FILE"
        # Fix any remaining Max Cart references
        sed -i "s/Max Cart/$APP_NAME/g" "$SETTINGS_FILE"
        echo "  -> $SETTINGS_FILE updated"
    fi
done

# -------------------------------------------------------
# 8. Stylique footer
# -------------------------------------------------------
echo "[8/10] Fixing theme footer files ..."

if [ -f "themes/stylique/views/front_end/partison/footer.blade.php" ]; then
    sed -i "s/Max Cart. All rights reserved./{{ env('APP_NAME', '$APP_NAME') }}. All rights reserved./g" themes/stylique/views/front_end/partison/footer.blade.php
    sed -i "s/© 2025 Max Cart/© {{ date('Y') }} {{ env('APP_NAME', '$APP_NAME') }}/g" themes/stylique/views/front_end/partison/footer.blade.php
    echo "  -> themes/stylique/views/front_end/partison/footer.blade.php updated"
fi

# -------------------------------------------------------
# 9. LandingPage layouts (ERPGo + Max Cart)
# -------------------------------------------------------
echo "[9/10] Fixing LandingPage layouts ..."

LP_CUSTOM="packages/workdo/LandingPage/src/Resources/views/layouts/custompage.blade.php"
LP_LANDING="packages/workdo/LandingPage/src/Resources/views/layouts/landingpage.blade.php"

for FILE in "$LP_CUSTOM" "$LP_LANDING"; do
    if [ -f "$FILE" ]; then
        # Remove ERPGo comments entirely
        sed -i '/Copyright.*ERPGo/d' "$FILE"
        sed -i '/Design By ERPGo/d' "$FILE"
        # Replace Max Cart fallback
        sed -i "s/config('app.name', 'Max Cart')/config('app.name', env('APP_NAME', '$APP_NAME'))/g" "$FILE"
        echo "  -> $FILE updated"
    fi
done

# -------------------------------------------------------
# 10. Other scattered references
# -------------------------------------------------------
echo "[10/10] Fixing remaining references ..."

# marketplace/landing.blade.php - WorkDo-Dash
if [ -f "resources/views/marketplace/landing.blade.php" ]; then
    sed -i "s/WorkDo-Dash/$APP_NAME/g" resources/views/marketplace/landing.blade.php
    echo "  -> resources/views/marketplace/landing.blade.php updated"
fi

# CreatePackage.php - WorkDo
if [ -f "app/Console/Commands/CreatePackage.php" ]; then
    sed -i "s/WorkDo/$APP_NAME/g" app/Console/Commands/CreatePackage.php
    echo "  -> app/Console/Commands/CreatePackage.php updated"
fi

# -------------------------------------------------------
# Delete diagnostic file (M-02 / L-03)
# -------------------------------------------------------
echo ""
echo "[SECURITY] Removing diagnostic files..."

if [ -f "public/setup_demo_images.php" ]; then
    rm -f "public/setup_demo_images.php"
    echo "  -> DELETED: public/setup_demo_images.php"
fi

# -------------------------------------------------------
# Summary
# -------------------------------------------------------
echo ""
echo "========================================="
echo "  Branding Removal Complete!"
echo "========================================="
echo ""
echo "IMPORTANT: Update your .env file:"
echo "  APP_NAME=$APP_NAME"
echo ""
echo "Also run: php artisan config:clear"
echo "          php artisan cache:clear"
echo ""
