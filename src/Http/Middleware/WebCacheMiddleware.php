<?php

namespace Andiwijaya\WebCache\Http\Middleware;

use Andiwijaya\WebCache\Facades\WebCache;

class WebCacheMiddleware{

  public function handle($request, $next){

    $response = $next($request);

    WebCache::store($request, $response);

    return $response;

  }

}