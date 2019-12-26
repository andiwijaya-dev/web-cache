<?php

namespace Andiwijaya\WebCache\Http\Middleware;

use Andiwijaya\WebCache\Facades\WebCache;

class WebCacheMiddlewareDisabled{

  public function handle($request, $next){

    WebCache::dontStore();

    $response = $next($request);

    return $response;

  }

}