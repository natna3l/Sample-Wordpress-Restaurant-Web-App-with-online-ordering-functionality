(function () {
    var $ = jQuery;

    var selectOptions = elementor.modules.controls.Select2.extend({
        onBeforeRender: function () {
            if (this.container && "section" === this.container.type) {
                var widgetObj = elementor.widgetsCache || elementor.config.widgets,
                    optionsToUpdate = {};

                this.container.children.forEach(function (child) {

                    child.view.$childViewContainer.children("[data-widget_type]").each(function (index, widget) {
                        var name = $(widget).data("widget_type").split('.')[0];

                        if ('undefined' !== typeof widgetObj[name]) {
                            optionsToUpdate[".elementor-widget-" + widgetObj[name].widget_type + " .elementor-widget-container"] = widgetObj[name].title;
                        }
                    });
                });

                this.model.set("options", optionsToUpdate);
            }
        },
    });

    elementor.addControlView("premium-select", selectOptions);

    var filterOptions = elementor.modules.controls.Select2.extend({
        isUpdated: false,
        onReady: function () {
            var self = this,
                type = self.options.elementSettingsModel.attributes.post_type_filter;

            if ('post' !== type) {
                var options = (0 === this.model.get('options').length);

                if (options) {
                    self.fetchData(type);
                }
            }

            elementor.channels.editor.on('change', function (view) {
                var changed = view.elementSettingsModel.changed;

                if (undefined !== changed.post_type_filter && 'post' !== changed.post_type_filter && !self.isUpdated) {
                    self.isUpdated = true;
                    self.fetchData(changed.post_type_filter);
                }
            });
        },
        fetchData: function (type) {
            var self = this;
            $.ajax({
                url: PremiumSettings.ajaxurl,
                dataType: 'json',
                type: 'POST',
                data: {
                    nonce: PremiumSettings.nonce,
                    action: 'premium_update_filter',
                    post_type: type
                },
                success: function (res) {
                    self.updateFilterOptions(JSON.parse(res.data));
                    self.isUpdated = false;

                    self.render();
                },
                error: function (err) {
                    console.log(err);
                },
            });
        },
        updateFilterOptions: function (options) {
            this.model.set("options", options);
        },
        onBeforeDestroy: function () {
            if (this.ui.select.data('select2')) {
                this.ui.select.select2('destroy');
            }

            this.$el.remove();
        }
    });

    elementor.addControlView("premium-post-filter", filterOptions);

    var taxOptions = elementor.modules.controls.Select.extend({
        isUpdated: false,
        onReady: function () {
            var self = this,
                type = self.options.elementSettingsModel.attributes.post_type_filter,
                options = (0 === this.model.get('options').length);

            if (options) {
                self.fetchData(type);
            }

            elementor.channels.editor.on('change', function (view) {
                var changed = view.elementSettingsModel.changed;

                if (undefined !== changed.post_type_filter && !self.isUpdated) {
                    self.isUpdated = true;
                    self.fetchData(changed.post_type_filter);
                }
            });
        },
        fetchData: function (type) {
            var self = this;
            $.ajax({
                url: PremiumSettings.ajaxurl,
                dataType: 'json',
                type: 'POST',
                data: {
                    nonce: PremiumSettings.nonce,
                    action: 'premium_update_tax',
                    post_type: type
                },
                success: function (res) {
                    var options = JSON.parse(res.data);
                    self.updateTaxOptions(options);
                    self.isUpdated = false;

                    if (0 !== options.length) {
                        var $tax = Object.keys(options);
                        self.container.settings.setExternalChange({ 'filter_tabs_type': $tax[0] });
                        self.container.render();
                        self.render();
                    }
                },
                error: function (err) {
                    console.log(err);
                },
            });
        },
        updateTaxOptions: function (options) {
            this.model.set("options", options);
        },
    });

    elementor.addControlView("premium-tax-filter", taxOptions);

})(jQuery);