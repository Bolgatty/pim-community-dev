/**
 * Send Product For Approval Extension
 * 
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'workflow/template/product/send-for-approval',
        'routing',
        'pim/user-context',
        'oro/loading-mask',
        'oro/messenger'
    ],
    function (
        _,
        __,
        BaseForm,
        template,
        Routing,
        UserContext,
        LoadingMask,
        messenger
    ) {
        return BaseForm.extend({
            tagName: 'a',
            className: 'AknDropdown-menuLink send-for-approval',
            updateFailureMessage: __('bolgatty_workflow.entity.product_draft.flash.create.fail'),
            updateSuccessMessage: __('bolgatty_workflow.entity.product_draft.flash.create.success'),
            template: _.template(template),
            config: null,
            events: {
              'click' : "sendForApproval"
            },
            initialize: function(config) {
                this.config = config.config;  
                return BaseForm.prototype.initialize.apply(this, arguments);
            },
            /**
             * {@inheritdoc}
             */
            configure: function () {
                return BaseForm.prototype.configure.apply(this, arguments);
            },
            sendForApproval: function() {
                this.showLoadingMask();
                $.ajax({
                    type: 'POST',
                    url: this.getUrl(),
                    data: null,
                    contentType: 'application/json; charset=UTF-8'
                }).then(function (entity) {
                    this.hideLoadingMask();
                    messenger.notify('success', this.updateSuccessMessage);
                }.bind(this));
            },
            
            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());
                return this;
            },

            getUrl: function() {
                var routeParam = this.config.idKeyName;                
                return Routing.generate( this.config.route, 
                { [routeParam]: this.getFormData().meta.id });
            },
            /**
             * Show the loading mask
             */
            showLoadingMask: function () {
                this.loadingMask = new LoadingMask();
                this.loadingMask.render().$el.appendTo(this.getRoot().$el).show();
            },

            /**
             * Hide the loading mask
             */
            hideLoadingMask: function () {
                this.loadingMask.hide().$el.remove();
            },
        });
    }
);
