<?php
/**
 * Room rate desposit
 *
 * This template can be overridden by copying it to yourtheme/hotelier/room-list/content/rate/rate-deposit.php.
 *
 * @author  Benito Lopez <hello@lopezb.com>
 * @package Hotelier/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $variation->needs_deposit() ) : ?>

<div class="rate__deposit rate__deposit--listing">
	<span class="rate__deposit-label rate__deposit-label--listing"><?php esc_html_e( 'Deposit required', 'wp-hotelier' ); ?></span>
	<span class="rate__deposit-amount rate__deposit-amount--listing"><?php echo esc_html( $variation->get_formatted_deposit() ); ?></span>
</div>

<?php endif; ?>
