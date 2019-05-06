<?php

namespace ASMBS\WPParsedown;

/**
 * Handles the plugin Settings page.
 *
 * Class SettingsPage
 * @package ASMBS\WPParsedown
 */
class SettingsPage {

	const OPTION_NAME = 'asmbs_parsedown_options';

	public $options;

	/**
	 * SettingsPage constructor.
	 */
	public function __construct() {

		$this->options = get_option( self::OPTION_NAME );

		// Hook admin menu for plugin options
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );

		// Register settings and fields methods
		add_action( 'admin_init', [ $this, 'register_settings_and_fields' ] );
	}

	// -----------------------------------------------------------------------------------------------------------------

	public function add_admin_menu(): void {
		add_options_page( 'WP Parsedown', 'WP Parsedown', 'manage_options', self::OPTION_NAME, [
			$this,
			'settings_page'
		] );
	}

	protected function get_default_options() {
		return [
			'image_shortcode' => 1,
			'image_warn_markdown' => 1,
			'image_warn_html' => 1,
			'image_show_id' => 1
        ];
    }

    static function get_option($option){
		return get_option( self::OPTION_NAME ) ? get_option( self::OPTION_NAME )[$option] : null;
    }

	// Sections & Fields -----------------------------------------------------------------------------------------------

	public function register_settings_and_fields(): void {

		register_setting( self::OPTION_NAME, self::OPTION_NAME );

		if (!$this->options) {
			$this->options = $this->get_default_options();
			update_option( self::OPTION_NAME, $this->options );
		}

		// Sections
		add_settings_section( 'asmbs_parsedown_shortcode_options_section', 'Image Shortcode Options', [
			$this,
			'asmbs_parsedown_shortcode_options_section_cb'
		], self::OPTION_NAME );

		// Fields
		add_settings_field( 'image_shortcode', 'Image shortcode', [
			$this,
			'asmbs_parsedown_image_shortcode_setting_cb'
		], self::OPTION_NAME, 'asmbs_parsedown_shortcode_options_section' );
		add_settings_field( 'image_warn_markdown', 'Warn when using Markdown for images', [
			$this,
			'asmbs_parsedown_image_warn_markdown_setting_cb'
		], self::OPTION_NAME, 'asmbs_parsedown_shortcode_options_section' );
		add_settings_field( 'image_warn_html', 'Warn when using HTML img tags for images', [
			$this,
			'asmbs_parsedown_image_warn_html_setting_cb'
		], self::OPTION_NAME, 'asmbs_parsedown_shortcode_options_section' );
		add_settings_field( 'image_show_id', 'Show image ID when viewing in Media Library', [
			$this,
			'asmbs_parsedown_image_show_id_setting_cb'
		], self::OPTION_NAME, 'asmbs_parsedown_shortcode_options_section' );
	}

	// Section Callbacks -----------------------------------------------------------------------------------------------

	public function asmbs_parsedown_shortcode_options_section_cb() {
		echo 'Enable and disable settings below.';
	}

	// Field Callbacks -------------------------------------------------------------------------------------------------

	public function asmbs_parsedown_image_shortcode_setting_cb() {
		?>
		<label>
			<input name="asmbs_parsedown_options[image_shortcode]" type="radio" value="0" <?php checked( 0, $this->options['image_shortcode'] ); ?> />
			Disabled
		</label><br />
		<label>
			<input name="asmbs_parsedown_options[image_shortcode]" type="radio" value="1" <?php checked( 1, $this->options['image_shortcode'] ); ?> />
			Enabled
		</label>
		<?php
	}

	public function asmbs_parsedown_image_warn_markdown_setting_cb() {
		?>
		<label>
			<input name="asmbs_parsedown_options[image_warn_markdown]" type="radio" value="0" <?php checked( 0, $this->options['image_warn_markdown'] ); ?> />
			Disabled
		</label><br />
		<label>
			<input name="asmbs_parsedown_options[image_warn_markdown]" type="radio" value="1" <?php checked( 1, $this->options['image_warn_markdown'] ); ?> />
			Enabled
		</label>
		<?php
	}

	public function asmbs_parsedown_image_warn_html_setting_cb() {
		?>
		<label>
			<input name="asmbs_parsedown_options[image_warn_html]" type="radio" value="0" <?php checked( 0, $this->options['image_warn_html'] ); ?> />
			Disabled
		</label><br />
		<label>
			<input name="asmbs_parsedown_options[image_warn_html]" type="radio" value="1" <?php checked( 1, $this->options['image_warn_html'] ); ?> />
			Enabled
		</label>
		<?php
	}

	public function asmbs_parsedown_image_show_id_setting_cb() {
		?>
		<label>
			<input name="asmbs_parsedown_options[image_show_id]" type="radio" value="0" <?php checked( 0, $this->options['image_show_id'] ); ?> />
			Disabled
		</label><br />
		<label>
			<input name="asmbs_parsedown_options[image_show_id]" type="radio" value="1" <?php checked( 1, $this->options['image_show_id'] ); ?> />
			Enabled
		</label>
		<?php
	}

	// Settings Page ---------------------------------------------------------------------------------------------------

	public function settings_page(): void {
		?>

		<div class="wrap">
			<h2>WP Parsedown Options</h2>
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php settings_fields( self::OPTION_NAME ); ?>
				<?php do_settings_sections( self::OPTION_NAME ); ?>
				<?php submit_button() ?>
			</form>
		</div>
		<?php
	}
}