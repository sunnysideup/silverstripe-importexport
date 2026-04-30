<?php

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Config\Config;
use BurnBright\ImportExport\BulkLoader\Extensions\ImportAdminExtension;

ModelAdmin::add_extension(ImportAdminExtension::class);
$remove = Config::inst()->get('ModelAdmin','removelegacyimporters');
if($remove === "scaffolded"){
	Config::inst()->update("ModelAdmin", 'model_importers', []);
}

//cache mappings forever
// SS_Cache::set_cache_lifetime('gridfieldimporter', null);
