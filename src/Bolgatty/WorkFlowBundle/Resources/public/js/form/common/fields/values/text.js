'use strict';

define([
    'pim/form/common/fields/text',
    'pim/form/common/fields/values/values-behavior',
    'pim/fetcher-registry',
    'pim/common/property',
    'oro/loading-mask'
], (BaseField, ValuesBehavior, FetcherRegistry, propertyAccessor, LoadingMask) => {
    return BaseField.extend({
        events: {
            'keyup input': function (event) {
                this.errors = [];
                var fieldVal = ($(event.target).prop('readonly') || _.isUndefined(this.getFormData()[this.identifier])) ? event.target.value : this.getFormData()[this.identifier];
                this.setFieldValue(fieldVal);
            }
        },
        /**
         * {@inheritdoc}
         */
        generatedSku: null,
        getGeneratedSku: function() {   
            var uniqueSku = FetcherRegistry.getFetcher('unique-sku').search({cache:false});
            return Promise.all([uniqueSku]).then((values) => {
                var sku = values[0].sku;
                this.generatedSku = _.isNull(sku) ? undefined : sku;
                
                if(!_.isNull(sku)) {
                   this.setFieldValue(sku);
                }               

            }).catch((errors) => {
                console.log(errors);
            });
        },
        setFieldValue: function(sku)
        {
            const data = this.getFormData();
            var skuObj = (this.config.fieldName === 'values.sku') ? [{ scope: null, locale: null, data: sku }] : sku;
            propertyAccessor.updateProperty(data, this.config.fieldName, skuObj);
            this.setData(data);
        },
         /**
         * {@inheritdoc}
         */
        renderInput: async function (templateContext) {            
            await this.getGeneratedSku();
            if(!_.isUndefined(this.generatedSku)) {
                templateContext.readOnly = true;
            }
            return this.template(_.extend(templateContext, {
                value: this.generatedSku
            }));
        },
        /**
         * Renders the container template.
         */
        render: async function() {            
            if (!this.isVisible()) {
                this.$el.empty();

                return;
            }
            var loadingMask = new LoadingMask();
            loadingMask.render().$el.appendTo(this.getRoot().$el).show();

            this.getTemplateContext().then(function (templateContext) {

                this.renderInput(templateContext).then(function (fieldInputVal) {
                    this.$el.html(this.containerTemplate(templateContext));
                
                    this.$('.field-input').replaceWith(fieldInputVal);
                    this.postRender(templateContext);
                    this.renderExtensions();
                    this.delegateEvents();
                    loadingMask.hide().$el.remove();
                }.bind(this));


            }.bind(this));

        },

    });
});
