import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon="tag"
				label={ __( 'Tarot', 'roxyapi' ) }
				instructions={ __( 'Tarot card readings: single, three card, Celtic Cross, and more.', 'roxyapi' ) }
			/>
		</div>
	);
}
