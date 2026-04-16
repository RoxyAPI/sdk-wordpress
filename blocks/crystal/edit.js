import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon="heart"
				label={ __( 'Crystal', 'roxyapi' ) }
				instructions={ __( 'Crystal properties, zodiac pairings, and healing guidance.', 'roxyapi' ) }
			/>
		</div>
	);
}
