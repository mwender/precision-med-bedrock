<?php
/**
 * Keeps patient-entered form content out of the AnalyticsWP events table.
 *
 * AnalyticsWP's Elementor integration records every submitted field verbatim
 * (Lib/Integrations/ElementorIntegration.php::track_form_submission, which sets
 * 'fields' => $record->get_formatted_data() with no filter to intercept it).
 * On this site that means a name, phone, email and free-text health goals.
 * Elementor's own submission storage is deliberately switched off for exactly
 * that reason, so the analytics table would otherwise be the only place that
 * content came to rest — and it is the table we open in front of people.
 *
 * We still want the conversion count. So: drop their handler and fire the same
 * `form_submission` event without the payload. The AnalyticsWP dashboard reads
 * event type and form name, so submission totals per form keep working.
 *
 * Their handler runs on `elementor_pro/forms/new_record` at priority 10 and is
 * registered from ElementorIntegration::add_hooks(). Rather than guess when
 * that runs, we unhook it from priority 9 of the same action — WordPress
 * re-reads the callback list as it walks it, so removing a later callback from
 * an earlier one on the same hook is reliable.
 *
 * If the plugin ever gains a real filter for the field payload, delete this and
 * use that instead. If the tracked count silently stops, check whether their
 * method name or hook priority changed.
 */

add_action( 'elementor_pro/forms/new_record', function ( $record, $ajax_handler ): void {

	$handler = [ 'AnalyticsWP\Lib\Integrations\ElementorIntegration', 'track_form_submission' ];

	if ( ! class_exists( $handler[0] ) || ! has_action( 'elementor_pro/forms/new_record', $handler ) ) {
		return; // Plugin inactive, integration off, or upstream moved. Nothing to suppress.
	}

	remove_action( 'elementor_pro/forms/new_record', $handler, 10 );

	$form_settings = $record->get( 'form_settings' );

	// Mirror upstream's opt-in check: tracking is a per-form Elementor setting.
	if ( empty( $form_settings['enable_tracking'] ) || 'yes' !== $form_settings['enable_tracking'] ) {
		return;
	}

	$properties = [
		'form_name' => $form_settings['form_name'] ?? '',
		'form_id'   => $record->get_form_settings( 'id' ),
		// 'fields' deliberately omitted — see the docblock.
	];

	// Static properties configured on the widget by us, not entered by the visitor.
	$configured = json_decode( $form_settings['event_properties'] ?? '{}', true ) ?? [];

	AnalyticsWP\Lib\Event::track_server_event( 'form_submission', array_merge( $properties, $configured ) );

}, 9, 2 );
