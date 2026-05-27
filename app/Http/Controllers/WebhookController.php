<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;

class WebhookController extends Controller
{
    /**
     * Validate webhook URL to prevent SSRF attacks
     * SECURITY PATCH H-02
     */
    private function validateWebhookUrl($url)
    {
        // Must be a valid URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Only allow http and https schemes
        $parsed = parse_url($url);
        if (!isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
            return false;
        }

        $host = strtolower($parsed['host'] ?? '');

        // Block localhost and direct private IPs
        if (in_array($host, ['localhost', '::1', '[::1]'])) {
            return false;
        }

        // Block private IP ranges by checking the host
        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}$/', $host)) {
            $parts = explode('.', $host);
            if (
                ($parts[0] == '10') ||
                ($parts[0] == '172' && intval($parts[1]) >= 16 && intval($parts[1]) <= 31) ||
                ($parts[0] == '192' && $parts[1] == '168') ||
                ($parts[0] == '169' && $parts[1] == '254') ||
                ($parts[0] == '127') ||
                ($parts[0] == '0')
            ) {
                return false;
            }
        }

        // Resolve hostname and check resolved IP against private ranges
        $ip = @gethostbyname($host);
        if ($ip !== $host) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                if (
                    ($parts[0] == '10') ||
                    ($parts[0] == '172' && intval($parts[1]) >= 16 && intval($parts[1]) <= 31) ||
                    ($parts[0] == '192' && $parts[1] == '168') ||
                    ($parts[0] == '169' && $parts[1] == '254') ||
                    ($parts[0] == '127') ||
                    ($parts[0] == '0')
                ) {
                    return false;
                }
            }
        }

        // Block cloud metadata endpoints
        $blockedHosts = [
            'metadata.google.internal', 'metadata.goog',
            '169.254.169.254',
        ];
        foreach ($blockedHosts as $blocked) {
            if (strpos($host, $blocked) !== false || strpos($ip, $blocked) !== false) {
                return false;
            }
        }

        // Block dangerous schemes
        if (preg_match('/^(file|ftp|gopher|dict|ldap|ssh2|php|zlib|data|phar):/i', $url)) {
            return false;
        }

        return true;
    }

    public function index()
    {
        //
    }

    public function create()
    {
        return view('webhook.create');
    }

    public function store(Request $request)
    {
        session()->put(['setting_tab' => 'webhook_setting']);
        $store = getStoreById(getCurrentStore());

        $validator = \Validator::make($request->all(), [
            'module' => 'required|unique:webhooks,module,NULL,id,store_id,' . $store->id,
            'method' => 'required|in:GET,POST,PUT,DELETE',
            'webbbook_url' => 'required|url|max:500',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // SECURITY PATCH H-02: SSRF validation
        if (!$this->validateWebhookUrl($request->webbbook_url)) {
            return redirect()->back()->with('error', __('Invalid webhook URL. Private/internal URLs are not allowed.'));
        }

        $webhook            = new Webhook();
        $webhook->module    = $request->module;
        $webhook->url       = $request->webbbook_url;
        $webhook->method    = $request->method;
        $webhook->store_id  = $store->id;
        $webhook->save();

        return redirect()->back()->with('success', __('Webhook setting created successfully'));
    }

    public function show(Webhook $webhook)
    {
        //
    }

    public function edit($id)
    {
        $webhook = Webhook::where('id', $id)->where('store_id', getCurrentStore())->get();
        return view('webhook.edit', compact('webhook'));
    }

    public function update(Request $request, $id)
    {
        session()->put(['setting_tab' => 'webhook_setting']);
        $store = getStoreById(getCurrentStore());

        $validator = \Validator::make($request->all(), [
            'module' => 'required|unique:webhooks,module,' . $id . ',id,store_id,' . $store->id,
            'method' => 'required|in:GET,POST,PUT,DELETE',
            'webbbook_url' => 'required|url|max:500',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // SECURITY PATCH H-02: SSRF validation
        if (!$this->validateWebhookUrl($request->webbbook_url)) {
            return redirect()->back()->with('error', __('Invalid webhook URL. Private/internal URLs are not allowed.'));
        }

        $webhook['module']      =   $request->module;
        $webhook['method']      =   $request->method;
        $webhook['url']         =   $request->webbbook_url;
        $webhook['store_id']    =   $store->id;
        Webhook::where('id', $id)->update($webhook);

        return redirect()->back()->with('success', __('Webhook Setting Succssfully Updated'));
    }

    public function destroy($id)
    {
        session()->put(['setting_tab' => 'webhook_setting']);
        if (auth()->user() && auth()->user()->type == 'admin') {
            // SECURITY PATCH: Add store ownership check (IDOR fix)
            $webhook = Webhook::where('id', $id)->where('store_id', getCurrentStore())->first();
            if ($webhook) {
                $webhook->delete();
                return redirect()->back()->with('success', __('Webhook Setting successfully deleted .'));
            } else {
                return redirect()->back()->with('error', __('Webhook not found or does not belong to your store.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
