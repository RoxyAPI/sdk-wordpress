import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon="chart-line"
				label={ __( 'Biorhythm', 'roxyapi' ) }
				instructions={ __( 'Physical, emotional, intellectual, and intuitive cycles.', 'roxyapi' ) }
			/>
		</div>
	);
}
