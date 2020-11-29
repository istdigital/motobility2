UPDATE `cms_page` SET `layout_update_xml` = '<referenceContainer name=\"before.body.end\">\r\n    <block class=\"Magento\\Contact\\Block\\ContactForm\" name=\"contact.popup\" template=\"Magento_Contact::popup.phtml\" cacheable=\"true\">\r\n        <block class=\"Magento\\ReCaptchaUi\\Block\\ReCaptcha\" name=\"msp-recaptcha-contact\" after=\"-\" template=\"Magento_ReCaptchaFrontendUi::recaptcha.phtml\" ifconfig=\"recaptcha_frontend/type_for/contact\">\r\n            <arguments>\r\n                <argument name=\"recaptcha_for\" xsi:type=\"string\">contact</argument>\r\n                <argument name=\"jsLayout\" xsi:type=\"array\">\r\n                    <item name=\"components\" xsi:type=\"array\">\r\n                        <item name=\"recaptcha\" xsi:type=\"array\">\r\n                            <item name=\"component\" xsi:type=\"string\">Magento_ReCaptchaFrontendUi/js/reCaptcha</item>\r\n                        </item>\r\n                    </item>\r\n                </argument>\r\n            </arguments>\r\n        </block>\r\n    </block>\r\n</referenceContainer>\r\n<referenceContainer name=\"main.content\">\r\n    <block class=\"Magento\\Cms\\Block\\Block\" name=\"whyus.page.bottom\">\r\n        <arguments>\r\n            <argument name=\"block_id\" xsi:type=\"string\">whyus_page_bottom</argument>\r\n        </arguments>\r\n    </block>\r\n</referenceContainer>\r\n<referenceContainer name=\"sidebar.additional\">\r\n    <block class=\"Zemez\\CmsHierarchy\\Block\\Links\" before=\"-\" name=\"sidebar.links\">\r\n        <arguments>\r\n            <argument name=\"default\" xsi:type=\"array\">\r\n                <item name=\"whyus\" xsi:type=\"array\">\r\n                    <item name=\"title\" xsi:type=\"string\">Why us?</item>\r\n                    <item name=\"url\" xsi:type=\"string\">why-us</item>\r\n                </item>\r\n                <item name=\"testimonials\" xsi:type=\"array\">\r\n                    <item name=\"title\" xsi:type=\"string\">Testimonials</item>\r\n                    <item name=\"url\" xsi:type=\"string\">testimonials</item>\r\n                </item>\r\n            </argument>\r\n        </arguments>\r\n    </block>\r\n    <referenceBlock name=\"catalog.compare.sidebar\" remove=\"true\" />\r\n    <referenceBlock name=\"wishlist_sidebar\" remove=\"true\" />\r\n</referenceContainer>' WHERE  `page_id` = 20;


UPDATE `cms_page` SET `layout_update_xml` = '<referenceContainer name=\"before.body.end\">\r\n    <block class=\"Magento\\Contact\\Block\\ContactForm\" name=\"contact.popup\" template=\"Magento_Contact::popup.phtml\" cacheable=\"true\">\r\n        <block class=\"Magento\\ReCaptchaUi\\Block\\ReCaptcha\" name=\"msp-recaptcha-contact\" after=\"-\" template=\"Magento_ReCaptchaFrontendUi::recaptcha.phtml\" ifconfig=\"recaptcha_frontend/type_for/contact\">\r\n            <arguments>\r\n                <argument name=\"recaptcha_for\" xsi:type=\"string\">contact</argument>\r\n                <argument name=\"jsLayout\" xsi:type=\"array\">\r\n                    <item name=\"components\" xsi:type=\"array\">\r\n                        <item name=\"recaptcha\" xsi:type=\"array\">\r\n                            <item name=\"component\" xsi:type=\"string\">Magento_ReCaptchaFrontendUi/js/reCaptcha</item>\r\n                        </item>\r\n                    </item>\r\n                </argument>\r\n            </arguments>\r\n        </block>\r\n    </block>\r\n</referenceContainer>\r\n<referenceContainer name=\"sidebar.additional\">\r\n    <block class=\"Zemez\\CmsHierarchy\\Block\\Links\" before=\"-\" name=\"sidebar.links\">\r\n        <arguments>\r\n            <argument name=\"default\" xsi:type=\"array\">\r\n                <item name=\"privacy\" xsi:type=\"array\">\r\n                    <item name=\"title\" xsi:type=\"string\">Privacy Policy</item>\r\n                    <item name=\"url\" xsi:type=\"string\">privacy-policy</item>\r\n                </item>\r\n                <item name=\"terms\" xsi:type=\"array\">\r\n                    <item name=\"title\" xsi:type=\"string\">Terms &amp; Conditions</item>\r\n                    <item name=\"url\" xsi:type=\"string\">terms-conditions</item>\r\n                </item>\r\n            </argument>\r\n        </arguments>\r\n    </block>\r\n    <block class=\"Magento\\Cms\\Block\\Block\" name=\"sidebar.contact.popup\">\r\n        <arguments>\r\n            <argument name=\"block_id\" xsi:type=\"string\">sidebar_contact_popup</argument>\r\n        </arguments>\r\n    </block>\r\n    <referenceBlock name=\"catalog.compare.sidebar\" remove=\"true\" />\r\n    <referenceBlock name=\"wishlist_sidebar\" remove=\"true\" />\r\n</referenceContainer>' WHERE  `page_id` = 24;




