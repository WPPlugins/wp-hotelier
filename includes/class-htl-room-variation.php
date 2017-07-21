<?php
/**
 * Room Variation Class.
 *
 * @author   Benito Lopez <hello@lopezb.com>
 * @category Class
 * @package  Hotelier/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HTL_Room_Variation' ) ) :

/**
 * HTL_Room_Variation Class
 */
class HTL_Room_Variation {

	/**
	 * The room (post) ID.
	 *
	 * @var int
	 */
	public $room_id = 0;

	/**
	 * $variation Stores variation data
	 *
	 * @var $variation
	 */
	public $variation = null;

	/**
	 * Get things going
	 */
	public function __construct( $variation, $room ) {
		$this->variation = $variation;

		if ( is_numeric( $room ) ) {
			$this->room_id = absint( $room );
		} elseif ( $room instanceof HTL_Room ) {
			$this->room_id = absint( $room->id );
		} elseif ( isset( $room->ID ) ) {
			$this->room_id = absint( $room->ID );
		}
	}

	/**
	 * Returns the variation's index.
	 *
	 * @return string room_index
	 */
	public function get_room_index() {
		$index = $this->variation[ 'index' ];

		return absint( $index );
	}

	/**
	 * Returns the variation's rate.
	 *
	 * @return string room_rate
	 */
	public function get_room_rate() {
		$room_rate = $this->variation[ 'room_rate' ];

		return $room_rate;
	}

	/**
	 * Returns the formatted variation's rate.
	 *
	 * @return string room_rate
	 */
	public function get_formatted_room_rate() {
		$room_rate = $this->get_room_rate();
		$room_rate = htl_get_formatted_room_rate( $room_rate );

		return $room_rate;
	}

	/**
	 * Returns the variation's description.
	 *
	 * @return string room_description
	 */
	public function get_room_description() {
		// We need to check if the term exists because the rate_name is stored
		// in a meta box (and we do not know if it still exists).
		$rate_name = $this->get_room_rate();

		$get_room_rates = get_terms( 'room_rate', 'hide_empty=0' );

		if ( empty( $get_room_rates ) || is_wp_error( $get_room_rates ) ) {
			// room_rate taxonomy empty
			$description = '';
		}

		$term = term_exists( $rate_name, 'room_rate' );

		if ( $term !== 0 && $term !== null ) {
			$description = term_description( $term[ 'term_id' ], 'room_rate' );
		} else {
			// room_rate taxonomy empty
			$description = '';
		}

		return $description;
	}

	/**
	 * Returns the variation's price type.
	 *
	 * @return string price_type
	 */
	public function get_price_type() {
		$price_type = $this->variation[ 'price_type' ];

		return $price_type;
	}

	/**
	 * Checks if the price is per day.
	 *
	 * @return bool
	 */
	public function is_price_per_day() {
		return $this->get_price_type() == 'per_day' ? true : false;
	}

	/**
	 * Checks if the variation has a seasonal price.
	 *
	 * @return bool
	 */
	public function has_seasonal_price() {
		return ( $this->get_price_type() == 'seasonal_price' ) ? true : false;
	}

	/**
	 * Returns the variation's regular price.
	 *
	 * @return mixed int price or false if there are none
	 */
	public function get_regular_price( $checkin, $checkout ) {
		$checkin   = new DateTime( $checkin );
		$checkout  = new DateTime( $checkout );
		// $checkout  = $checkout->modify( '+1 day' ); // include last day

		$interval  = new DateInterval( 'P1D' );
		$daterange = new DatePeriod( $checkin, $interval ,$checkout );

		$price = 0;

		if ( $this->is_price_per_day() ) {
			if ( $this->variation[ 'price_day' ] ) {
				// Different price for each day
				foreach( $daterange as $date ) {

					// 0 (for Sunday) through 6 (for Saturday)
					$day_index = $date->format( 'w' );

					// We need to sum the price of each day
					$price += $this->variation[ 'price_day' ][ $day_index ];
				}

			} else {
				// The room has a price per day but empty price
				$price = 0;
			}

		} else {
			// Same price for all days
			foreach( $daterange as $date ) {
				$price += $this->variation[ 'regular_price' ];
			}
		}

		if ( $price > 0 ) {

			$price = $price;
			return apply_filters( 'hotelier_get_variation_regular_price', $price, $this );

		} else {

			return false;

		}
	}

	/**
	 * Returns the variation's sale price.
	 *
	 * @return mixed int price or false if there are none
	 */
	public function get_sale_price( $checkin, $checkout ) {
		$checkin   = new DateTime( $checkin );
		$checkout  = new DateTime( $checkout );
		// $checkout  = $checkout->modify( '+1 day' ); // include last day

		$interval  = new DateInterval( 'P1D' );
		$daterange = new DatePeriod( $checkin, $interval ,$checkout );

		$price = 0;

		if ( $this->is_price_per_day() ) {
			if ( $this->variation[ 'sale_price_day' ] ) {
				// different price for each day
				foreach( $daterange as $date ) {

					// 0 (for Sunday) through 6 (for Saturday)
					$day_index = $date->format( 'w' );

					// We need to sum the price of each day
					$price += $this->variation[ 'sale_price_day' ][ $day_index ]; // Use integers
				}

			} else {
				// The room has a price per day but empty price
				$price = 0;
			}

		} else {
			// Same price for all days
			foreach( $daterange as $date ) {
				$price += $this->variation[ 'sale_price' ];
			}
		}

		if ( $price > 0 ) {

			$price = $price;
			return apply_filters( 'hotelier_get_variation_sale_price', $price, $this );

		} else {

			return false;

		}
	}

