/**
 * Generic field to be added in a creation form
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/fetcher-registry',
    'pim/template/form/creation/field'
], function($, _, __, BaseForm, FetcherRegistry, template) {

    return BaseForm.extend({
        template: _.template(template),
        dialog: null,
        events: {
            'change input': 'updateModel'
        },

        /**
     * {@inheritdoc}
     */
        initialize: function(config) {
            this.config = config.config;
            this.identifier = this.config.identifier || 'code';

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
     * Model update callback
     */
        updateModel: function(event) {
            var fieldVal = ($(event.target).prop('readonly') || _.isUndefined(this.getFormData()[this.identifier])) ? $('#creation_' + this.identifier).val() : this.getFormData()[this.identifier];
            this.getFormModel().set(this.identifier, fieldVal || '');
        },

        /**
     * {@inheritdoc}
     */
        render: function() {
            if (!this.configured)
                this;
            var uniqueSku = FetcherRegistry.getFetcher('unique-sku').search({cache:false});
            const errors = this.getRoot().validationErrors || [];

            var self = this;
            Promise.all([uniqueSku]).then((values) => {

                var sku = values[0].sku;
                self.$el.html(self.template({
                    identifier: self.identifier,
                    label: __(self.config.label),
                    requiredLabel: __('pim_common.required_label'),
                    errors: errors.filter(error => {
                        const id = self.identifier;
                        const {path, attribute} = error;

                        return id === path || id === attribute;
                    }),
                    value: _.isNull(sku) ? this.getFormData()[this.identifier] : sku,
                }));

                if(!_.isNull(sku)) {
                    self.setData({[self.identifier] : sku });
                    $('#creation_' + self.identifier).prop('readonly', true);
                }
                self.delegateEvents();
                return self;
            });
        }
    });
});
