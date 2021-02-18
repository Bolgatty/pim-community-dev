'use strict';

define([
        'underscore',
        'jquery',
        'routing',
        'pim/form/common/save',
        'pim/template/form/save'
    ],
    function(
        _,
        $,
        Routing,
        SaveForm,
        template
    ) {
        return SaveForm.extend({
            template: _.template(template),
            currentKey: 'current_form_tab',
            events: {
                'click .save': 'save'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {      
                this.$el.html(this.template({
                    label: _.__('pim_enrich.entity.save.label')
                })); 
                this.delegateEvents();
            },

            /**
             * {@inheritdoc}
             */
            save: function () {
                this.getRoot().trigger('pim_enrich:form:entity:pre_save', this.getFormData());
                this.showLoadingMask();

                var data = this.stringify(this.getFormData());
                $.ajax({
                    method: 'POST',
                    url: this.getSaveUrl(),
                    contentType: 'application/json',
                    data: data
                })
                .then(this.postSave.bind(this))
                .fail(this.fail.bind(this))
                .always(this.hideLoadingMask.bind(this));
            },

            stringify: function(formData) {
                if('undefined' != typeof(formData['mapping']) && formData['mapping'] instanceof Array) {
                    formData['mapping'] = $.extend({}, formData['mapping']);
                }

                return JSON.stringify(formData);                
            },

            /**
             * {@inheritdoc}
             */
            getSaveUrl: function () {
                var tab = null;
                switch(sessionStorage.getItem(this.currentKey)) { 
                    case 'Bolgatty-additional-featuers-configuration-tab-settings':
                        tab = 'settings';
                        break;
                }
                var route = Routing.generate(this.config.route);
                
                return tab ? route + '/' + tab : route;
            },

            /**
             * {@inheritdoc}
             */
            postSave: function (data) {
                this.setData(data);
                this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);

                SaveForm.prototype.postSave.apply(this, arguments);
            }     
        });
    }
);
