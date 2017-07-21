<?php
/**
 * Post Types Admin.
 *
 * @author   Benito Lopez <hello@lopezb.com>
 * @category Admin
 * @package  Hotelier/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HTL_Admin_Post_Types' ) ) :

/**
 * HTL_Admin_Post_Types Class
 *
 * Handles the functionality on the edit post screen for Hotelier post types.
 */
class HTL_Admin_Post_Types {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu',  array( $this, 'remove_meta_boxes' ) );

		// Disable Auto Save
		add_action( 'admin_print_scripts', array( $this, 'disable_autosave' ) );

		// Room post type columns
		add_filter( 'manage_room_posts_columns', array( $this, 'room_columns' ) );
		add_filter( 'manage_room_posts_custom_column', array( $this, 'render_room_columns' ) );

		// Reservation post type columns
		add_filter( 'manage_room_reservation_posts_columns', array( $this, 'reservation_columns' ) );
		add_filter( 'manage_room_reservation_posts_custom_column', array( $this, 'render_room_reservation_columns' ) );

		// Reservation post type row actions
		add_filter( 'post_row_actions', array( $this, 'delete_actions' ) );

		// Action during room trash
		add_action( 'wp_trash_post', array( $this, 'trash_room' ) );

		// Actions during reservation trash/untrash/deletion
		add_action( 'untrashed_post', array( $this, 'untrash_reservation' ) );
		add_action( 'wp_trash_post', array( $this, 'trash_reservation' ) );
		add_action( 'before_delete_post', array( $this, 'delete_reservation_items' ) );

		// Remove date filter on reservations
		add_action( 'admin_head', array( $this, 'remove_date_filter' ) );

