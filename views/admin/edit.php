<?php
if ( ! defined('WPINC')) {
    die;
}
?>

<div class="wrap">

    <h1 class="wp-heading-inline"><?php _e('Edit price type', 'premmerce-price-types'); ?></h1>

    <hr class="wp-header-end">

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field(); ?>
        <input type="hidden" name="action" value="premmerce_update_price_type">
        <input type="hidden" name="ID" value="<?php echo $priceType['ID']; ?>">
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="name"><?php _e('Name', 'premmerce-price-types') ?></label>
                </th>
                <td>
                    <input type="text" id="name" class="regular-text" name="name"
                           value="<?php echo $priceType['name']; ?>">
                    <p class="description"><?php _e('Price type name', 'premmerce-price-types') ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="roles"><?php _e('Roles', 'premmerce-price-types') ?></label>
                </th>
                <td>
                    <select data-select="roles" size="10" name="roles[]" multiple>
                        <?php foreach ($roles as $key => $name): ?>
                            <?php $selected = in_array($key, $priceType['roles']) ? 'selected' : '' ?>
                            <option value="<?php echo $key; ?>" <?php echo $selected ?> ><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('Select roles to this price type', 'premmerce-price-types') ?></p>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit"
                           name="update_rice_type"
                           id="submit"
                           class="button button-primary"
                           value="<?php _e('Update', 'premmerce-price-types'); ?>"
                    >

                    <span id="delete-link">
                            <a class="delete" href="<?php echo $deleteUrl; ?>" data-action--delete><?= __('Delete',
                                    'premmerce-price-types'); ?></a>
                        </span>
                </td>
            </tr>

            </tbody>
        </table>
    </form>

</div>
