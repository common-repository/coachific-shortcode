<?php
/*
Plugin Name: Coachific Shortcode Plugin
Description: Enables shortcode to link Coachific functionality. Usage: <code>[coachific userhash="x7w3w3" login=false]</code> or <code>[coachific userhash="" login=false]</code> and manage hashes in the Wordpress user manager.
Version: 1.0
License: GPL
Author: Coachific
Author URI: http://coachific.com
Text Domain: coachific-shortcode
*/

function createCoachificEmbedJS($atts, $content = null) {
    $args = shortcode_atts( array(
        'userhash'   => '',
        'login'     => 'false'
    ), $atts, 'coachific' );

    $args['login'] = filter_var( $args['login'], FILTER_VALIDATE_BOOLEAN );

    if (is_user_logged_in() || $args['login'] == false) {
        if (!$args['userhash']) {
            $args['userhash'] = get_user_meta(get_current_user_id(), 'coachific_user_hash', true);
        }

        $userhash = sanitize_text_field($args['userhash']);

        $strURL = 'https://app.coachific.com/login.php/' . $userhash;

        return '<script type="text/javascript">document.location = "'.$strURL.'";</script>';
    } else {
        $html = esc_html__( 'Login To Continue', 'coachific' );
        echo "<h3>".$html."</h3>";
        $args = array('echo' => true);
        wp_login_form($args);
    }
}

add_shortcode('coachific', 'createCoachificEmbedJS');

/**
 * Back end display
 */

add_action( 'show_user_profile', 'coachific_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'coachific_show_extra_profile_fields' );

function coachific_show_extra_profile_fields( $user ) {
    $hash = get_user_meta($user->ID, 'coachific_user_hash', true );
    ?>
    <h3><?php esc_html_e( 'Coachific Integration', 'coachific' ); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="coachific_user_hash"><?php esc_html_e( 'Coachific User Hash', 'coachific' ); ?></label></th>
            <td>
                <input type="text"
                       id="coachific_user_hash"
                       name="coachific_user_hash"
                       value="<?php echo esc_attr( $hash ); ?>"
                       class="regular-text"
                />
            </td>
        </tr>
    </table>
    <?php
}

add_action( 'personal_options_update', 'coachific_update_profile_fields' );
add_action( 'edit_user_profile_update', 'coachific_update_profile_fields' );

function coachific_update_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }

    if ( ! empty( sanitize_text_field($_POST['coachific_user_hash']) ) ) {
        update_user_meta( $user_id, 'coachific_user_hash', sanitize_text_field($_POST['coachific_user_hash']) );
    } else {
        update_user_meta( $user_id, 'coachific_user_hash', '' );
    }
}

?>
