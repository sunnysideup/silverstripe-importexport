<?php

use SilverStripe\Admin\ModelAdmin;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use BurnBright\ImportExport\BulkLoader\Extensions\ImportAdminExtension;

ModelAdmin::add_extension(ImportAdminExtension::class);
$remove = Config::inst()->get('ModelAdmin','removelegacyimporters');
if($remove === "scaffolded"){
	Config::inst()->update("ModelAdmin", 'model_importers', array());
}
//cache mappings forever
// SS_Cache::set_cache_lifetime('gridfieldimporter', null);