		$this->includes();
	}

	/**
	 * Remove postboxes on edit screen.
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'tagsdiv-room_rate', 'room', 'side' );
		remove_meta_box( 'submitdiv', 'room_reservation', 'side' );
		remove_meta_box( 'slugdiv', 'room_reservation', 'side' );
		remove_meta_box( 'commentsdiv', 'room_reservation', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'room_reservation', 'normal' );
	}

	/**
	 * Disable the auto-save functionality for Reservations.
	 */
	public function disable_autosave() {
		global $post;

		if ( $post && ( get_post_type( $post->ID ) == 'room_reservation' ) ) {
			wp_dequeue_script( 'autosave' );
		}
	}

	/**
	 * Include required files used in admin by post types.
	 */
	private function includes() {
		include_once( 'meta-boxes/class-htl-admin-meta-boxes.php' );
	}

	/**
	 * Define custom columns for rooms.
	 */
	public function room_columns( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		unset( $existing_columns[ 'comments' ], $existing_columns[ 'date' ] );

		$columns               = array();
		$columns[ 'guests' ]   = esc_html__( 'Guests', 'wp-hotelier' );
		$columns[ 'type' ]     = esc_html__( 'Room Type', 'wp-hotelier' );
		$columns[ 'quantity' ] = esc_html__( 'Available Today', 'wp-hotelier' );
		$columns[ 'date' ]     = esc_html__( 'Date', 'wp-hotelier' );

		return array_merge( $existing_columns, $columns );
	}

	/**
	 * Ouput custom columns for rooms.
	 */
	public function render_room_columns( $column ) {
		global $post, $the_room;

		if ( empty( $the_room ) || $the_room->id != $post->ID ) {
			$the_room = htl_get_room( $post );
		}

		$today              = date( 'Y-m-d' );
		$available_rooms    = absint( $the_room->get_available_rooms( $today ) );
		$low_room_threshold = htl_get_option( 'low_room_threshold', 2 );

		switch ( $column ) {
			case 'guests' :
				echo absint( $the_room->get_max_guests() );
				break;

			case 'type' :
				echo esc_html( $the_room->get_room_type_formatted() );
				break;

			case 'quantity' :

				if ( $available_rooms > 0 ) {
					echo ( $available_rooms <= $low_room_threshold ) ? '<mark class="only-x-left">' : '<mark>';

					echo sprintf( _n( '%s room left', '%s rooms left', absint( $available_rooms ), 'wp-hotelier' ), $available_rooms );

					echo '</mark>';
				} else {
					echo '<mark class="not-left">' . esc_html__( 'No rooms left!', 'wp-hotelier' ) . '</mark>';
				}
 				break;

			default :
				break;
		}
	}

	/**
	 * Define custom columns for reservations.
	 */
	public function reservation_columns( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		unset( $existing_columns[ 'title' ], $existing_columns[ 'title' ] );
		unset( $existing_columns[ 'comments' ], $existing_columns[ 'date' ] );

		$columns                = array();
		$columns[ 'title' ]     = esc_html__( 'Guest', 'wp-hotelier' );
		$columns[ 'nights' ]    = esc_html__( 'Nights', 'wp-hotelier' );
		$columns[ 'check_in' ]  = esc_html__( 'Check-in', 'wp-hotelier' );
		$columns[ 'check_out' ] = esc_html__( 'Check-out', 'wp-hotelier' );
		$columns[ 'date' ]      = esc_html__( 'Date', 'wp-hotelier' );
		$columns[ 'status' ]    = esc_html__( 'Status', 'wp-hotelier' );

		return array_merge( $existing_columns, $columns );
	}

	/**
	 * Ouput custom columns for reservations.
	 */
	public function render_room_reservation_columns( $column ) {
		global $post, $the_reservation;

		if ( empty( $the_reservation ) || $the_reservation->id != $post->ID ) {
			$the_reservation = htl_get_reservation( $post );
		}

		switch ( $column ) {
			case 'nights' :
				echo '<span>' . absint( $the_reservation->get_nights() ) . '</span>';
				break;

			case 'check_in' :
				echo '<span>' . esc_html( $the_reservation->get_formatted_checkin() ) . '</span>';
				break;

			case 'check_out' :
				echo '<span>' . esc_html( $the_reservation->get_formatted_checkout() ) . '</span>';
				break;

			case 'status' :
				echo '<span class="' . esc_html( $the_reservation->get_status() ) . ' hastip" title="' . esc_html( htl_get_reservation_status_name( $the_reservation->get_status() ) ) . '">' . esc_html( $the_reservation->get_status() ) . '</span>';
				break;

			default :
				break;
		}
	}

	/**
	 * Delete unused actions.
	 */
	public function delete_actions( $actions ) {
		if ( get_post_type() === 'room_reservation' ) {

			unset( $actions[ 'trash' ] );
			unset( $actions[ 'inline hide-if-no-js' ] );
		}

		return $actions;
	}

	/**
	 * Check rooms availability when a reservation is restored.
	 */
	public function untrash_reservation( $postid ) {
		// When a reservation is restored from trash, we need to ensure that the rooms are still available on the given dates. If not, change the status to 'cancelled'.
		if ( get_post_type() === 'room_reservation' ) {

			$reservation = htl_get_reservation( $postid );
			$old_status  = get_post_meta( $postid, '_wp_trash_meta_status', true );
			$checkin     = $reservation->guest_checkin;
			$checkout    = $reservation->guest_checkout;
			$items       = $reservation->get_items();

			$ret = true;

			foreach ( $items as $item ) {
				$_room = $reservation->get_room_from_item( $item );
				$qty   = $item[ 'qty' ];

				if ( ! $_room || ! $_room->exists() || $_room->post->post_status == 'trash' ) {
					$ret = false;
				}

				if ( ! $_room->is_available( $checkin, $checkout, $qty ) ) {
					$ret = false;
				}
			}

			if ( ! $ret ) {
				// One or more rooms are not available anymore. Change status to cancelled.
				$reservation->update_status( 'cancelled', esc_html__( 'This reservation cannot be restored because one or more rooms are no longer available on those dates.', 'wp-hotelier' ) );
			} else {
				// The reservation can be restored. Update the 'bookings' table to the old status
				$reservation->update_table_status( $old_status );
			}
		}
	}

	/**
	 * Change reservation to 'cancelled' in 'bookings' table when the post is trashed.
	 */
	public function trash_reservation( $postid ) {
		if ( get_post_type() === 'room_reservation' ) {

			$reservation = htl_get_reservation( $postid );
			$reservation->update_table_status( 'cancelled' );
		}
	}

	/**
	 * Remove reservation meta on permanent deletion.
	 */
	public function delete_reservation_items( $postid ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM itemmeta USING {$wpdb->prefix}hotelier_reservation_itemmeta itemmeta INNER JOIN {$wpdb->prefix}hotelier_reservation_items items WHERE itemmeta.reservation_item_id = items.reservation_item_id and items.reservation_id = %d", $postid ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}hotelier_reservation_items WHERE reservation_id = %d", $postid ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}hotelier_rooms_bookings WHERE reservation_id = %d", $postid ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}hotelier_bookings WHERE reservation_id = %d", $postid ) );
	}

	/**
	 * Delete `hotelier_room_ids` transient when the post is trashed.
	 */
	public function trash_room( $postid ) {
		if ( get_post_type() === 'room' ) {

			delete_transient( 'hotelier_room_ids' );
		}
	}

	/**
	 * Remove date filter on reservations
	 */
	public function remove_date_filter( $postid ) {
		$screen = get_current_screen();

		if ( 'room_reservation' == $screen->post_type ) {
			add_filter( 'months_dropdown_results', '__return_empty_array' );
		}
	}
}

endif;

new HTL_Admin_Post_Types();
