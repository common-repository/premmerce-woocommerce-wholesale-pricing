<?php
if ( ! defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h2>Premmerce Wholesale Pricing for WooCommerce</h2>

    <hr class="wp-header-end">

    <div id="col-left">
        <div class="col-wrap">
            <div class="form-wrap">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field(); ?>

                    <input type="hidden" name="action" value="premmerce_create_price_type">

                    <h3 class="wp-heading-inline"><?php _e('Add new price type', 'premmerce-price-types'); ?></h3>

                    <div class="form-field form-required">
                        <label for="name"><?php _e('Name', 'premmerce-price-types') ?></label>
                        <input type="text" name="name" id="name">
                        <p class="description"><?php _e('Price type name', 'premmerce-price-types') ?></p>
                    </div>

                    <div class="form-field form-required">
                        <label for="roles"><?php _e('Roles', 'premmerce-price-types') ?></label>
                        <select data-select="roles" name="roles[]" multiple>
                            <?php foreach ($roles as $key => $name): ?>
                                <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Select roles to this price type',
                                'premmerce-price-types') ?></p>
                    </div>

                    <?php submit_button(__('Add new price type', 'premmerce-price-types')); ?>
                </form>
            </div>
        </div>
    </div>

    <div id="col-right">
        <div class="col-wrap">
            <form action="" method="POST">
                <?php $table->display(); ?>
            </form>
        </div>
    </div>
</div>