	/**
	 * Returns the variatio's seasonal price.
	 *
	 * @return mixed int price or false if there are none
	 */
	public function get_seasonal_price( $checkin, $checkout = false ) {
		$checkin   = new DateTime( $checkin );
		$checkout  = $checkout ? new DateTime( $checkout ) : $checkin;

		$interval  = new DateInterval( 'P1D' );
		$daterange = new DatePeriod( $checkin, $interval ,$checkout );

		$price = 0;
		$rules = htl_get_seasonal_prices_schema();

		if ( is_array( $rules ) ) {
			// Reverse the array, last rules have a higher precedence
			$rules = array_reverse( $rules );
		}

		foreach( $daterange as $date ) {
			$curr_date = $date->getTimestamp();

			if ( $rules ) {
				$has_seasonal_price = false;

				foreach ( $rules as $key => $rule ) {
					$begin = new DateTime( $rule[ 'from' ] );
					$end = new DateTime( $rule[ 'to' ] );

					if ( $curr_date >= $begin->getTimestamp() && $curr_date <= $end->getTimestamp() ) {

						if ( isset( $this->variation[ 'seasonal_price' ][ $rule[ 'index' ] ] ) ) {
							// Rule found, use seasonal price
							$price += $this->variation[ 'seasonal_price' ][ $rule[ 'index' ] ];
							$has_seasonal_price = true;
						}

						break;
					}
				}

				if ( ! $has_seasonal_price ) {
					// Rule not found, use default price
					$price += $this->variation[ 'seasonal_base_price' ];
				}
			}
		}

		if ( $price > 0 ) {
			return apply_filters( 'hotelier_get_variation_seasonal_price', $price, $this );

		} else {

			return false;

		}
	}

	/**
	 * Returns whether or not the variation is on sale.
	 *
	 * @return bool
	 */
	public function is_on_sale( $checkin, $checkout ) {
		$checkout = $checkout ? $checkout : $checkin;

		return apply_filters( 'hotelier_variation_is_on_sale', ( ! $this->has_seasonal_price() && $this->get_sale_price( $checkin, $checkout ) && $this->get_regular_price( $checkin, $checkout ) && $this->get_sale_price( $checkin, $checkout ) < $this->get_regular_price( $checkin, $checkout ) ), $this );
	}

	/**
	 * Returns the variation's price.
	 *
	 * @return mixed int price or false if there are none
	 */
	public function get_price( $checkin, $checkout = false ) {
		$checkout = $checkout ? $checkout : $checkin;

		if ( $this->has_seasonal_price() ) {
			$price = $this->get_seasonal_price( $checkin, $checkout );
		} else if ( $this->is_on_sale( $checkin, $checkout ) ) {
			$price = $this->get_sale_price( $checkin, $checkout );
		} else {
			$price = $this->get_regular_price( $checkin, $checkout );
		}

		return apply_filters( 'hotelier_variation_get_price', $price, $this );
	}

	/**
	 * Returns the variation's price in html format.
	 *
	 * @return string
	 */
	public function get_price_html( $checkin, $checkout ) {
		if ( $get_price = $this->get_price( $checkin, $checkout ) ) {

			if ( $this->is_on_sale( $checkin, $checkout ) ) {

				$from  = $this->get_regular_price( $checkin, $checkout ) / 100; // (prices are stored as integers)
				$to    = $this->get_sale_price( $checkin, $checkout ) / 100; // (prices are stored as integers)
				$price = sprintf( _x( 'Price: %s', 'price', 'wp-hotelier' ), $this->get_price_html_from_to( $from, $to ) );

				$price = apply_filters( 'hotelier_sale_price_html', $price, $this );

			} else {

				$price = htl_price( $get_price / 100 ); // (prices are stored as integers)
				$price = sprintf( _x( 'Price: %s', 'price', 'wp-hotelier' ), $price );
				$price = apply_filters( 'hotelier_get_price_html', $price, $this );

			}

		} else {

			$price = apply_filters( 'hotelier_empty_price_html', '', $this );

		}

		return $price;
	}

	/**
	 * Functions for getting parts of a price, in html, used by get_price_html.
	 *
	 * @param  string $from String or float to wrap with 'from' text
	 * @param  mixed $to String or float to wrap with 'to' text
	 * @return string
	 */
	public function get_price_html_from_to( $from, $to ) {
		$price = '<del>' . ( ( is_numeric( $from ) ) ? htl_price( $from ) : $from ) . '</del> <ins>' . ( ( is_numeric( $to ) ) ? htl_price( $to ) : $to ) . '</ins>';

		return apply_filters( 'hotelier_get_price_html_from_to', $price, $from, $to, $this );
	}

