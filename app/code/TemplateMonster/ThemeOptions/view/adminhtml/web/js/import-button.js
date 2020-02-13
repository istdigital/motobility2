define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('tm.importButton', {

        options: {
            importFileSelector: '#theme_options_general_import_file',
            importFileName: 'import_file',
            uploadFormSelector: '#config-edit-form',
            importProcessorUrl: null
        },

        _create: function() {
            _.bindAll(this, '_showUploadDialog', '_submitUploadForm');
            this._bind();
        },

        _bind: function () {
            this.element.click(this._showUploadDialog);
            $(this.options.importFileSelector).change(this._submitUploadForm)
        },

        _showUploadDialog: function() {
            var selector = this.options.importFileSelector,
                name = this.options.importFileName;

            $(selector).attr('name', name).trigger('click');
        },

        _submitUploadForm: function() {
            var form = this.options.uploadFormSelector,
                importFile = this.options.importFileSelector,
                url = this.options.importProcessorUrl;

            $(form).find(':file').not(importFile).remove();
            $(form).attr('action', url).submit();

        }

    });

    return $.tm.importButton;
});