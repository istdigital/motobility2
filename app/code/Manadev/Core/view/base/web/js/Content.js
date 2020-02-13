define(['module', 'Manadev_Core/js/functions/class', 'Manadev_Core/js/Data', 'jquery', 'mage/apply/main'],
function(module, class_, Data, $, main) {
    return class_(module.id, Data, {
        isPrevented: function(action) {
            return $(document).data('mana-prevent-replacing-content') ||
                $(document).data('mana-prevent-replacing-' + action + '-content');
        },
        replace: function(replacements) {
            $(document).data('mana-replacing-content', true);

            try {
                $(document).trigger('mana-before-replacing-content', [this.fetchContainers(replacements)]);

                $.each(replacements, function (selector, newContent) {
                    $(selector).replaceWith(newContent);
                });

                var $containers = this.fetchContainers(replacements);
                this.addMageInitAttributes($containers);
                main.apply();
                $(document).trigger('mana-after-content-replaced', [$containers]);
            }
            finally {
                setTimeout(function() {
                    $(document).removeData('mana-replacing-content');
                }, 10000);
            }
        },
        fetchContainers: function (replacements) {
            var combinedSelector = '';
            $.each(replacements, function (selector) {
                if (combinedSelector) {
                    combinedSelector += ',';
                }
                combinedSelector += selector;
            });

            return $(combinedSelector);
        },
        addMageInitAttributes: function($containers) {
            if (!this.scripts) {
                this.initScripts();

            }

            $containers.each(function(index, element) {
                this.mergeScripts(this.findScriptsIn(element));
            }.bind(this));

            for (var selector in this.scripts) {
                if (!this.scripts.hasOwnProperty(selector)) continue;

                if (selector == '*') {
                    continue;
                }

                $containers.find(selector).each(function (index, element) {
                    var components = JSON.parse(element.getAttribute('data-mage-init') || '{}');
                    for (var component in this.scripts[selector]) {
                        if (!this.scripts[selector].hasOwnProperty(component)) continue;

                        if (components[component]) {
                            continue;
                        }

                        components[component] = this.scripts[selector][component];
                    }

                    if (Object.keys(components).length) {
                        element.setAttribute('data-mage-init', JSON.stringify(components));
                    }
                }.bind(this));

            }
        },
        initScripts: function() {
            this.scripts = {};

            if (!window.manaScripts) {
                return;
            }

            this.mergeScripts(window.manaScripts);

            window.manaScripts = undefined;
            try {
                delete window.manaScripts;
            }
            catch (e) {
            }
        },
        mergeScripts: function(scripts) {
            scripts.forEach(function(components) {
                for (var selector in components) {
                    if (!components.hasOwnProperty(selector)) continue;

                    if (!this.scripts[selector]) {
                        this.scripts[selector] = {};
                    }

                    for (var component in components[selector]) {
                        if (!components[selector].hasOwnProperty(component)) continue;

                        if (!this.scripts[selector][component]) {
                            this.scripts[selector][component] = components[selector][component];
                        }
                    }
                }
            }.bind(this));
        },
        findScriptsIn: function(element) {
            return Array.prototype.slice.call(element.getElementsByTagName('script'))
                .filter(function (element) {
                    return element.getAttribute('type') == 'text/x-magento-init';
                })
                .map(function (element) {
                    return JSON.parse(element.textContent);
                });
        }
    });
});