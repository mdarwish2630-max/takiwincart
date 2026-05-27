<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Facades\ModuleFacade as Module;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class XSS
{
    use \RachidLaasri\LaravelInstaller\Helpers\MigrationsHelper;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user() && Auth::user()->type == 'super admin') {
            $ranMigrations = DB::table('migrations')->pluck('migration');
            $modules = Module::allModules();
            $migrationFiles = collect(File::glob(database_path('migrations/*.php')))->map(fn ($path) => File::name($path));
            foreach ($modules as $module) {
                $packageMigration = ["packages/workdo/{$module->name}/src/Database/Migrations", "packages/workdo/{$module->name}/src/database/migrations"];
                foreach ($packageMigration as $dir) {
                    if (File::exists($dir)) {
                        $files = collect(File::glob("{$dir}/*.php"))->map(fn ($path) => File::name($path));
                        $migrationFiles = $migrationFiles->merge($files);
                    }
                }
            }
            $pendingMigrations = $migrationFiles->diff($ranMigrations);
            if (count($pendingMigrations) > 0) {
// PATCHED:                 // return redirect()->route('LaravelUpdater::welcome'); // PATCHED: disabled
            }
        }

        // === SECURITY PATCH H-01: Enhanced XSS Protection ===
        $input = $request->all();
        $this->sanitizeInput($input);
        $request->merge($input);

        return $next($request);
    }

    /**
     * Recursively sanitize all input values
     */
    private function sanitizeInput(&$input)
    {
        array_walk_recursive($input, function (&$value, $key) {
            if (!is_string($value)) {
                return;
            }

            // Skip JSON strings - they will be validated at usage point
            if ($this->looksLikeJson($value)) {
                $value = $this->sanitizeJsonValue($value);
                return;
            }

            // Step 1: Normalize encodings (prevent double-encoding bypass)
            $value = $this->normalizeEncoding($value);

            // Step 2: Remove null bytes
            $value = str_replace(chr(0), '', $value);

            // Step 3: Strip dangerous HTML/JS patterns
            $value = $this->stripDangerousPatterns($value);

            // Step 4: Final encoding for safe output
            $value = $this->encodeOutput($value);
        });
    }

    /**
     * Normalize character encodings to prevent bypass via alternate encodings
     */
    private function normalizeEncoding($value)
    {
        // Decode HTML entities first (catches java&#115;cript: etc.)
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remove invisible/control characters used for obfuscation
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        return $value;
    }

    /**
     * Strip dangerous HTML, JavaScript, and event handler patterns
     */
    private function stripDangerousPatterns($value)
    {
        $dangerousPatterns = [
            // Script tags and content
            '/<\s*script[^>]*>.*?<\s*\/\s*script\s*>/is',
            '/<\s*script\b[^>]*>/i',
            '/<\s*\/\s*script\s*>/i',
            // Dangerous embed/object tags
            '/<\s*iframe[^>]*>.*?<\s*\/\s*iframe\s*>/is',
            '/<\s*iframe\b/i',
            '/<\s*object[^>]*>.*?<\s*\/\s*object\s*>/is',
            '/<\s*object\b/i',
            '/<\s*embed\b/i',
            '/<\s*applet\b/i',
            '/<\s*meta\b[^>]*http-equiv[^>]*>/i',
            '/<\s*link\b[^>]*rel[^>]*stylesheet/i',
            '/<\s*base\b/i',
            '/<\s*form\b/i',
            '/<\s*input\b/i',
            // JavaScript protocol
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/data\s*:\s*text\/html/i',
            // Event handlers (on* attributes)
            '/\bon\w+\s*=\s*["\']?[^"\']*["\']?/i',
            '/\bon\w+\s*=\s*\S+/i',
            // CSS expression
            '/expression\s*\(/i',
            '/url\s*\(\s*["\']?\s*javascript/i',
            '/-moz-binding\s*:/i',
            '/@import/i',
            // SVG-based XSS
            '/<\s*svg[^>]*>.*?<\s*\/\s*svg\s*>/is',
            '/<\s*svg\b/i',
            '/<\s*math[^>]*>.*?<\s*\/\s*math\s*>/is',
            '/<\s*math\b/i',
            // XML-based attacks
            '/<\s*\?xml/i',
            '/<!\[CDATA\[/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }

        // Also strip remaining HTML tags entirely (safe fallback)
        $value = strip_tags($value);

        return $value;
    }

    /**
     * Encode special characters for safe HTML output
     */
    private function encodeOutput($value)
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Check if a string looks like JSON
     */
    private function looksLikeJson($value)
    {
        $trimmed = trim($value);
        if (strlen($trimmed) < 2) {
            return false;
        }
        $first = $trimmed[0];
        $last = substr($trimmed, -1);
        return ($first === '{' || $first === '[') && ($last === '}' || $last === ']');
    }

    /**
     * Sanitize JSON string values while preserving structure
     */
    private function sanitizeJsonValue($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        $this->sanitizeInput($decoded);
        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }
}
