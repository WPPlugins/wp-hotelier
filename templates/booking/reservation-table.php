<?php
/**
 * Booking table
 *
 * This template can be overridden by copying it to yourtheme/hotelier/booking/reservation-table.php.
 *
 * @author  Benito Lopez <hello@lopezb.com>
 * @package Hotelier/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="reservation-table" class="booking__section booking__section--reservation-table">

	<header class="section-header">
		<h3 class="section-header__title"><?php esc_html_e( 'Your reservation', 'wp-hotelier' ); ?></h3>
	</header>

	<?php do_action( 'hotelier_booking_before_booking_table' ); ?>

	<table class="table table--reservation-table reservation-table hotelier-table">
		<thead class="reservation-table__heading">
			<tr class="reservation-table__row reservation-table__row--heading">
				<th class="reservation-table__room-name reservation-table__room-name--heading"><?php esc_html_e( 'Room', 'wp-hotelier' ); ?></th>
				<th class="reservation-table__room-qty reservation-table__room-qty--heading"><?php esc_html_e( 'Qty', 'wp-hotelier' ); ?></th>
				<th class="reservation-table__room-cost reservation-table__room-cost--heading"><?php esc_html_e( 'Cost', 'wp-hotelier' ); ?></th>
			</tr>
		</thead>
		<tbody class="reservation-table__body">
			<?php
				foreach ( HTL()->cart->get_cart() as $cart_item_key => $cart_item ) :
					$_room    = $cart_item[ 'data' ];
					$_room_id = $cart_item[ 'room_id' ];

					if ( $_room && $_room->exists() && $cart_item[ 'quantity' ] > 0 ) : ?>

						<tr class="reservation-table__row reservation-table__row--body">
							<td class="reservation-table__room-name reservation-table__room-name--body">
								<a class="reservation-table__room-link" href="<?php echo esc_url( get_permalink( $_room_id ) ); ?>"><?php echo esc_html( $_room->get_title() ); ?></a>

								<?php if ( $cart_item[ 'rate_name' ] ) : ?>
									<small class="reservation-table__room-rate"><?php printf( esc_html__( 'Rate: %s', 'wp-hotelier' ), htl_get_formatted_room_rate( $cart_item[ 'rate_name' ] ) ); ?></small>
								<?php endif; ?>

								<?php if ( ! $cart_item[ 'is_cancellable' ] ) : ?>
									<span class="reservation-table__room-non-cancellable"><?php echo esc_html_e( 'Non-refundable', 'wp-hotelier' ); ?></span>
								<?php endif; ?>
							</td>

							<td class="reservation-table__room-qty reservation-table__room-qty--body"><?php echo absint( $cart_item[ 'quantity' ] ); ?></td>

							<td class="reservation-table__room-cost reservation-table__room-cost--body">
								<?php echo HTL()->cart->get_room_price( $cart_item[ 'total' ] ); ?>

								<?php if ( $nights > 1 ) : ?>
								<a class="view-price-breakdown" href="#<?php echo esc_attr( htl_generate_item_key( $cart_item[ 'room_id' ], $cart_item[ 'rate_id' ] ) ); ?>" data-closed="<?php esc_html_e( 'View price breakdown', 'wp-hotelier' ); ?>" data-open="<?php esc_html_e( 'Hide price breakdown', 'wp-hotelier' ); ?>"><?php esc_html_e( 'View price breakdown', 'wp-hotelier' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>

						<?php if ( $nights > 1 ) : ?>
						<tr class="reservation-table__row reservation-table__row--body reservation-table__row--price-breakdown">
							<td colspan="3" class="price-breakdown-wrapper">
								<?php echo htl_cart_price_breakdown( HTL()->session->get( 'checkin' ), HTL()->session->get( 'checkout' ), $cart_item[ 'room_id' ], $cart_item[ 'rate_id' ], $cart_item[ 'quantity' ] ); ?>
							</td>
						</tr>
						<?php endif;

					endif;
				endforeach;
			?>

		</tbody>
		<tfoot class="reservation-table__footer">
			<?php
				if ( HTL()->cart->needs_payment() ) : ?>

					<tr class="reservation-table__row reservation-table__row--footer">
						<th colspan="2" class="reservation-table__label reservation-table__label--total"><?php esc_html_e( 'Total:', 'wp-hotelier' ); ?></th>
						<td class="reservation-table__data reservation-table__data--total"><strong><?php echo htl_cart_formatted_total(); ?></strong></td>
					</tr>

					<?php if ( htl_get_option( 'booking_mode' ) == 'instant-booking' ) : ?>

						<tr class="reservation-table__row reservation-table__row--footer">
							<th colspan="2" class="reservation-table__label reservation-table__label--total reservation-table__label--deposit"><?php esc_html_e( 'Deposit due now:', 'wp-hotelier' ); ?></th>
							<td class="reservation-table__data reservation-table__data--total reservation-table__data--deposit"><strong><?php echo htl_cart_formatted_required_deposit(); ?></strong></td>
						</tr>

					<?php else : ?>

						<tr class="reservation-table__row reservation-table__row--footer">
							<th colspan="2" class="reservation-table__label reservation-table__label--total reservation-table__label--deposit"><?php esc_html_e( 'Deposit due after confirm:', 'wp-hotelier' ); ?></th>
							<td class="reservation-table__data reservation-table__data--total reservation-table__data--deposit"><strong><?php echo htl_cart_formatted_required_deposit(); ?></strong></td>
						</tr>

					<?php endif; ?>

				<?php else : ?>

					<tr class="reservation-table__row reservation-table__row--footer">
						<th colspan="2" class="reservation-table__label reservation-table__label--total"><?php esc_html_e( 'Total:', 'wp-hotelier' ); ?></th>
						<td class="reservation-table__data reservation-table__data--total"><strong><?php echo htl_cart_formatted_total(); ?></strong></td>
					</tr>

				<?php endif;
			?>
		</tfoot>
	</table>

	<?php if ( ! HTL()->cart->is_cancellable() ) : ?>
		<div class="reservation-non-cancellable-disclaimer">
			<p class="reservation-non-cancellable-disclaimer__text">
				<?php esc_html_e( 'This reservation includes a non-cancellable and non-refundable room. You will be charged the total price if you cancel your booking.', 'wp-hotelier' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<?php do_action( 'hotelier_booking_after_booking_table' ); ?>

</div>
