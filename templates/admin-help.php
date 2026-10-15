<?php
/**
 * Template: practitioner help with native, keyboard accessible disclosures.
 *
 * @package RoxyAPI
 */

use RoxyAPI\Admin\BuilderPage;
use RoxyAPI\Admin\Onboarding;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Admin\ShortcodesPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap roxyapi-settings">
	<h1 class="roxyapi-heading">
		<span class="roxyapi-setup"><?php echo esc_html__( 'Help.', 'roxyapi' ); ?></span>
		<span class="roxyapi-punchline"><?php echo esc_html__( 'Quick answers.', 'roxyapi' ); ?></span>
	</h1>
	<details class="roxyapi-faq">
		<summary><?php echo esc_html__( 'How do I add a reading to a page?', 'roxyapi' ); ?></summary>
		<p><?php echo esc_html__( 'Open Create a reading, answer a few questions and copy the shortcode. In the block editor, add a Shortcode block and paste it there.', 'roxyapi' ); ?></p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . BuilderPage::PAGE_SLUG ) ); ?>"><?php echo esc_html__( 'Create a reading', 'roxyapi' ); ?></a></p>
	</details>
	<details class="roxyapi-faq">
		<summary><?php echo esc_html__( 'Can my visitors enter their own birth details?', 'roxyapi' ); ?></summary>
		<p><?php echo esc_html__( 'Yes. Choose Ask my visitor and the reading shows a form. Each visitor sees a reading made for their own details.', 'roxyapi' ); ?></p>
		<p><a href="<?php echo esc_url( Onboarding::docs_url() . '#ask-your-visitor-or-show-a-set-reading' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Learn more', 'roxyapi' ); ?></a></p>
	</details>
	<details class="roxyapi-faq">
		<summary><?php echo esc_html__( 'Can I charge for readings?', 'roxyapi' ); ?></summary>
		<p><?php echo esc_html__( 'Yes. Keep one reading free and put the deeper ones on a page only paying members can open.', 'roxyapi' ); ?></p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . SettingsPage::PAGE_SLUG . '&tab=sell-readings' ) ); ?>"><?php echo esc_html__( 'Sell readings', 'roxyapi' ); ?></a>
			<a href="<?php echo esc_url( Onboarding::docs_url( 'monetization' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Read the full guide', 'roxyapi' ); ?></a>
		</p>
	</details>
	<details class="roxyapi-faq">
		<summary><?php echo esc_html__( 'Is client data safe?', 'roxyapi' ); ?></summary>
		<p><?php echo esc_html__( 'Birth details are used only to calculate the reading. Your key stays on your server and never reaches the browser.', 'roxyapi' ); ?></p>
		<p><a href="<?php echo esc_url( Onboarding::docs_url( 'data-protection' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Learn more', 'roxyapi' ); ?></a></p>
	</details>
	<details class="roxyapi-faq">
		<summary><?php echo esc_html__( 'Which language do readings use?', 'roxyapi' ); ?></summary>
		<p><?php echo esc_html__( 'Readings follow your site language. To choose another, open Settings and pick a reading language on the Branding tab.', 'roxyapi' ); ?></p>
		<p><a href="<?php echo esc_url( Onboarding::docs_url() . '#language-and-appearance' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Learn more', 'roxyapi' ); ?></a></p>
	</details>
	<details class="roxyapi-faq">
		<summary><?php echo esc_html__( 'Will readings match my theme?', 'roxyapi' ); ?></summary>
		<p><?php echo esc_html__( 'Yes. Readings follow your light or dark theme, and you can set the chart style on the Branding tab.', 'roxyapi' ); ?></p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . SettingsPage::PAGE_SLUG . '&tab=branding' ) ); ?>"><?php echo esc_html__( 'Branding', 'roxyapi' ); ?></a></p>
	</details>
	<details class="roxyapi-faq">
		<summary><?php echo esc_html__( 'Where can I see every reading?', 'roxyapi' ); ?></summary>
		<p><?php echo esc_html__( 'Open Shortcodes for the full list, or Create a reading to search by name.', 'roxyapi' ); ?></p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . ShortcodesPage::PAGE_SLUG ) ); ?>"><?php echo esc_html__( 'Shortcodes', 'roxyapi' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . BuilderPage::PAGE_SLUG ) ); ?>"><?php echo esc_html__( 'Create a reading', 'roxyapi' ); ?></a>
		</p>
	</details>
	<details class="roxyapi-faq">
		<summary><?php echo esc_html__( 'I still need help.', 'roxyapi' ); ?></summary>
		<p><?php echo esc_html__( 'Send us a message and we will reply by email.', 'roxyapi' ); ?></p>
		<p><a href="<?php echo esc_url( Onboarding::support_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Contact us', 'roxyapi' ); ?></a></p>
	</details>
</div>
