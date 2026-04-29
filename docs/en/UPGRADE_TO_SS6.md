# Upgrade to Silverstripe CMS 6

## Dependencies

⚠️ **Update composer requirements to Silverstripe 6**
- Change `silverstripe/cms` and `silverstripe/framework` from `^4 || ^5` to `^6.0`
- Update `goodby/csv` from `1.2.x` to `^1.2.0`

## Namespace Changes

⚠️ **Replace deprecated ORM/View namespaces with Model namespaces**
- `SilverStripe\ORM\ArrayList` → `SilverStripe\Model\List\ArrayList`
- `SilverStripe\View\ArrayData` → `SilverStripe\Model\ArrayData`
- `SilverStripe\View\ViewableData` → `SilverStripe\Model\ModelData`

Affected files:
- `code/CSVFieldMapper.php`
- `code/CSVPreviewer.php`
- `code/gridfield/GridFieldImporter.php`
- `code/gridfield/GridFieldImporter_Request.php`

## Exception Handling

⚠️ **Move ValidationException to Core namespace**
- `SilverStripe\ORM\ValidationException` → `SilverStripe\Core\Validation\ValidationException`

Affected files:
- `code/bulkloader/BetterBulkLoader.php`

## Type Declarations

**Add return type declarations to overridden methods**
- `CSVPreviewer::forTemplate()` now returns `string`
- Add `#[Override]` attribute to all methods overriding parent/interface methods

Affected methods:
- `CSVPreviewer::forTemplate()`
- `BetterBulkLoader::load()`
- `CsvBetterBulkLoader::processAll()`
- `ListBulkLoader::getDataList()`
- `ListBulkLoader::processAll()`
- `ListBulkLoader::deleteExistingRecords()`
- `GridFieldImporter_Request::Link()`
- `GridFieldImporter_Request::getBackURL()`

## Test Changes

🔍 **ArrayList import in tests requires attention**
- Test file shows a complex replacement note for `ArrayList`
- May need to verify the correct import path: check if using `use ArrayList;` (global) or `use SilverStripe\Model\List\ArrayList;`

Affected files:
- `tests/BulkLoaderRelationTest.php`
