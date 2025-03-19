<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OAuthService;

class OAuthController extends Controller
{
    private OAuthService $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function redirectToProvider(Request $request, string $platform)
    {
        return match ($platform) {
            'shopify' => $this->oauthService->redirectToOAuthProvider($request),
            default => redirect()->route('app.welcome')
        };
    }

    public function handleOAuthCallback(Request $request, string $platform)
    {
        return match ($platform) {
            'shopify' => $this->oauthService->handleOAuthCallback($request),
            default => redirect()->route('app.welcome')
        };
    }
}
