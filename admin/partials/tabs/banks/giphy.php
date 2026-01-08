<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<tr valign="top">
    <td colspan="2" class="source-logo"><img alt="GIPHY Logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/giphy.svg' ); ?>"></td>
</tr>

<tr valign="top">
    <td colspan="2">
        <div class="update-nag">
            <?php esc_html_e( 'Create a free API key on developers.giphy.com and paste it below. You can choose whether to query GIFs or Stickers, filter by rating, language, and limit the amount of results.', 'all-sources-images' ); ?>
        </div>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="giphy-apikey"><?php esc_html_e( 'API key', 'all-sources-images' ); ?></label>
    </th>
    <td id="password-giphy" class="password">
        <input id="giphy-apikey" type="password" name="ALLSI_plugin_banks_settings[giphy][apikey]" class="form-control" value="<?php echo ( isset( $options['giphy']['apikey'] ) && ! empty( $options['giphy']['apikey'] ) ) ? esc_attr( $options['giphy']['apikey'] ) : ''; ?>">
        <i id="togglePassword"></i>
    </td>
</tr>

<tr valign="top">
    <td colspan="2">
        <button type="button" class="btn btn-primary" id="btnGiphy">
            <?php esc_html_e( 'API testing', 'all-sources-images' ); ?>
        </button>
        <span id="resultGiphy"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden"/></span>
    </td>
</tr>

<tr valign="top">
    <td colspan="2"><hr/></td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="giphy-media-type"><?php esc_html_e( 'Content type', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <select id="giphy-media-type" name="ALLSI_plugin_banks_settings[giphy][media_type]" class="form-control">
            <?php
                $allsi_media_selected = isset( $options['giphy']['media_type'] ) ? $options['giphy']['media_type'] : 'gifs';
                $allsi_media_types = array(
                __( 'GIFs', 'all-sources-images' )     => 'gifs',
                __( 'Stickers', 'all-sources-images' ) => 'stickers',
            );
                foreach ( $allsi_media_types as $allsi_label => $allsi_value ) {
                    echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_media_selected, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="giphy-rating"><?php esc_html_e( 'Rating filter', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <select id="giphy-rating" name="ALLSI_plugin_banks_settings[giphy][rating]" class="form-control">
            <?php
                $allsi_rating_selected = isset( $options['giphy']['rating'] ) ? $options['giphy']['rating'] : 'g';
                $allsi_ratings = array(
                __( 'G – suitable for all ages', 'all-sources-images' )      => 'g',
                __( 'PG – mild suggestive content', 'all-sources-images' )    => 'pg',
                __( 'PG-13 – may contain moderate content', 'all-sources-images' ) => 'pg-13',
                __( 'R – restricted content', 'all-sources-images' )        => 'r',
            );
                foreach ( $allsi_ratings as $allsi_label => $allsi_value ) {
                    echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_rating_selected, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="giphy-lang"><?php esc_html_e( 'Language', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <select id="giphy-lang" name="ALLSI_plugin_banks_settings[giphy][lang]" class="form-control">
            <?php
                $allsi_lang_selected = isset( $options['giphy']['lang'] ) ? $options['giphy']['lang'] : 'en';
                $allsi_languages = array(
                __( 'English', 'all-sources-images' ) => 'en',
                __( 'Spanish', 'all-sources-images' ) => 'es',
                __( 'Portuguese', 'all-sources-images' ) => 'pt',
                __( 'French', 'all-sources-images' ) => 'fr',
                __( 'German', 'all-sources-images' ) => 'de',
                __( 'Italian', 'all-sources-images' ) => 'it',
                __( 'Dutch', 'all-sources-images' ) => 'nl',
                __( 'Turkish', 'all-sources-images' ) => 'tr',
                __( 'Russian', 'all-sources-images' ) => 'ru',
                __( 'Japanese', 'all-sources-images' ) => 'ja',
                __( 'Korean', 'all-sources-images' ) => 'ko',
            );
                foreach ( $allsi_languages as $allsi_label => $allsi_value ) {
                    echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_lang_selected, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="giphy-limit"><?php esc_html_e( 'Max results per search', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <input id="giphy-limit" type="number" min="1" max="50" name="ALLSI_plugin_banks_settings[giphy][limit]" class="form-control" value="<?php echo isset( $options['giphy']['limit'] ) ? intval( $options['giphy']['limit'] ) : 25; ?>">
        <i><?php esc_html_e( 'GIPHY caps beta keys at 50 results per request.', 'all-sources-images' ); ?></i>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="giphy-bundle"><?php esc_html_e( 'Rendition bundle (optional)', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <input id="giphy-bundle" type="text" name="ALLSI_plugin_banks_settings[giphy][bundle]" class="form-control" value="<?php echo ( isset( $options['giphy']['bundle'] ) && ! empty( $options['giphy']['bundle'] ) ) ? esc_attr( $options['giphy']['bundle'] ) : ''; ?>">
        <i><?php esc_html_e( 'Restrict responses to a specific renditions bundle (e.g. messaging_non_clips).', 'all-sources-images' ); ?></i>
    </td>
</tr>
