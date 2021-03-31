import { __ } from '@wordpress/i18n';
import { PanelBody, BaseControl, SelectControl } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

export default function TaxonomySearchEdit( props ) {
	const { attributes, setAttributes } = props;

	const { isSelectedTaxonomy } = attributes;

	let editContent;
	const selected = ( taxonomy ) => taxonomy.value === isSelectedTaxonomy;
	//eslint-disable-next-line camelcase,no-undef
	const selectedTaxonomy = vk_filter_search_params.taxonomy_list.find(
		selected
	);

	const condition = ( taxonomy ) => taxonomy.value === isSelectedTaxonomy;

	if (
		vk_filter_search_params.taxonomy_option.some( condition ) && //eslint-disable-line camelcase,no-undef
		isSelectedTaxonomy !== '' &&
		isSelectedTaxonomy !== null &&
		isSelectedTaxonomy !== undefined
	) {
		editContent = (
			<ServerSideRender
				block="vk-filter-search/taxonomy-search"
				attributes={ props.attributes }
			/>
		);
	} else if (
		isSelectedTaxonomy === '' ||
		isSelectedTaxonomy === null ||
		isSelectedTaxonomy === undefined
	) {
		editContent = (
			<div>
				<div className="vkfs__warning">
					<div className="vkfs__label-name">
						{ __( 'Taxonomy', 'filter-search-textdomain' ) }
					</div>
					<div className="vkfs__warning-text">
						{ __(
							'Because no taxonomy is selected, this block will not render.',
							'filter-search-textdomain'
						) }
					</div>
				</div>
			</div>
		);
	} else {
		editContent = (
			<div>
				<div className="vkfs__warning">
					<div className="vkfs__label-name">
						{ selectedTaxonomy.label }
					</div>
					<div className="vkfs__warning-text">
						{ __(
							'Because this taxonomy has no term, this block will not render.',
							'filter-search-textdomain'
						) }
					</div>
				</div>
			</div>
		);
	}

	const blockProps = useBlockProps( {
		className: `vkfs-taxonomy-search`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Taxonomy Option', 'filter-search-textdomain' ) }
					initialOpen={ true }
				>
					<BaseControl id={ 'vsfs03' }>
						<SelectControl
							label={ __( 'Taxonomy', 'filter-search-textdomain' ) }
							value={ isSelectedTaxonomy }
							//eslint-disable-next-line camelcase,no-undef
							options={ vk_filter_search_params.taxonomy_option }
							onChange={ ( value ) =>
								setAttributes( { isSelectedTaxonomy: value } )
							}
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>{ editContent }</div>
		</>
	);
}