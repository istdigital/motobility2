Magento2 Social Sharing


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
                "url": "http://products.git.devoffice.com/magento/magento2-social-sharing.git"
            }
        ]
}
```

2. Add the module to composer:
```bash
composer require templatemonster/magento2-social-sharing:dev-master
```

3. Enable the module:
```bash
bin/magento module:enable --clear-static-content TemplateMonster_SocialSharing
bin/magento setup:upgrade
```

Configure
=========

Please navigate to the **Stores -> Settings -> Configuration -> Template Monster -> Social Sharing** in order to configure the module.

Uninstall
=========

1. Disable the module:
```bash
bin/magento module:disable --clear-static-content TemplateMonster_SocialSharing
```

2. Remove the module from composer:
```bash
composer remove templatemonster/magento2-social-sharing
```