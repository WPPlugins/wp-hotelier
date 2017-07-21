<?php
/**
 * Guest invoice email (HTML)
 *
 * This template can be overridden by copying it to yourtheme/hotelier/emails/guest-invoice.php
 *
 * @author  Benito Lopez <hello@lopezb.com>
 * @package Hotelier/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php do_action( 'hotelier_email_header', $reservation, $email_heading ); ?>

<?php if ( $reservation->has_status( 'pending' ) ) :

	if ( $reservation->needs_payment() ) : ?>
		<tr>
			<td style="text-align:left;font-size:15px;line-height:19px;color:#999999;font-family:Helvetica,Arial;"><?php printf( esc_html__( 'Hello %s, a reservation has been created for you on %s. To pay for this reservation please use the button below.', 'wp-hotelier' ), $reservation->get_formatted_guest_full_name(), get_bloginfo( 'name', 'display' ) ); ?></td>
		</tr>
	<?php else : ?>
		<tr>
			<td style="text-align:left;font-size:15px;line-height:19px;color:#999999;font-family:Helvetica,Arial;"><?php printf( esc_html__( 'Hello %s, a reservation has been created for you on %s.', 'wp-hotelier' ), $reservation->get_formatted_guest_full_name(), get_bloginfo( 'name', 'display' ) ); ?></td>
		</tr>
	<?php endif; ?>

<?php endif; ?>
<tr>
	<td style="border-bottom: solid 1px #e9e9e9; background: #ffffff" bgcolor="ffffff" width="100%">&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>

<?php do_action( 'hotelier_email_hotel_info' ); ?>

<tr>
	<td style="text-align:left;font-size:16px;line-height:20px;color:#444444;font-weight:bold;font-family:Helvetica,Arial;"><?php printf( esc_html__( 'Reservation number: %s', 'wp-hotelier' ), $reservation->get_reservation_number() ) ?></td>
</tr>
<tr>
	<td style="text-align:left;font-size:13px;line-height:17px;color:#999999;font-family:Helvetica,Arial;"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $reservation->reservation_date ) ) ?></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td style="text-align:left;font-size:14px;line-height:20px;color:#999999;"><strong style="color:#444444;font-family:Helvetica,Arial;"><?php esc_html_e( 'Check-in:', 'wp-hotelier' ); ?></strong> <?php echo esc_html( $reservation->get_formatted_checkin() ); ?> (<?php echo esc_html( HTL_Info::get_hotel_checkin() ); ?>)</td>
</tr>
<tr>
	<td style="text-align:left;font-size:14px;line-height:20px;color:#999999;font-family:Helvetica,Arial;"><strong style="color:#444444;"><?php esc_html_e( 'Check-out:', 'wp-hotelier' ); ?></strong> <?php echo esc_html( $reservation->get_formatted_checkout() ); ?> (<?php echo esc_html( HTL_Info::get_hotel_checkout() ); ?>)</td>
</tr>
<tr>
	<td style="text-align:left;font-size:14px;line-height:20px;color:#999999;font-family:Helvetica,Arial;"><strong style="color:#444444;"><?php esc_html_e( 'Nights:', 'wp-hotelier' ); ?></strong> <?php echo esc_html( $reservation->get_nights() ); ?></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" style="margin:0px;padding:0px;border:0px;margin:0;padding:0;font-family:Helvetica,Arial;">
			<thead>
				<tr>
					<th scope="col" style="text-align:left;font-size:16px;line-height:20px;color:#444444;border-bottom: solid 1px #e9e9e9;padding-top:10px;padding-bottom:10px;padding-left:0;padding-right:0;font-family:Helvetica,Arial;"><?php esc_html_e( 'Room', 'wp-hotelier' ); ?></th>
					<th scope="col" style="text-align:left;font-size:16px;line-height:20px;color:#444444;border-bottom: solid 1px #e9e9e9;padding-top:10px;padding-bottom:10px;padding-left:0;padding-right:0;font-family:Helvetica,Arial;"><?php esc_html_e( 'Qty', 'wp-hotelier' ); ?></th>
					<th scope="col" style="text-align:left;font-size:16px;line-height:20px;color:#444444;border-bottom: solid 1px #e9e9e9;padding-top:10px;padding-bottom:10px;padding-left:0;padding-right:0;font-family:Helvetica,Arial;"><?php esc_html_e( 'Cost', 'wp-hotelier' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $reservation->email_reservation_items_table(); ?>
			</tbody>
			<tfoot>
				<?php
				if ( $totals = $reservation->get_totals_before_booking() ) {
					$count_totals = count( $totals );
					$i = 1;

					foreach ( $totals as $total ) {
						if ( $i == 1 ) {
							$padding = 'padding-top:10px;padding-bottom:5px;';
							$border = 'border-top: solid 1px #e9e9e9;';
						} elseif ( $i == $count_totals ) {
							$padding = 'padding-top:5px;padding-bottom:10px;';
							$border = 'border-bottom: solid 1px #e9e9e9;';
						} else {
							$padding = 'padding-top:5px;padding-bottom:5px;';
							$border = '';
						}
						?>
						<tr>
							<th scope="row" colspan="2" style="text-align:left;font-size:14px;line-height:20px;color:#444444;<?php echo $padding; ?>padding-left:0;padding-right:0;<?php echo $border; ?>font-family:Helvetica,Arial;"><?php echo esc_html( $total[ 'label' ] ); ?></th>
							<td style="text-align:left;font-size:14px;line-height:20px;color:#999999;padding-top:10px;padding-bottom:5px;padding-left:0;padding-right:0;<?php echo $border; ?>font-family:Helvetica,Arial;"><?php echo $total[ 'value' ]; ?></td>
						</tr>
						<?php
						$i++;
					}
				}
				?>
			</tfoot>
		</table>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>

<?php if ( ! $reservation->can_be_cancelled() ) : ?>
<tr>
	<td style="padding-top:20px;padding-bottom:0;padding-left:0;padding-right:0;text-align:left;font-size:12px;line-height:17px;color:#999999;font-family:Helvetica,Arial;"><?php esc_html_e( 'This reservation includes a non-cancellable and non-refundable room. You will be charged the total price if you cancel your booking.', 'wp-hotelier' ); ?></td>
</tr>
<?php endif; ?>

<?php if ( $reservation->needs_payment() ) : ?>
	<tr>
		<td style="padding-top:20px;padding-bottom:25px;padding-left:0;padding-right:0;"><a href="<?php echo esc_url( $reservation->get_booking_payment_url() ); ?>" style="text-align:left;font-size:14px;line-height:20px;color:#ffffff;padding-top:12px;padding-bottom:12px;padding-left:25px;padding-right:25px;background-color:#5CC8FF;text-decoration:none;border-radius:10px;display:inline-block;font-family:Helvetica,Arial;"><?php esc_html_e( 'Pay deposit', 'wp-hotelier' ); ?></a></td>
	</tr>
<?php endif; ?>

<?php do_action( 'hotelier_email_guest_details', $reservation, $sent_to_admin ); ?>

<?php do_action( 'hotelier_email_reservation_meta', $reservation, $sent_to_admin ); ?>

<?php do_action( 'hotelier_email_footer' ); ?>
