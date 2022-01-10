<?php
class Flexible_Table_Sample {

	public static $custom_fields_array = array(
		'field_name'  => 'pattern_info',
		'row_default' => 1,
		'items'       => array(
			'block_type' => array(
				'label' => 'blockType',
				'type'  => 'text',
			),
		),
	);

	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'admin_menu', array( __CLASS__, 'add_metabox_table' ), 10, 2 );
		add_action( 'save_post', array( __CLASS__, 'save_custom_fields' ), 10, 2 );
	}

	/**
	 * Add meta box
	 *
	 * @return void
	 */
	public static function add_metabox_table() {

		$id            = 'meta_box__block_type';
		$title         = 'blockTypes';
		$callback      = array( __CLASS__, 'fields_form' );
		$screen        = 'vk-managing-patterns';
		$context       = 'advanced';
		$priority      = 'high';
		$callback_args = '';

		add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );

	}

	/**
	 * Custom fields Form
	 *
	 * @return void
	 */
	public static function fields_form() {

		echo '<p>通常ブロックタイプは自動で取得しますが、入力がある場合は入力内容が優先されます。テンプレートパーツを作る場合などにも利用できます。</p>';
		echo '例）<br><pre>
		core/template-part/header
		core/template-part/footer
		</pre>';

		global $post;
		$pattern_info = get_post_meta( $post->ID, 'pattern_info', true );
		VK_Custom_Field_Builder_Flexible_Table::form_table_flexible( self::$custom_fields_array );

	}

	/**
	 * Save function
	 *
	 * @return void
	 */
	public static function save_custom_fields() {

		VK_Custom_Field_Builder_Flexible_Table::save_cf_value( self::$custom_fields_array );

	}

}

new Flexible_Table_Sample();