	/**
	 * Returns the low variation's price in html format.
	 *
	 * @return string
	 */
	public function get_min_price_html() {
		$min_price = 0;

		if ( $this->has_seasonal_price() ) {
			$prices = array();

			// seasonal price schema
			$rules = htl_get_seasonal_prices_schema();

			if ( is_array( $rules ) ) {
				// get variation seasonal prices
				$seasonal_prices = $this->variation[ 'seasonal_price' ];

				// check only the rules stored in the schema
				// we don't allow 'orphan' rules
				foreach ( $rules as $key => $value ) {

					// check if this rule has a price
					if ( isset( $this->variation[ 'seasonal_price' ][ $key ] ) ) {
						$prices[] = $this->variation[ 'seasonal_price' ][ $key ];
					}
				}

				// get also the default price
				$prices[] = $this->variation[ 'seasonal_base_price' ];
			}

			$min_price = min( $prices ) / 100; // (prices are stored as integers)

			if ( $min_price > 0 ) {
				$min_price = sprintf( __( 'Rates from %s per night', 'wp-hotelier' ), htl_price( $min_price ) );
			}

		} else if  ( $this->is_price_per_day() ) {
			if ( $this->variation[ 'sale_price_day' ] ) {
				$min_price = min( $this->variation[ 'sale_price_day' ] ) / 100; // (prices are stored as integers)

			} elseif ( $this->variation[ 'price_day' ] ) {
				$min_price = min( $this->variation[ 'price_day' ] ) / 100; // (prices are stored as integers)
			}

			if ( $min_price > 0 ) {
				$min_price = sprintf( __( 'Rates from %s per night', 'wp-hotelier' ), htl_price( $min_price ) );
			}

		} else {

			if ( $this->variation[ 'sale_price' ] ) {
				$min_price = $this->variation[ 'sale_price' ] / 100; // (prices are stored as integers)

			} elseif ( $this->variation[ 'regular_price' ] ) {
				$min_price = $this->variation[ 'regular_price' ] / 100; // (prices are stored as integers)
			}

			if ( $min_price > 0 ) {
				$min_price = sprintf( __( '%s per night', 'wp-hotelier' ), htl_price( $min_price ) );
			}
		}

		if ( $min_price === 0 ) {
			$min_price = apply_filters( 'hotelier_empty_price_html', '', $this );
		}

		return $min_price;

	}

	/**
	 * Returns the variation's conditions.
	 *
	 * @return array conditions
	 */
	public function get_room_conditions() {
		$room_conditions = array();

		// we just need a flat array to output the conditions on the front-end
		foreach ( $this->variation[ 'room_conditions' ] as $key => $value ) {
			if ( isset( $value[ 'name' ] ) ) {
				$room_conditions[] = $value[ 'name' ];
			}
		}

		$room_conditions = array_filter( $room_conditions );

		return apply_filters( 'hotelier_room_conditions', $room_conditions, $this );
	}

	/**
	 * Checks if the variation has some conditions.
	 *
	 * @return bool
	 */
	public function has_conditions() {
		$room_conditions = $this->get_room_conditions();

		return empty( $room_conditions ) ? false : true;
	}

	/**
	 * Checks if the variation requires a deposit.
	 *
	 * @return bool
	 */
	public function needs_deposit() {
		$require_deposit = isset( $this->variation[ 'require_deposit' ] ) ? $this->variation[ 'require_deposit' ] : false;

		return $require_deposit;
	}

	/**
	 * Returns the deposit amount
	 *
	 * @return int percentage of total price
	 */
	public function get_deposit() {
		$percentage = $this->needs_deposit() ? $this->variation[ 'deposit_amount' ] : 0;

		return $percentage;
	}

	/**
	 * Returns the deposit amount with percentage symbol
	 *
	 * @return int percentage of total price
	 */
	public function get_formatted_deposit() {
		$percentage = $this->get_deposit() . '%';

		return apply_filters( 'hotelier_room_deposit', $percentage, $this );
	}

	/**
	 * Checks if the variation is cancellable.
	 *
	 * @return bool
	 */
	public function is_cancellable() {
		$non_cancellable = isset( $this->variation[ 'non_cancellable' ] ) ? false : true;

		return $non_cancellable;
	}

	/**
	 * Get variation's required minimum nights
	 *
	 * @return int
	 */
	public function get_min_nights() {
		return apply_filters( 'hotelier_per_variation_minimum_nights', htl_get_option( 'booking_minimum_nights', 1 ), $this );
	}

	/**
	 * Get variation's required maximum nights
	 *
	 * @return int
	 */
	public function get_max_nights() {
		return apply_filters( 'hotelier_per_variation_maximum_nights', htl_get_option( 'booking_maximum_nights', 0 ), $this );
	}
}

endif;
