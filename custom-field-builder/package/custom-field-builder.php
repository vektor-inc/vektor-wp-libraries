<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。
修正の際は上記リポジトリのデータを修正してください。
編集権限を持っていない方で何か修正要望などありましたら
各プラグインのリポジトリにプルリクエストで結構です。
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VK_Custom_Field_Builder' ) ) {

	class VK_Custom_Field_Builder {

		public static $version = '0.2.6';

		// define( 'Bill_URL', get_template_directory_uri() );
		public static function init() {
			add_action( 'admin_footer', array( __CLASS__, 'print_script' ), 10, 2 );
		}

		static function admin_directory_url() {
			global $vgjpm_custom_field_builder_url; // configファイルで指定
			$direcrory_url = $vgjpm_custom_field_builder_url;
			return $direcrory_url;
		}

		/*
		-------------------------------------------
		管理画面用共通js読み込み（記述場所によっては動作しないので注意）
		-------------------------------------------
		*/
		public static function print_script( $hook_suffix ) {
			global $hook_suffix;
			wp_register_script( 'datepicker', self::admin_directory_url() . 'js/datepicker.js', array( 'jquery', 'jquery-ui-datepicker' ), self::$version, true );
			wp_enqueue_script( 'datepicker' );

			// media_uploader.js は、メディアアップローダーを使うためのjs
			// Post Author Display と干渉するのでプロフィール画面では読み込まない
			$media_uploader_exclude = array( 'profile.php' );
			if ( has_filter( 'cfb_media_uploader_exclude' ) ) {
				if ( function_exists( 'apply_filters_deprecated' ) ) {
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Back-compat for legacy hook.
					$media_uploader_exclude = apply_filters_deprecated(
						'cfb_media_uploader_exclude',
						array( $media_uploader_exclude ),
						self::$version,
						'vgjpm_cfb_media_uploader_exclude'
					);
				} else {
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Back-compat for legacy hook.
					$media_uploader_exclude = apply_filters( 'cfb_media_uploader_exclude', $media_uploader_exclude );
				}
			}
			$media_uploader_exclude = apply_filters( 'vgjpm_cfb_media_uploader_exclude', $media_uploader_exclude );
			if ( ! in_array( $hook_suffix, $media_uploader_exclude, true ) ) {
				wp_enqueue_script( 'vk_mediauploader', self::admin_directory_url() . 'js/mediauploader.js', array( 'jquery' ), self::$version, true );
				wp_localize_script(
					'vk_mediauploader',
					'vk_cfb',
					array(
						'select_image' => __( 'Select image', 'vk-google-job-posting-manager' ),
					)
				);
			}

			// flexible-table の js が NestedPagesのjsと干渉して正常に動かなくなるので、NestedPagesのページで読み込まないように.
			$cfb_flexible_table_excludes = array( 'toplevel_page_nestedpages' );
			if ( has_filter( 'cfb_flexible_table_excludes' ) ) {
				if ( function_exists( 'apply_filters_deprecated' ) ) {
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Back-compat for legacy hook.
					$cfb_flexible_table_excludes = apply_filters_deprecated(
						'cfb_flexible_table_excludes',
						array( $cfb_flexible_table_excludes ),
						self::$version,
						'vgjpm_cfb_flexible_table_excludes'
					);
				} else {
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Back-compat for legacy hook.
					$cfb_flexible_table_excludes = apply_filters( 'cfb_flexible_table_excludes', $cfb_flexible_table_excludes );
				}
			}
			$cfb_flexible_table_excludes = apply_filters( 'vgjpm_cfb_flexible_table_excludes', $cfb_flexible_table_excludes );

			if ( ! in_array( $hook_suffix, $cfb_flexible_table_excludes, true ) ) {
				wp_enqueue_script( 'flexible-table', self::admin_directory_url() . 'js/flexible-table.js', array( 'jquery', 'jquery-ui-sortable' ), self::$version, true );
			}

			wp_enqueue_style( 'cf-builder-style', self::admin_directory_url() . 'css/cf-builder.css', array(), self::$version, 'all' );

			// Contact form 7　など jQuery ui のクラス名を使っていて干渉するので除外 .
			$cfb_jquery_ui_excludes = array( 'toplevel_page_wpcf7', 'toplevel_page_gf_edit_forms' );
			if ( has_filter( 'cfb_jquery_ui_excludes' ) ) {
				if ( function_exists( 'apply_filters_deprecated' ) ) {
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Back-compat for legacy hook.
					$cfb_jquery_ui_excludes = apply_filters_deprecated(
						'cfb_jquery_ui_excludes',
						array( $cfb_jquery_ui_excludes ),
						self::$version,
						'vgjpm_cfb_jquery_ui_excludes'
					);
				} else {
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Back-compat for legacy hook.
					$cfb_jquery_ui_excludes = apply_filters( 'cfb_jquery_ui_excludes', $cfb_jquery_ui_excludes );
				}
			}
			$cfb_jquery_ui_excludes = apply_filters( 'vgjpm_cfb_jquery_ui_excludes', $cfb_jquery_ui_excludes );
			if ( ! in_array( $hook_suffix, $cfb_jquery_ui_excludes, true ) ) {
				wp_enqueue_style( 'cf-builder-jquery-ui-style', self::admin_directory_url() . 'css/jquery-ui.css', array( 'cf-builder-style' ), self::$version, 'all' );
			}
		}

		public static function form_post_value( $post_field = '', $type = false ) {
			$value = '';
			global $post;
			$value = esc_attr( get_post_meta( $post->ID, $post_field, true ) );
			$posted_value = null;
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Value is sanitized below.
			if ( isset( $_POST[ $post_field ] ) ) {
				$posted_value = wp_unslash( $_POST[ $post_field ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
			}
			if ( isset( $_POST['noncename__fields'] ) && null !== $posted_value ) {
				$noncename__fields = sanitize_text_field( wp_unslash( $_POST['noncename__fields'] ) );
				if ( ! wp_verify_nonce( $noncename__fields, wp_create_nonce( __FILE__ ) ) ) {
					return $value;
				}
				if ( isset( $type ) && $type == 'textarea' ) {
					// n2brはフォームにbrがそのまま入ってしまうので入れない
					$value = wp_kses_post( $posted_value );
				} else {
					$value = esc_attr( $posted_value );
				}
			}
			return $value;
		}

		public static function form_required() {
			$required = '<span class="required">' . __( 'Required', 'vk-google-job-posting-manager' ) . '</span>';
			return $required;
		}

		/*
		-------------------------------------------
		保存値の取得と復元
		-------------------------------------------
		*/

		/**
		 * 投稿メタの値を、復元せず保存されているままの形で取得する
		 *
		 * get_post_meta() は単一キーを要求すると保存値を自前で復元するため、
		 * シリアライズされたオブジェクトが保存されていた場合はそれを生成してしまう。
		 * 生成してしまってから中身を検査しても手遅れなので、取得の時点で復元させない。
		 * get_post_custom() は全メタをまとめて要求するので、どの値も保存されたまま返る。
		 *
		 * @param  int    $post_id  Post ID to read the value of.
		 * @param  string $meta_key Meta key to read.
		 * @return string Stored value, or an empty string when the key holds no string value.
		 */
		public static function get_raw_post_meta( $post_id, $meta_key ) {
			$stored_all = get_post_custom( $post_id );

			if ( ! isset( $stored_all[ $meta_key ][0] ) || ! is_string( $stored_all[ $meta_key ][0] ) ) {
				return '';
			}

			return $stored_all[ $meta_key ][0];
		}

		/**
		 * 保存されている値を復元する。その際 PHP オブジェクトの生成は許可しない
		 *
		 * このライブラリが保存する値は、素の文字列かシリアライズされた配列のみ。
		 * シリアライズされたオブジェクトは復元せず破棄する。復元してしまうと、
		 * 保存されていたデータが別クラスのマジックメソッドに渡されてしまうため。
		 *
		 * @param  mixed $value Stored value as it is saved in the database.
		 * @return mixed Restored value, the value itself when it is not serialized, or an empty string when it cannot be restored safely.
		 */
		public static function maybe_unserialize_without_object( $value ) {
			// シリアライズされた文字列でなければ復元するものはない
			if ( ! is_string( $value ) || ! is_serialized( $value ) ) {
				return $value;
			}

			// シリアライズされた false だけは復元結果が正しく false になるため、
			// 後続の失敗判定より前に処理する
			if ( 'b:0;' === $value ) {
				return false;
			}

			// allowed_classes => false により、読み込み中に PHP がどのクラスも
			// インスタンス化しないため、どのクラスのマジックメソッドにも到達できない。
			// 壊れたデータの警告は意図的に抑制している。値はデータベース由来であり、
			// 壊れていた場合は直後で処理するため、画面表示のたびにエラーログを埋めてはならない
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- allowed_classes => false prevents PHP object injection, and a malformed value is handled below.
			$restored = @unserialize( $value, array( 'allowed_classes' => false ) );

			// 壊れたデータ、およびオブジェクトを含んだままのデータは、どちらも利用できない
			if ( false === $restored || self::contains_object( $restored ) ) {
				return '';
			}

			return $restored;
		}

		/**
		 * 値がオブジェクトか、あるいは何階層目かにオブジェクトを含む配列かを判定する
		 *
		 * @param  mixed $value Value to inspect.
		 * @param  int   $depth Current recursion depth.
		 * @return bool True when an object is found, or when the value nests deeper than the limit.
		 */
		public static function contains_object( $value, $depth = 0 ) {
			// 自分自身を指す配列を渡されると再帰が止まらずメモリを使い切るため、階層の上限で打ち切る。
			// このライブラリが保存するのはチェックボックスの選択値の配列で、入れ子はごく浅い。
			// 上限を超えた値は正常な保存値ではないので、オブジェクトを含む場合と同じく使わずに破棄する
			if ( 64 < $depth ) {
				return true;
			}

			if ( is_object( $value ) ) {
				return true;
			}

			if ( is_array( $value ) ) {
				foreach ( $value as $item ) {
					if ( self::contains_object( $item, $depth + 1 ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/*
		-------------------------------------------
		フォームテーブル
		-------------------------------------------
		*/
		public static function form_table( $custom_fields_array, $befor_items = '', $echo = true, $options = array() ) {

			wp_nonce_field( wp_create_nonce( __FILE__ ), 'noncename__fields' );

			global $post;
			global $vgjpm_custom_field_builder_url;

			$form_html = '';

			$form_html .= '<div class="vk-custom-field-builder">';
			$form_html .= '<table class="table table-striped table-bordered">';

			$form_html .= $befor_items;

			foreach ( $custom_fields_array as $key => $value ) {
				$form_html .= '<tr class="cf_item"><th class="text-nowrap"><label>' . $value['label'] . '</label>';
				$form_html .= ( isset( $value['required'] ) && $value['required'] ) ? self::form_required() : '';
				$form_html .= '</th><td>';

				if ( $value['type'] == 'text' || $value['type'] == 'url' ) {
					if ( isset( $value['before_text'] ) && $value['before_text'] ) {
						$form_html .= esc_html( $value['before_text'] ) . ' ';
					}

					$post_value = '';
					$form_post_value = self::form_post_value( $key );
					if ( ! empty( $form_post_value ) || '0' === $form_post_value ) {
						$post_value = $form_post_value;
					} elseif ( ! empty( $options[ $key ] ) ) {
						$post_value = $options[ $key ];
					}

					$form_html .= '<input class="form-control" type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $post_value ) . '" size="70">';

					if ( isset( $value['after_text'] ) && $value['after_text'] ) {
						$form_html .= ' ' . esc_html( $value['after_text'] );
					}
				} elseif ( $value['type'] == 'datepicker' ) {

					$post_value = '';
					if ( ! empty( self::form_post_value( $key ) ) ) {
						$post_value = self::form_post_value( $key );
					} elseif ( ! empty( $options[ $key ] ) ) {
						$post_value = $options[ $key ];
					}

					$form_html .= '<input class="form-control datepicker" type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $post_value ) . '" size="70">';

				} elseif ( $value['type'] == 'textarea' ) {

					$post_value = '';
					if ( ! empty( self::form_post_value( $key, 'textarea' ) ) ) {
						$post_value = self::form_post_value( $key, 'textarea' );
					} elseif ( ! empty( $options[ $key ] ) ) {
						$post_value = $options[ $key ];
					}

					if ( isset( $value['wysiwyg'] ) && $value['wysiwyg'] ) {
						ob_start();
						wp_editor(
							$post_value,
							$key,
							array(
								'textarea_name' => $key,
								'textarea_rows' => 10,
								'media_buttons' => false,
								'tinymce'       => false,
								'teeny'         => true,
								'quicktags'     => false,
							)
						);
						$form_html .= ob_get_clean();
					} else {
						$textarea_value = wp_kses_post( $post_value );
						$textarea_value = str_ireplace( '</textarea>', '&lt;/textarea&gt;', $textarea_value );
						$form_html .= '<textarea class="form-control cf_textarea_wysiwyg" name="' . esc_attr( $key ) . '" cols="70" rows="3">' . $textarea_value . '</textarea>';
					}

				} elseif ( $value['type'] == 'select' ) {
					$form_html .= '<select id="' . esc_attr( $key ) . '" class="form-control" name="' . esc_attr( $key ) . '"  >';

					foreach ( $value['options'] as $option_value => $option_label ) {
						if ( self::form_post_value( $key ) == $option_value ) {
							$selected = ' selected="selected"';
						} elseif ( ! empty( $options[ $key ] ) && $options[ $key ] === $option_value ) {
							$selected = ' selected="selected"';
						} else {
							$selected = '';
						}

						$form_html .= '<option value="' . esc_attr( $option_value ) . '"' . $selected . '>' . esc_html( $option_label ) . '</option>';
					}
					$form_html .= '</select>';

				} elseif ( $value['type'] == 'checkbox' || $value['type'] == 'radio' ) {
					// 保存値はそのままの形で取得し、シリアライズして保存されてたら戻す
					// get_post_meta() は取得時に自前で復元してしまうため、ここでは使わない
					$field_value = self::maybe_unserialize_without_object( self::get_raw_post_meta( $post->ID, $key ) );
					if ( empty( $field_value ) ) {
						$field_value = ( ! empty( $options[ $key ] ) ) ? $options[ $key ] : array();
					}
					$form_html .= '<ul>';

					// チェックボックスは配列としてしか扱えないため、配列以外は空配列にする
					if ( $value['type'] == 'checkbox' && ! is_array( $field_value ) ) {
						$field_value = array();
					}

					foreach ( $value['options'] as $option_value => $option_label ) {
						$selected = '';
						// print '<pre style="text-align:left">';print_r( $option_value );print '</pre>';
						// print '<pre style="text-align:left">';print_r($field_value);print '</pre>';
						// チェックボックス
						if ( $value['type'] == 'checkbox' ) {

							if ( is_array( $field_value ) && in_array( $option_value, $field_value ) ) {
								$selected = ' checked';
							}

							$form_html .= '<li><label><input type="checkbox" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '" value="' . esc_attr( $option_value ) . '"' . $selected . '  /><span>' . esc_html( $option_label ) . '</span></label></li>';

							// ラジオボタン
						} elseif ( $value['type'] == 'radio' ) {
							if ( $option_value == $field_value ) {
								$selected = ' checked';
							}
							$form_html .= '<li><label><input type="radio" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $option_value ) . '"' . $selected . '  /><span>' . esc_html( $option_label ) . '</span></label></li>';
						}
					} // foreach ($value['options'] as $option_value => $option_label) {

					$form_html .= '</ul>';

				} elseif ( $value['type'] == 'image' ) {
					if ( $post->$key ) {
						$thumb_image = wp_get_attachment_image_src( $post->$key, 'medium', false );
						if ( is_array( $thumb_image ) && ! empty( $thumb_image[0] ) ) {
							$thumb_image_url = $thumb_image[0];
						} else {
							$thumb_image_url = $vgjpm_custom_field_builder_url . 'images/no_image.png';
						}
					} elseif ( ! empty( $options[ $key ] ) ) {
						$thumb_image = wp_get_attachment_image_src( $options[ $key ], 'medium', false );
						if ( is_array( $thumb_image ) && ! empty( $thumb_image[0] ) ) {
							$thumb_image_url = $thumb_image[0];
						} else {
							$thumb_image_url = $vgjpm_custom_field_builder_url . 'images/no_image.png';
						}
					} else {
						$thumb_image_url = $vgjpm_custom_field_builder_url . 'images/no_image.png';
					}

					$post_value = '';
					if ( ! empty( self::form_post_value( $key ) ) ) {
						$post_value = self::form_post_value( $key );
					} elseif ( ! empty( $options[ $key ] ) ) {
						$post_value = $options[ $key ];
					}
					// ダミー & プレビュー画像
					$form_html .= '<img src="' . esc_url( $thumb_image_url ) . '" id="thumb_' . esc_attr( $key ) . '" alt="" class="input_thumb" style="width:200px;height:auto;"> ';

					// 実際に送信する値
					$form_html .= '<input type="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $post_value ) . '" style="width:60%;" />';

					// 画像選択ボタン
					// .media_btn がトリガーでメディアアップローダーが起動する
					// id名から media_ を削除した id 名の input 要素に返り値が反映される。
					// id名が media_src_ で始まる場合はURLを返す
					$form_html .= '<button type="button" id="media_' . esc_attr( $key ) . '" class="cfb_media_btn btn btn-default button button-default">' . esc_html__( 'Choose Image', 'vk-google-job-posting-manager' ) . '</button> ';

					// 削除ボタン
					// ボタンタグだとその場でページが再読込されてしまうのでaタグに変更
					$form_html .= '<a id="media_reset_' . esc_attr( $key ) . '" class="media_reset_btn btn btn-default button button-default">' . esc_html__( 'Delete Image', 'vk-google-job-posting-manager' ) . '</a>';

				} elseif ( 'file' === $value['type'] ) {

					$post_value = '';
					if ( ! empty( self::form_post_value( $key ) ) ) {
						$post_value = self::form_post_value( $key );
					} elseif ( ! empty( $options[ $key ] ) ) {
						$post_value = $options[ $key ];
					}

					$form_html .= '<input name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $post_value ) . '" style="width:60%;" />
<button type="button" id="media_src_' . esc_attr( $key ) . '" class="cfb_media_btn btn btn-default button button-default">' . esc_html__( 'Select file', 'vk-google-job-posting-manager' ) . '</button> ';
					if ( $post_value ) {
						$form_html .= '<a href="' . esc_url( $post_value ) . '" target="_blank" rel="noopener noreferrer" class="btn btn-default button button-default">' . esc_html__( 'View file', 'vk-google-job-posting-manager' ) . '</a>';
					}
				}
				if ( $value['description'] ) {
					$form_html .= '<div class="description">' . wp_kses_post( $value['description'] ) . '</div>';
				}
				$form_html .= '</td></tr>';
			}
			$form_html .= '</table>';
			$form_html .= '</div>';
			if ( $echo ) {
				wp_enqueue_media();
				echo wp_kses( $form_html, self::get_allowed_form_html() );
			} else {
				wp_enqueue_media();
				return wp_kses( $form_html, self::get_allowed_form_html() );
			}
		} // public static function form_table( $custom_fields_array, $befor_items, $echo = true ){

		/*
		-------------------------------------------
		入力された値の保存
		-------------------------------------------
		*/
		public static function save_cf_value( $custom_fields_array ) {

			global $post;

			// 設定したnonce を取得（CSRF対策）
			$noncename__fields = isset( $_POST['noncename__fields'] ) ? sanitize_text_field( wp_unslash( $_POST['noncename__fields'] ) ) : null;

			// nonce を確認し、値が書き換えられていれば、何もしない（CSRF対策）
			if ( ! wp_verify_nonce( $noncename__fields, wp_create_nonce( __FILE__ ) ) ) {
				return;
			}

			// 自動保存ルーチンかどうかチェック。そうだった場合は何もしない（記事の自動保存処理として呼び出された場合の対策）
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			foreach ( $custom_fields_array as $key => $value ) {

				$field_value = null;
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Value is sanitized below.
				if ( isset( $_POST[ $key ] ) ) {
					$field_value = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
				}
				$field_value = self::sanitize_field_value( $field_value, $value );

				// データが空だったら入れる
				if ( get_post_meta( $post->ID, $key ) == '' ) {
					add_post_meta( $post->ID, $key, $field_value, true );
					// 今入ってる値と違ってたらアップデートする
				} elseif ( $field_value != get_post_meta( $post->ID, $key, true ) ) {
					update_post_meta( $post->ID, $key, $field_value );
					// 入力がなかったら消す
				} elseif ( $field_value == '' ) {
					delete_post_meta( $post->ID, $key, get_post_meta( $post->ID, $key, true ) );
				}
			} // foreach ($custom_fields_all_array as $key => $value) {
		}

		private static function sanitize_field_value( $field_value, $field_config ) {
			if ( is_array( $field_value ) ) {
				return array_map( 'sanitize_text_field', $field_value );
			}

			if ( ! isset( $field_config['type'] ) ) {
				return sanitize_text_field( $field_value );
			}

			switch ( $field_config['type'] ) {
				case 'textarea':
					return wp_kses_post( $field_value );
				case 'url':
					return esc_url_raw( $field_value );
				default:
					return sanitize_text_field( $field_value );
			}
		}

		private static function get_allowed_form_html() {
			return array(
				'div'    => array( 'class' => true, 'id' => true, 'style' => true ),
				'p'      => array(),
				'br'     => array(),
				'strong' => array(),
				'em'     => array(),
				'table'  => array( 'class' => true ),
				'thead'  => array(),
				'tbody'  => array( 'class' => true ),
				'tr'     => array( 'class' => true ),
				'th'     => array( 'class' => true ),
				'td'     => array( 'class' => true ),
				'label'  => array(),
				'ul'     => array(),
				'li'     => array( 'style' => true ),
				'span'   => array( 'class' => true ),
				'input'  => array(
					'type'  => true,
					'name'  => true,
					'id'    => true,
					'class' => true,
					'value' => true,
					'size'  => true,
					'checked' => true,
					'style' => true,
				),
				'textarea' => array(
					'name'  => true,
					'class' => true,
					'cols'  => true,
					'rows'  => true,
				),
				'select' => array( 'id' => true, 'name' => true, 'class' => true ),
				'option' => array( 'value' => true, 'selected' => true ),
				'button' => array( 'id' => true, 'class' => true, 'type' => true ),
				'img'    => array( 'src' => true, 'id' => true, 'alt' => true, 'class' => true, 'style' => true ),
				'a'      => array( 'href' => true, 'target' => true, 'class' => true, 'rel' => true, 'id' => true ),
			);
		}
	} // class Vk_custom_field_builder

	VK_Custom_Field_Builder::init();

	require_once 'custom-field-flexible-table.php';

} // if ( ! class_exists( 'VK_Custom_Field_Builder' ) ) {
