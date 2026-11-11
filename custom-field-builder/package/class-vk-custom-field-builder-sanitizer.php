<?php
/**
 * Custom Field Builder の保存値をフィールド形式に応じて整える.
 *
 * @package vektor-inc/VK_Custom_Field_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VK_Custom_Field_Builder_Sanitizer' ) ) {
	/**
	 * Custom Field Builder の値を共通で整える.
	 */
	class VK_Custom_Field_Builder_Sanitizer {

		/**
		 * 配列を再帰的にたどり、各フィールド形式に応じて値を整える.
		 *
		 * @param mixed         $value 送信された値.
		 * @param mixed         $field_config フィールド定義または子フィールド定義.
		 * @param callable|null $image_sanitizer 画像フィールド用の任意のサニタイザー.
		 * @return mixed 整えた値.
		 */
		public static function sanitize_value( $value, $field_config = null, $image_sanitizer = null ) {
			if ( is_array( $value ) ) {
				// 配列の各要素に対応する定義を渡し、入れ子の値も同じ規則で処理する.
				foreach ( $value as $key => $item ) {
					if ( is_array( $field_config ) && isset( $field_config['type'] ) ) {
						$item_config = $field_config;
					} elseif ( is_array( $field_config ) && array_key_exists( $key, $field_config ) ) {
						$item_config = $field_config[ $key ];
					} else {
						$item_config = $field_config;
					}

					$value[ $key ] = self::sanitize_value( $item, $item_config, $image_sanitizer );
				}

				return $value;
			}

			if ( is_array( $field_config ) && isset( $field_config['type'] ) ) {
				switch ( $field_config['type'] ) {
					case 'textarea':
						return wp_kses_post( $value );
					case 'url':
						return esc_url_raw( $value );
					case 'image':
						if ( is_callable( $image_sanitizer ) ) {
							return call_user_func( $image_sanitizer, $value );
						}
						break;
				}
			}

			return sanitize_text_field( $value );
		}
	}
}
