### How to install:

1 First add repo to composer.json of magento.

"repositories": [
        {
            "type": "vcs",
            "url": "http://products.git.devoffice.com/magento/shop-by-brand.git"
        }
    ],

2 Run command:

bin/magento cache:disable

composer require templatemonster/shop-by-brand:dev-default

3 Run command:

bin/magento module:enable --clear-static-content TemplateMonster_ShopByBrand

bin/magento setup:upgrade


### How to remove module:

1 Run command:

bin/magento module:disable --clear-static-content TemplateMonster_ShopByBrand

2 Run command:

composer remove templatemonster/shop-by-brand



### How to configure module:

1 Go to Admin Panel:

2 After modification:

Clear magento cache.

Clear browser cache.