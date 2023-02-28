## Help

All `editor.sccs` files are bundled into a single CSS file using the entry point name for the file. The same is done for all `style.scss` files except the output files uses a format of `style-[NAME].css` as the file name.

If no `editor.scss` or `style.scss` files are included then the corresponding output file will not be generated.

A single PHP file is also generated per entry and contains the WordPress dependencies for the compiled code and a build unique version. This file uses the format of `[NAME].asset.php` and would look something like the following:

```php
<?php return array('dependencies' => array('lodash', 'wp-api-fetch', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'wp-url'), 'version' => 'b08a8dbb96ff09c305ab');
```

### Example

For an entry with the name of `blocks` the following output will be generated:

```text
~/assets/
    |- blocks.asset.php
    |- blocks.js
    |- blocks.css
    |- style-blocks.css
```

### Note

At the moment there are no frontend styles for the FooGallery blocks. To output a separate `style-blocks.css` the component requiring the styles should import a local `style.scss` file.