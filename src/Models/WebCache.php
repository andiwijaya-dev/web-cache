<?php

namespace Andiwijaya\WebCache\Models;

use Andiwijaya\WebCache\Jobs\WebCacheLoad;
use Andiwijaya\WebCache\Models\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebCache extends Model{

  use SearchableTrait;

  protected $table = 'web_cache';

  protected $fillable = [ 'key', 'tag', 'created_ua', 'created_ip' ];

  protected $primaryKey = 'key';

  public $incrementing = false;

  protected $searchable = [
    'tag'
  ];

}
