# pFileCache
### Used

```
require 'FileCache.php';
use pFileCache\FileCache;

$cache = new FileCache();

$cache->set('Foo', 'This is Value', 3600);

$cache->get('Foo');

$cache->delete('Foo');

```
