<?php
/**
 * wpglobus_dropdown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPGlobusOptions_wpglobus_dropdown
 */
class WPGlobusOptions_wpglobus_dropdown {


	/**
	 * WPGlobusOptions_wpglobus_dropdown constructor.
	 *
	 * @param array $field Field attributes.
	 */
	public function __construct( $field ) {

		$this->render( $field );
	}

	/**
	 * Render the field.
	 *
	 * @param array $field Field attributes.
	 */
	public function render( $field ) {
		?>
		<div id="wpglobus-options-<?php echo esc_attr( $field['id'] ); ?>"
				class="wpglobus-options-field wpglobus-options-field-wpglobus_select">
			<div class="grid__item">
				<label class="title" for="<?php echo esc_attr( $field['id'] ); ?>-select">
					<?php echo esc_html( $field['title'] ); ?>
				</label>
				<?php if ( ! empty( $field['subtitle'] ) ) { ?>
					<p class="subtitle"><?php echo esc_html( $field['subtitle'] ); ?></p>
				<?php } ?>
			</div>
			<div class="grid__item">
				<select id="<?php echo esc_attr( $field['id'] ); ?>-select"
						name="<?php echo esc_attr( $field['name'] ); ?>">';
					<?php foreach ( $field['options'] as $value => $label ): ?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $field['default'] ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php if ( ! empty( $field['desc'] ) ): ?>
					<p class="description"><?php echo esc_html( $field['desc'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

/**
 * @global array $field
 * @see WPGlobus_Options::pageOptions
 */
new WPGlobusOptions_wpglobus_dropdown( $field );