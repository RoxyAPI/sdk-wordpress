import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon="lightbulb"
				label={ __( 'Dream Symbol', 'roxyapi' ) }
				instructions={ __( 'Dream symbol dictionary with meanings and interpretations.', 'roxyapi' ) }
			/>
		</div>
	);
}
