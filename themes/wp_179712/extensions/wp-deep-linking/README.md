# wp-deep-linking
JavaScript extention which provides Deep Linking into Wordpress theme

Supose, is cloned into __extensions/__ directory. Then inside your __functions.php__:
```php
locate_template( 'extensions/wp-deep-linking/main.php', true );
```
And inside your theme's _javascript_:
```js
deepLinking.init({
      menuSelector : 'your_menu_selector'
});
```
