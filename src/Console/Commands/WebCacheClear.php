<?php

namespace Andiwijaya\WebCache\Console\Commands;

use Andiwijaya\WebCache\Facades\WebCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class WebCacheClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'web-cache:clear {tag?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      if(!Schema::hasTable('web_cache'))
        $this->warn("Table web_cache not exists, clear by tag feature is not available.");

      $count = strlen($this->argument('tag')) > 0 ?
        WebCache::clear($this->argument('tag')) :
        WebCache::clearAll();

      $count >= 0 ? $this->info("Urls cleared: {$count}") :
        $this->info("Cache cleared");

    }
}
