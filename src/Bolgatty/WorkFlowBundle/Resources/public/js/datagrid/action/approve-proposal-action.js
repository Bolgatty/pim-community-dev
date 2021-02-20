'use strict';

/**
 * Approve proposal action *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'oro/messenger',
        'oro/translator',
        'oro/datagrid/ajax-action',
        'pim/form-modal',
        'routing'
    ],
    function (
        $,
        _,
        mediator,
        messenger,
        __,
        AjaxAction,
        FormModal,
        Router
    ) {
        return AjaxAction.extend({
            /**
             * Parameters to be send with the request
             */
            actionParameters: {},

            /**
             * {@inheritdoc}
             */
            getMethod() {
                return 'POST';
            },

            /**
             * {@inheritdoc}
             */
            getLink() {
                const productDraftType = this.model.get('document_type');
                const id = this.model.get('proposal_id');
                console.log('reached here : ', productDraftType );
                // pimee_workflow_product_draft_rest_approve
                return Router.generate('pimee_workflow_' + productDraftType + '_rest_approve', { id });
            },

            /**
             * Override the default handler to trigger the popin to add comment
             *
             * {@inheritdoc}
             */
            _handleAjax(action) {
                if (this._isAllowedToComment(action)) {
                    const modalParameters = {
                        title: __('bolgatty_workflow.entity.product_draft.module.proposal.accept'),
                        okText: __('bolgatty_workflow.entity.product_draft.module.proposal.confirm'),
                        cancelText: __('pim_common.cancel'),
                    illustrationClass: 'proposal'
                };

                    const formModal = new FormModal(
                        'bolgatty-workflow-proposal-add-comment',
                        this.validateForm.bind(this),
                        modalParameters
                    );

                    formModal.open().then(function () {
                        AjaxAction.prototype._handleAjax.apply(this, [action]);
                    }.bind(this));
                } else {
                    AjaxAction.prototype._handleAjax.apply(this, [action]);
                }
            },

            _isAllowedToComment(action) {
                return true;
            },

            /**
             * Override the default handler to trigger the event containing the new product data
             *
             * @param response
             */
            _onAjaxSuccess(response) {
                messenger.notify(
                    'success',
                    __('bolgatty_workflow.entity.product_draft.flash.approve.success')
                );

                mediator.trigger('pim_enrich:form:proposal:post_approve:success', response);

                /**
                 * Hard reload of the page, if deleted the last grid proposal,
                 * in order to refresh proposal grid filters.
                 */
                if (1 === this.datagrid.collection.models.length && 'proposal-grid' === this.datagrid.name) {
                    window.location.reload();
                } else {
                    this.datagrid.collection.fetch();
                }
            },

            /**
             * Override the default handler to avoid displaying the error modal and triggering our own event instead
             *
             * @param jqXHR
             */
            _onAjaxError(jqXHR) {
                var message = jqXHR.responseJSON.message;

                messenger.notify(
                    'error',
                    __('bolgatty_workflow.entity.product_draft.flash.approve.fail', {
                        error: jqXHR.responseJSON.message
                    })
                );

                this.datagrid.hideLoading();

                mediator.trigger('pim_enrich:form:proposal:post_approve:error', message);
            },

            /**
             * Validate the given form data. We must check for comment length.
             *
             * @param {Object} form
             *
             * @return {Promise}
             */
            validateForm(form) {
                var comment = form.getFormData().comment;
                this.actionParameters.comment = _.isUndefined(comment) ? null : comment;

                return $.Deferred().resolve();
            },

            /**
             * {@inheritdoc}
             */
            getActionParameters() {
                return this.actionParameters;
            }
        });
    }
);