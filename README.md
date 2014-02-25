xhtml-clsss-to-style
====================

Convert CSS rules into local styles (HTML)

Example
=======

```php



$dom = new \DOMDocument("1.0", "UTF-8");
$dom->loadXML("myhtml.html");

$api = new XhtmlClassToStyle();
$api->applyCss($dom, file_get_contents("style.css"));

echo $dom->saveXML(); // all classes of style.css will be transformed into local styles
```
