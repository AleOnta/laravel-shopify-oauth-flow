<?php

namespace App\Services;

use Illuminate\Http\Request;

abstract class OAuthService
{
    abstract public function redirectToOAuthProvider(Request $request);
    abstract public function handleOAuthCallback(Request $request);
}
