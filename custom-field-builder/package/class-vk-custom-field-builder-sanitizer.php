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
		 * 保存できる値の長さ（バイト数）の上限. textarea 以外の項目に使う.
		 *
		 * 現実的でない長さの値は保存しない.
		 */
		const VALUE_MAX_LENGTH = 2048;

		/**
		 * 配列を再帰的にたどり、各フィールド形式に応じて値を整える.
		 *
		 * 長さが上限（VALUE_MAX_LENGTH バイト）を超える値は、textarea 以外の項目で、URL・画像・ファイルは空文字にし、それ以外は上限までに切り詰める.
		 * URL は整形後の値も上限以内か確かめ、超えたら空文字にする.
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
					if ( is_array( $field_config ) && isset( $field_config['type'] ) && is_string( $field_config['type'] ) ) {
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

			$type = ( is_array( $field_config ) && isset( $field_config['type'] ) && is_string( $field_config['type'] ) ) ? $field_config['type'] : '';

			// textarea 以外は、現実的でない長さの値を保存しない（途中で切った URL は壊れているため、URL・画像・ファイルは空にする）.
			if ( 'textarea' !== $type && is_scalar( $value ) && strlen( (string) $value ) > self::VALUE_MAX_LENGTH ) {
				if ( in_array( $type, array( 'url', 'image', 'file' ), true ) ) {
					return '';
				}
				$value = self::truncate_utf8_by_bytes( (string) $value, self::VALUE_MAX_LENGTH );
			}

			switch ( $type ) {
				case 'textarea':
					return wp_kses_post( $value );
				case 'url':
					$sanitized_url = esc_url_raw( $value );
					// 整形すると空白が %20 に変わり長くなることがあるため、整形後の値も上限以内か確かめる（超えたら切り詰めずに空にする）.
					if ( strlen( $sanitized_url ) > self::VALUE_MAX_LENGTH ) {
						return '';
					}
					return $sanitized_url;
				case 'image':
					if ( is_callable( $image_sanitizer ) ) {
						return call_user_func( $image_sanitizer, $value );
					}
					break;
			}

			return sanitize_text_field( $value );
		}

		/**
		 * 文字列を、UTF-8 の文字の途中で切らずに、指定したバイト数以下へ切り詰める.
		 *
		 * 拡張 mbstring の有無で結果が変わらないよう、PHP 標準の関数だけで処理する.
		 *
		 * @param string $value      切り詰める文字列.
		 * @param int    $max_length 上限のバイト数.
		 * @return string 切り詰めた文字列.
		 */
		private static function truncate_utf8_by_bytes( $value, $max_length ) {
			$value = substr( $value, 0, $max_length );

			// 末尾の文字が途中で切れていたら、そのバイトを取り除く（UTF-8 の 1 文字は最大 4 バイトのため、取り除くのは最大 3 バイト）.
			$removed = 0;
			while ( $removed < 3 && 1 !== preg_match( '//u', $value ) ) {
				$value = substr( $value, 0, -1 );
				++$removed;
			}

			return $value;
		}
	}
}
