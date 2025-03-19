<?php

namespace App\Http\Controllers;

use App\Models\ShopifyStore;
use Illuminate\Http\Request;

class AppLaunchController extends Controller
{
    # DOCS
    public function launch(Request $request)
    {
        echo "<h2>URI: " . $request->url() . "</h2>";
        echo "<h2>FULL URI: " . $request->fullUrl() . "</h2>";
        echo "<h2>Parameters: </h2>";
        echo "<pre>" . print_r($request->query(), 1) . "</pre>";
        die();
        $platform = $this->detectPlatform($request);
        if (!$platform) {
            return redirect()->route('app.welcome');
        }
        # check if the app is installed
        $storeData = !$this->isInstalled($request, $platform);
        if ($storeData) {
            return $this->triggerInstallation($request, $platform);
        }
        # allow dashboard
        return redirect()->route('app.dashboard');
    }

    # DOCS
    private function detectPlatform(Request $request)
    {
        # 1) Shopify
        $shop = $request->query('shop');
        if (!empty($shop) || str_contains($shop, 'myshopify.com')) {
            return 'shopify';
        }

        # 2) Others
        # ....
        # ....

        # direct client request
        return false;
    }

    # DOCS
    private function isInstalled(Request $request, string $platform)
    {
        $data = match ($platform) {
            'shopify' => ShopifyStore::where([
                ['shop_domain', $request->query('shop')],
                ['is_active', true]
            ])->first(),
            # 'others' => Store::where...
            default => false
        };
        return $data;
    }

    # DOCS
    private function triggerInstallation(Request $request, string $platform)
    {
        $url = '/oauth';
        switch ($platform) {
            case 'shopify':
                $url .= '/shopify/install?' . http_build_query($request->query());
                break;

            case 'others':
                # define url
                break;

            default:
                $url = '/welcome';
        }
        return redirect($url);
    }
}
