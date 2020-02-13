Description
===========
Makes it possible for Magento Wishlist module to work asynchronously, i.e. without page reloading.

Install
=======

1. Add repo to composer.json file:
```json
{
    "repositories": [
            {
                "type": "composer",
                "url": "https://repo.magento.com/"
            },
            {
                "type": "vcs",
                "url": "http://products.git.devoffice.com/magento/magento2-ajaxwishlist.git"
            }
        ]
}
```

2. Add the module to composer:
```bash
composer require templatemonster/ajaxwishlist:dev-master
```

3. Enable the module:
```bash
bin/magento module:enable --clear-static-content TemplateMonster_AjaxWishlist
bin/magento setup:upgrade
```

Configure
=========

Please navigate to the **Stores -> Settings -> Configuration -> Template Monster -> Ajax Wishlist** in order to configure the module.

Uninstall
=========

1. Disable the module:
```bash
bin/magento module:disable --clear-static-content TemplateMonster_AjaxWishlist
```

2. Remove the module from composer:
```bash
composer remove templatemonster/ajaxwishlist
```