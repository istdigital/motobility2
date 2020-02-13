### How to install:

1 First add repo to composer.json of magento.

"repositories": [
        {
            "type": "vcs",
            "url": "http://products.git.devoffice.com/magento/film-slider.git"
        }
    ],

2 Run command:

bin/magento cache:disable

composer require templatemonster/film-slider:dev-default

3 Run command:

bin/magento module:enable --clear-static-content TemplateMonster_FilmSlider

bin/magento setup:upgrade


### How to remove module:

1 Run command:

bin/magento module:disable --clear-static-content TemplateMonster_FilmSlider

2 Run command:

composer remove templatemonster/film-slider

### How to configure module:

1 Go to Admin Panel:

2 After modification:

Clear magento cache.

Clear browser cache.