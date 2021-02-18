"use strict";

define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'autoskugenerator/template/configuration/tab/settings',
        'jquery',
        'oro/loading-mask',
        'pim/fetcher-registry',
        'pim/user-context',
        'bootstrap.bootstrapswitch',
        'pim/initselect2'
    ],
    function(
        _,
        __,
        BaseForm,
        template,
        $,
        LoadingMask,
        FetcherRegistry,
        UserContext,
        select2
    ) {
        return BaseForm.extend({
            isGroup: true,
            label: __('autoskugenerator.settings'),
            template: _.template(template),
            code: 'autoskugenerator.settings.tab',
            events: {
                'change .AknFormContainer-Mappings .field-input, select, input': 'updateModel',
                'keyup input': 'validateField'
            },
            validateField: function(event) {
                var invalidChars = ["-", "e", "+", "E"];
                var fieldName = $(event.target).attr('name');
               
                if('startSkuSequence' == fieldName && invalidChars.includes(event.key)) {
                    var fieldVal = $(event.target).attr('value');
                    var filterValue = fieldVal.replace(/[e\+\-]/gi, '');
                    $('.sku_generator_sequence_class').val(filterValue);
                }
            },
            skuGeneratorForm: { name: "startSkuSequence", type: "input", readonly: "true", label: "Auto SKU Start Sequence From" },
            autoSkuGeneratorOptions: [
                { name: "autogenerateSkuOptions", type: "select", readonly: "true", multiple: true,
                label: "Autogenerate SKU Options",
                    tooltip : 'supported attributes types: text , select. attribute must not to be scopale, localizable or localespecific.', },
                { name: "skuPrefix", type: "input", readonly: "true", label: "Prefix", tooltip : 'Prefix will be append befor sku.', },
                { name: "skuSuffix", type: "input", readonly: "true", label: "Suffix", tooltip : 'Prefix will be append after sku.',  },
                { name: "skuSeparator", type: "select", readonly: "true", label: "Sku Separator", multiple: false,
                    options: [
                        {code: "slash", label: "Separate with slash ('/')"},
                        {code: "space", label: "Separate with space (' ')"},
                        {code: "hyphen", label: "Separate with hyphen ('-')"},
                        {code: "underscore", label: "Separate with underscore ('_')"}
                    ]    
            },
            ],
            attributes: null,
            locales: null,            
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:bad_request',
                    this.setValidationErrors.bind(this)
                );

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:pre_save',
                    this.resetValidationErrors.bind(this)
                );

                this.trigger('tab:register', {
                    code: this.code,
                    label: this.label
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },


            /**
             * {@inheritdoc}
             */
            render: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                var attributes;
                if(this.attributes) {
                    attributes = this.attributes;
                } else {
                    attributes = FetcherRegistry.getFetcher('attribute').search({options: {'page': 1, 'limit': 10000 } });
                }
                var locales;
                if(this.locales) {
                    locales = this.locales;
                } else {
                    locales = FetcherRegistry.getFetcher('wk-active-locales').fetchAll({cached: false});
                }
                
                var self = this; 
                Promise.all([attributes, locales]).then(function(values) {
                    var formData = self.getFormData()['settings']; 

                    // self.attributes = self.sortByLabel(values[0]);  
                    self.attributes = self.sortAttributes(values[0], formData);
                    var flag = false;
                    if(!_.isUndefined(formData) && _.isUndefined(formData.skuSeparator)) {
                        formData['skuSeparator'] = 'slash';
                        flag = true;
                    }  
                    if(!_.isUndefined(formData) && _.isUndefined(formData.akeneo_locale)) {
                        formData['akeneo_locale'] = _.first(values[1]).code;
                        flag = true;
                    }
                    if(!_.isUndefined(formData) && !_.isUndefined(formData.akeneo_locale)) {
                        var chnagedLocale = _.findWhere(values[1], { code: formData.akeneo_locale });
                        formData['akeneo_locale'] = !_.isUndefined(chnagedLocale) ? formData.akeneo_locale : _.first(values[1]).code;
                        flag = true;                        
                    }
                    
                    if( flag) {
                        self.setData(formData);
                    }
                    self.$el.html(self.template({
                        skuGeneratorForm: self.skuGeneratorForm,
                        optionsForm: self.autoSkuGeneratorOptions,
                        attributes: self.filterAttributes(self.attributes),
                        model: formData, 
                        error: self.errors,
                        locales: values[1],
                        moduleVersion: self.getFormData()['module_version'],
                        currentLocale: UserContext.get('uiLocale'), 
                    }));
                    
                    $('.select2').select2();
                    loadingMask.hide().$el.remove();
                });

                self.delegateEvents();
                return BaseForm.prototype.render.apply(self, arguments);
            },
            sortAttributes: function(data,  fields) {
                var mappedData = (typeof(fields) !== 'undefined' && typeof(fields.autogenerateSkuOptions) !== 'undefined' && typeof(fields.autogenerateSkuOptions) !== 'undefined') && fields.autogenerateSkuOptions ? fields.autogenerateSkuOptions : [];
                
                data.sort(function(a, b) {
                    var textA = mappedData.indexOf(a.code);
                    var textB = mappedData.indexOf(b.code);
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                });

                return data;                
            },
            sortByLabel: function(data) {
                data.sort(function(a, b) {
                    var textA = typeof(a.labels[UserContext.get('uiLocale')]) !== 'undefined' && a.labels[UserContext.get('uiLocale')] ? a.labels[UserContext.get('uiLocale')].toUpperCase() : a.code.toUpperCase();
                    var textB = typeof(b.labels[UserContext.get('uiLocale')]) !== 'undefined' && b.labels[UserContext.get('uiLocale')] ? b.labels[UserContext.get('uiLocale')].toUpperCase() : b.code.toUpperCase();
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                });
                return data;
            },
            /**
             * Update model after value change
             *
             * @param {Event} event
             */
            updateModel: function (event) {
                var data = this.getFormData(); 
                
                if(
                    typeof data['settings'] === 'undefined' 
                    || ! data['settings']
                    || typeof data['settings'] !== 'object'
                    || data['settings'] instanceof Array
                ) {
                    
                    data['settings'] = {};
                }

                var attrName = $(event.target).attr('name');
                var attrValue = $(event.target).val();
                if( 'autogenerateSkuOptions' === attrName ) {                    
                    var attrValue = $(event.target).select2('data');
                    attrValue = attrValue.map((obj) => { return obj.id});                   
                }               
                
                data['settings'][attrName] = attrValue;
                this.setData(data);
            },
            filterAttributes: function(attributres) {
                var allAttrs = [];
                _.each(attributres, (attribute) => {
                    if(!attribute.scopable && !attribute.localizable && !attribute.is_locale_specific) {
                        allAttrs.push(attribute);  
                    }
                });
              
                return allAttrs;
            },
            /**
             * Sets errors
             *
             * @param {Object} errors
             */
            setValidationErrors: function (errors) {
                this.errors = errors.response;
                this.render();
            },

            /**
             * Resets errors
             */
            resetValidationErrors: function () {
                this.errors = {};
                this.render();
            }
 
        });
    }
);
