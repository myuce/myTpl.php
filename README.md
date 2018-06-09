# myTpl.php
Simple template engine with limited logic written in PHP. A version written in Javascript can be found [here](http://github.com/myuce/myTpl.js).

# Usage

```php
<?php
require 'myTpl.php';
use myuce\myTpl;
$tpl = new('tplDir','cacheDir');
$tpl->set('name','John Doe');
$tpl->load('test');
?>
```

# Template file

```html
Hello! My name is {%name}.
```

And the result should look like this:

```html
Hello! My name is John Doe.
```

The test files can be checked for futher examples.