UPDATE `cms_page` SET `layout_update_xml` = '<referenceContainer name=\"before.body.end\">\r\n    <block class=\"Magento\\Contact\\Block\\ContactForm\" name=\"contact.popup\" template=\"Magento_Contact::popup.phtml\" cacheable=\"true\">\r\n        <block class=\"Magento\\ReCaptchaUi\\Block\\ReCaptcha\" name=\"msp-recaptcha-contact\" after=\"-\" template=\"Magento_ReCaptchaFrontendUi::recaptcha.phtml\" ifconfig=\"recaptcha_frontend/type_for/contact\">\r\n            <arguments>\r\n                <argument name=\"recaptcha_for\" xsi:type=\"string\">contact</argument>\r\n                <argument name=\"jsLayout\" xsi:type=\"array\">\r\n                    <item name=\"components\" xsi:type=\"array\">\r\n                        <item name=\"recaptcha\" xsi:type=\"array\">\r\n                            <item name=\"component\" xsi:type=\"string\">Magento_ReCaptchaFrontendUi/js/reCaptcha</item>\r\n                        </item>\r\n                    </item>\r\n                </argument>\r\n            </arguments>\r\n        </block>\r\n    </block>\r\n</referenceContainer>\r\n<referenceContainer name=\"sidebar.additional\">\r\n    <block class=\"Zemez\\CmsHierarchy\\Block\\Links\" before=\"-\" name=\"sidebar.links\">\r\n        <arguments>\r\n            <argument name=\"default\" xsi:type=\"array\">\r\n                <item name=\"privacy\" xsi:type=\"array\">\r\n                    <item name=\"title\" xsi:type=\"string\">Privacy Policy</item>\r\n                    <item name=\"url\" xsi:type=\"string\">privacy-policy</item>\r\n                </item>\r\n                <item name=\"terms\" xsi:type=\"array\">\r\n                    <item name=\"title\" xsi:type=\"string\">Terms &amp; Conditions</item>\r\n                    <item name=\"url\" xsi:type=\"string\">terms-conditions</item>\r\n                </item>\r\n            </argument>\r\n        </arguments>\r\n    </block>\r\n    <block class=\"Magento\\Cms\\Block\\Block\" name=\"sidebar.contact.popup\">\r\n        <arguments>\r\n            <argument name=\"block_id\" xsi:type=\"string\">sidebar_contact_popup</argument>\r\n        </arguments>\r\n    </block>\r\n    <referenceBlock name=\"catalog.compare.sidebar\" remove=\"true\" />\r\n    <referenceBlock name=\"wishlist_sidebar\" remove=\"true\" />\r\n</referenceContainer>' WHERE  `page_id` = 23;


UPDATE `core_config_data` SET `value` = 1 WHERE `path` = 'layered_navigation/general/ajax_enable';
UPDATE `core_config_data` SET `value` = 1 WHERE `path` = 'shopbybrand/brand/show_description';


INSERT INTO `core_config_data` (`scope`, `scope_id`, `path`, `value`, `updated_at`) VALUES
('default',	0,	'layered_navigation/general/infinite_scroll',	'1',	'2020-11-15 04:10:03');


INSERT INTO `core_config_data` (`scope`, `scope_id`, `path`, `value`, `updated_at`) VALUES ('default',	0,	'customer/address/street_lines',	'2',	'2020-11-15 05:11:44');


UPDATE `customer_eav_attribute` SET `sort_order` =  117 WHERE `attribute_id` = 30;
UPDATE `customer_eav_attribute` SET `sort_order` =  115 WHERE `attribute_id` IN (31, 32);