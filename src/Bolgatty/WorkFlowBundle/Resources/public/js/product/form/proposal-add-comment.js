/**
 * Form used to add a comment on a proposal when
 * a product owner refuses it or accepts it.
 * 
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'workflow/product/form/abstract-add-notification-comment'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        AbstractCommentForm
    ) {
        return AbstractCommentForm.extend({
            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(
                    this.template({
                        label: __('bolgatty_workflow.entity.product.proposal.modal.title'),
                        characters: __('bolgatty_workflow.entity.product_draft.module.proposal.comment_chars')
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
