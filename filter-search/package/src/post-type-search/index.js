import { __ } from '@wordpress/i18n';
import { ReactComponent as Icon } from './icon.svg';

import metadata from './block.json';
import edit from './edit';

const { name } = metadata;

export { metadata, name };

export const settings = {
	title: __( 'VK Post Type Search', 'filter-search-textdomain' ),
	icon: <Icon />,
	edit,
};
