# myTpl.php
Simple template engine written in PHP.

# Usage

```php
<?php
require 'myTpl.php';
using myuce\myTpl;
$tpl = new('tplDir','cacheDir');
$tpl->set('name','John Doe');
$tpl->load('test');
?>
```

```html
Hello! My name is {%name}.
```

And the result should look like this:

```html
Hello! My name is John Doe.
```
