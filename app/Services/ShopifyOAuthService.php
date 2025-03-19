<?php

namespace App\Services;

use App\Exceptions\OAuthException;
use App\Models\ShopifyStore;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ShopifyOAuthService extends OAuthService
{

    # oauth/shopify/install?hmac=c17473f25cb00009f67f3060de087fbaebc9efbeef5cb0a36d630a2c190b1344&host=YWRtaW4uc2hvcGlmeS5jb20vc3RvcmUvZGV2b250LWRldmVsb3BtZW50&shop=devont-development.myshopify.com&timestamp=1742080371
    public function redirectToOAuthProvider(Request $request)
    {
        $shop = $request->query('shop');
        if (!isset($shop)) {
            dd('Shop domain is required for installation');
        }

        # generate a state token
        $state = Str::random(40);
        # save it in session for future validation
        Session::put("{$shop}_oauth_state", $state);

        $oauthURL = "https://{$shop}/admin/oauth/authorize?";
        $oauthURL .= "client_id=" . config('app.shopify.client_id');
        $oauthURL .= "&scope=" . config('app.shopify.scopes');
        $oauthURL .= "&redirect_uri=" . config('app.shopify.redirect_uri');
        $oauthURL .= "&state={$state}";

        return redirect()->away($oauthURL);
    }

    public function handleOAuthCallback(Request $request)
    {
        # HMAC is already checked
        $shop = $request->query('shop');
        $host = $request->query('host');
        $state = $request->query('state');
        $authCode = $request->query('code');

        if (!$shop || !$host || !$state || !$authCode) {
            $missing = [];
            if (!$shop) $missing[] = 'shop';
            if (!$host) $missing[] = 'host';
            if (!$state) $missing[] = 'state';
            if (!$authCode) $missing = 'code';
            throw new OAuthException('Some required parameter are missing', $missing);
        }

        $internalState = Session::get("{$shop}_oauth_state");
        if ($internalState !== $state) {
            throw new OAuthException('The state returned doesn\'t match our internal records');
        }

        if (!$this->validateHost($host, $shop)) {
            throw new OAuthException('The host domain returned is invalid');
        }

        $data = $this->getAccessToken($shop, $authCode);
        if (!$data) {
            throw new OAuthException('Failed to get Access-Token from Shopify');
        }

        $accessToken = $data['access_token'];
        $grantedPermissions = $data['scope'];
        $storeData = $this->fetchStoreData($shop, $accessToken);

        if (!$storeData) {
            throw new OAuthException('Failed to fetch data from Shopify Store');
        }

        $res = ShopifyStore::updateOrCreate(
            ['shop_domain' => $storeData['myshopifyDomain']],
            [
                'shop_name' => $storeData['name'],
                'shop_url' => $storeData['primaryDomain']['url'],
                'shop_domain' => $storeData['myshopifyDomain'],
                'shop_currency' => $storeData['currencyCode'],
                'shop_country' => $storeData['billingAddress']['country'],
                'shop_owner_email' => $storeData['contactEmail'],
                'access_token' => $accessToken,
                'is_active' => true,
                'installed_at' => now(),
                'uninstalled_at' => null
            ]
        );

        $this->createUninstallWebhook($shop, $accessToken);
    }

    private function validateHost(string $host, string $domain)
    {
        $incomingHost = base64_decode($host, true);
        if (!$host) {
            return false;
        }
        $store = str_replace('.myshopify.com', '', $domain);
        return $incomingHost === "admin.shopify.com/store/{$store}";
    }

    private function getAccessToken(string $shop, string $authCode)
    {
        $response = Http::asForm()->post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => config('app.shopify.client_id'),
            'client_secret' => config('app.shopify.client_secret'),
            'code' => $authCode
        ]);

        if ($response->failed()) {
            return false;
        }
        return $response->json();
    }

    private function fetchStoreData(string $shop, string $accessToken)
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop}/admin/api/2025-01/graphql.json", [
            'query' => '{
                shop {
                    name
                    currencyCode
                    billingAddress {
                        country
                    }
                    email
                    contactEmail
                    myshopifyDomain
                    primaryDomain {
                        url
                    }
                }
            }'
        ]);

        if ($response->failed()) {
            return false;
        }
        $data = $response->json();

        if (isset($data['errors'])) {
            return false;
        }
        return $data['data']['shop'];
    }

    private function createUninstallWebhook(string $shop, string $accessToken)
    {
        $uninstallURL = config('app.url') . "/shopify/webhooks/app-uninstall-event";
        echo $uninstallURL;
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop}/admin/api/2025-01/graphql.json", [
            'query' => 'mutation CreateWebhook {
                webhookSubscriptionCreate(
                    topic: APP_UNINSTALLED,
                    webhookSubscription: {
                        format: JSON,
                        callbackUrl: "' . $uninstallURL . '" 
                    }
                ) {
                    webhookSubscription {
                        id
                        topic
                        format
                        endpoint {
                            __typename
                            ... on WebhookHttpEndpoint {
                                callbackUrl
                            }
                        }
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }'
        ]);

        echo "<pre>" . print_r($response->json(), 1) . "</pre>";
        die();
        redirect()->route('/dashboard');
    }
}
