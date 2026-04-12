/**
 * Page Header Image - Block Editor Native Panel.
 * ページヘッダー画像のブロックエディタネイティブサイドバーパネル。
 *
 * @package vektor-inc/vektor-wp-libraries
 */

var registerPlugin = wp.plugins.registerPlugin;
var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
var Button = wp.components.Button;
var useSelect = wp.data.useSelect;
var useEntityProp = wp.coreData.useEntityProp;
var createElement = wp.element.createElement;
var Fragment = wp.element.Fragment;
var MediaUpload = wp.blockEditor.MediaUpload;
var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
var i18n = window.vkPageHeaderPanelI18n || {};

/**
 * Image upload field component.
 * 画像アップロードフィールドコンポーネント。
 *
 * @param {Object} props - Component props.
 * @param {string} props.label - Field label.
 * @param {number} props.imageId - Current image attachment ID.
 * @param {Function} props.onChange - Callback when image changes.
 * @return {Object} React element.
 */
var ImageUploadField = function ( props ) {
	var image = useSelect(
		function ( select ) {
			return props.imageId ? select( 'core' ).getMedia( props.imageId ) : null;
		},
		[ props.imageId ]
	);

	return createElement(
		'div',
		{ style: { marginBottom: '16px' } },
		createElement(
			'p',
			{ style: { fontWeight: 'bold', marginBottom: '8px' } },
			props.label
		),
		createElement(
			MediaUploadCheck,
			null,
			createElement( MediaUpload, {
				onSelect: function ( media ) {
					props.onChange( media.id );
				},
				allowedTypes: [ 'image' ],
				value: props.imageId,
				render: function ( renderProps ) {
					return createElement(
						Fragment,
						null,
						// Image preview.
						// 画像プレビュー。
						props.imageId && image
							? createElement(
									'div',
									{ style: { marginBottom: '8px' } },
									createElement( 'img', {
										src: image.source_url,
										alt: props.label,
										style: {
											maxWidth: '100%',
											height: 'auto',
										},
									} )
							  )
							: null,
						// Select / Change button.
						// 選択 / 変更ボタン。
						createElement(
							Button,
							{
								onClick: renderProps.open,
								variant: props.imageId ? 'secondary' : 'primary',
								style: { marginRight: '8px' },
							},
							props.imageId
								? i18n.changeImage || 'Change image'
								: i18n.selectImage || 'Select image'
						),
						// Remove button.
						// 削除ボタン。
						props.imageId
							? createElement(
									Button,
									{
										onClick: function () {
											props.onChange( 0 );
										},
										isDestructive: true,
										variant: 'tertiary',
									},
									i18n.removeImage || 'Remove image'
							  )
							: null
					);
				},
			} )
		)
	);
};

/**
 * Page Header Image panel component.
 * ページヘッダー画像パネルコンポーネント。
 *
 * @return {Object|null} React element or null if not a page.
 */
var PageHeaderImagePanel = function () {
	var postType = useSelect( function ( select ) {
		return select( 'core/editor' ).getCurrentPostType();
	}, [] );

	// Only display on page post type.
	// 固定ページでのみ表示する。
	if ( postType !== 'page' ) {
		return null;
	}

	var entityProp = useEntityProp( 'postType', postType, 'meta' );
	var meta = entityProp[ 0 ];
	var setMeta = entityProp[ 1 ];

	var pageHeaderImage = meta && meta.vk_page_header_image ? meta.vk_page_header_image : 0;
	var pageHeaderImageSp = meta && meta.vk_page_header_image_sp ? meta.vk_page_header_image_sp : 0;

	return createElement(
		PluginDocumentSettingPanel,
		{
			name: 'vk-page-header-image',
			title: i18n.pageHeaderTitle || 'Page Header Image',
		},
		createElement( ImageUploadField, {
			label: i18n.pageHeaderBg || 'Page header bg image',
			imageId: pageHeaderImage,
			onChange: function ( value ) {
				var newMeta = Object.assign( {}, meta );
				newMeta.vk_page_header_image = value;
				setMeta( newMeta );
			},
		} ),
		createElement( ImageUploadField, {
			label:
				( i18n.pageHeaderBg || 'Page header bg image' ) +
				' ( ' +
				( i18n.mobile || 'Mobile' ) +
				' )',
			imageId: pageHeaderImageSp,
			onChange: function ( value ) {
				var newMeta = Object.assign( {}, meta );
				newMeta.vk_page_header_image_sp = value;
				setMeta( newMeta );
			},
		} )
	);
};

registerPlugin( 'vk-page-header-image-panel', {
	render: PageHeaderImagePanel,
	icon: 'format-image',
} );
