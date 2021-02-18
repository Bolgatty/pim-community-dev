'use strict';
/**
 * Delete extension to not display the action if the user is not owner of the product
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
define(
    [
        'pim/product-edit-form/delete'
    ],
    function (Delete) {
        return Delete.extend({
            render: function () {
                if (!this.getFormData().meta.is_owner) {
                    return;
                }

                return Delete.prototype.render.apply(this, arguments);
            }
        });
    }
);
