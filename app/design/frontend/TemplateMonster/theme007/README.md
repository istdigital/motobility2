## Magento 2 Frontend Framework

### Установка через Composer

В файл composer.json в корне Magento добавляете:

В раздел "repositories":
{
    "type": "vcs",
    "url": "http://products.git.devoffice.com/magento/magento2_child.git"
}

В раздел "require":
"templatemonster/theme-frontend-child": "dev-master"

Где :
* "dev-master" - название ветки с префиксом dev-
* "templatemonster/child" - название пакета в файле composer.json в корне темы