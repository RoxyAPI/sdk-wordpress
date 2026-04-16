import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon="superhero"
				label={ __( 'Angel Number', 'roxyapi' ) }
				instructions={ __( 'Angel number meanings and pattern analysis.', 'roxyapi' ) }
			/>
		</div>
	);
}
