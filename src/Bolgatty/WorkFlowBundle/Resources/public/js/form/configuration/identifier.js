'use strict';

/**
 * Identifier field to be added in a creation form
 */
define([
    'underscore',
    'pim/form/common/creation/field',
    'pim/user-context',
    'pim/i18n',
    'oro/translator',
    'pim/fetcher-registry',
    'pim/template/product-create-error',
], function(_, FieldForm, UserContext, i18n, __, FetcherRegistry, errorTemplate) {

    return FieldForm.extend({
        errorTemplate: _.template(errorTemplate),
        events: {
            'change input': function () {
                this.errors = [];
                var fieldVal = ($(event.target).prop('readonly') || _.isUndefined(this.getFormData()[this.identifier])) ? $('#creation_' + this.identifier).val() : this.getFormData()[this.identifier];
                this.setData({ [this.identifier] : fieldVal });
                this.getRoot().render();
            }
        },
        
        render: function() {
            var uniqueSku = FetcherRegistry.getFetcher('unique-sku').search({cache:false});
            var attribtes = FetcherRegistry.getFetcher('attribute').getIdentifierAttribute();
            var self = this;

            Promise.all([uniqueSku, attribtes]).then((values) => {
                var sku = values[0].sku;
                var identifier = values[1];

                self.$el.html(self.template({
                    identifier: self.identifier,
                    label: i18n.getLabel(identifier.labels, UserContext.get('catalogLocale'), identifier.code),
                    requiredLabel: __('pim_common.required_label'),
                    errors: self.getRoot().validationErrors,
                    value: _.isNull(sku) ? self.getFormData()[self.identifier] : sku,
                }));

                if(!_.isNull(sku)) {
                    self.setData({[self.identifier] : sku });
                    $('#creation_' + self.identifier).prop('readonly', true);
                }

                self.delegateEvents();
                return self;
            }).catch(function (error) {
                self.$el.html(self.errorTemplate({message: __('pim_enrich.entity.product.flash.create.fail')}));
            });
        }
    });
});
