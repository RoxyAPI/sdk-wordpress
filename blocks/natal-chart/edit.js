import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon="admin-users"
				label={ __( 'Natal Chart', 'roxyapi' ) }
				instructions={ __( 'Western birth chart with planets, houses, and aspects.', 'roxyapi' ) }
			/>
		</div>
	);
}
