<?php
/*

Translatable Friendly Dates for WordPress
Written as part of Foo Plugin Base plugin framework
Original concept taken from Invent Partners - http://www.inventpartners.com

Example :

 $instance = new Foo_Friendly_Dates( 'plugin_text_domain' );
 echo $instance->friendly_date( '22 May 2030' );
 echo $instance->friendly_date( 1403533149 );

*/
if ( !class_exists( 'Foo_Friendly_Dates_v1' ) ) {

	if ( !function_exists( '__' ) ) {
		function __( $text, $domain = '' ) {
			return $text;
		}
	}

	class Foo_Friendly_Dates_v1 {

		private $current_timestamp;
		private $current_timestamp_day;
		private $text_domain;

		const DAY = 86400;	//60 * 60 * 24
		const WEEK = 604800; //60 * 60 * 24 * 7

		public function __construct( $text_domain = '' ) {
			$this->text_domain = $text_domain;

			$this->set_compare_timestamp( time() );

//			$this->event_timestamp     = $event_timestamp;
//			$this->event_timestamp_day = mktime( 0, 0, 0, $month = date( "n", $event_timestamp ), $day = date( "j", $event_timestamp ), date( "Y", $event_timestamp ) );
		}

		/**
		 * Allow the default compare timestamp to be overridden
		 * @param $compare_timestamp
		 */
		public function set_compare_timestamp( $compare_timestamp ) {
			$this->current_timestamp     = $compare_timestamp;
			$this->current_timestamp_day = mktime( 0, 0, 0, date( 'n', $compare_timestamp ), date( 'j', $compare_timestamp ), date( 'Y', $compare_timestamp ) );
		}

		/**
		 * Return a friendly date compared to the current date
		 *
		 * @param $timestamp string|int 	The timestamp we want return a friendly date for
		 *
		 * @return string 					A friendly date
		 */
		public function friendly_date( $timestamp ) {

			//check for empty, zero or null
			if ( empty( $timestamp ) ) return '';

			$event_timestamp     = is_string( $timestamp ) ? strtotime( $timestamp ) : $timestamp;
			$event_timestamp_day = mktime( 0, 0, 0, date( "n", $event_timestamp ), date( "j", $event_timestamp ), date( "Y", $event_timestamp ) );

			//check for today
			if ( $event_timestamp_day === $this->current_timestamp_day ) {
				return 'today';
			}

			$diff = $event_timestamp_day - $this->current_timestamp_day;

			// Future events
			if ( $diff > 0 ) {
				// Tomorrow
				if ( $diff >= self::DAY && $diff < (self::DAY * 2) ) {
					return 'tomorrow';
				}

				//calculate days
				$days = intval( $diff / self::DAY );

				//less then a week, return number of days
				if ( $days < 7 ) {
					return "in $days days";
				}

				//calculate weeks
				$weeks = intval( $diff / self::WEEK );

				if ( $weeks == 1 ) {
					return 'next week';
				} else if ( $weeks < 4 ) {
					return "in $weeks weeks";
				}

				//calculate the event month
				$event_month   = intval( (date( 'Y', $event_timestamp_day ) * 12) + date( 'm', $event_timestamp_day ) );

				//calculate the current month
				$current_month = intval( (date( 'Y', $this->current_timestamp_day ) * 12) + date( 'm', $this->current_timestamp_day ) );

				//calculate month difference
				$months = abs( $event_month - $current_month );

				if ( $months == 0 ) {
					return "in a month";
				} else if ( $months == 1 ) {
					return "in over a month";
				} else if ( $months < 12 ) {
					return "in $months months";
				}

				//calculate years
				$years = $months / 12;

				if ( $years == 1 ) {
					return "in a year";
				} else if ( $years < 2 ) {
					return "in over a year";
				}

				return "in $years years";

			}

			return 'unknown';
		}

		protected function calcMonthDiff() {

			$event_month   = intval( (date( 'Y', $this->event_timestamp_day ) * 12) + date( 'm', $this->event_timestamp_day ) );
			$current_month = intval( (date( 'Y', $this->current_timestamp_day ) * 12) + date( 'm', $this->current_timestamp_day ) );
			$month_diff    = abs( $event_month - $current_month );

			return $month_diff;

		}
	}
}