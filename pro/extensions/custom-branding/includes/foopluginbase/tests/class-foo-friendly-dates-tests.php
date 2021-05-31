<?php
/**
 * Created by brad.
 * Date: 2014-05-23
 */

require_once dirname(dirname(__FILE__)).'\classes\class-foo-friendly-dates.php';

class Foo_Friendly_Dates_v1Test extends PHPUnit_Framework_TestCase {

	/**
	 * @var Foo_Friendly_Dates_v1
	 */
	protected $instance;
	const TODAY_STRING = '1 Jan 2014';
	const TODAY_TIMESTAMP = 1388577600;

	protected function setUp() {
		$this->instance = new Foo_Friendly_Dates_v1();
		$this->instance->set_compare_timestamp( self::TODAY_TIMESTAMP );
	}

	public function test_friendly_date_given_null_returns_empty_string() {
		$expected = '';
		$actual = $this->instance->friendly_date( NULL );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_empty_string_returns_empty_string() {
		$expected = '';
		$actual = $this->instance->friendly_date( '' );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_zero_returns_empty_string() {
		$expected = '';
		$actual = $this->instance->friendly_date( 0 );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_bad_date_returns_unknown_string() {
		$expected = 'unknown';
		$actual = $this->instance->friendly_date( 'ABC' );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_today_string_returns_today_string() {
		$expected = 'today';
		$actual = $this->instance->friendly_date( self::TODAY_STRING  );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_today_timestamp_returns_today_string() {
		$expected = 'today';
		$actual = $this->instance->friendly_date( self::TODAY_TIMESTAMP  );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_24_hours_returns_tomorrow_string() {
		$expected = 'tomorrow';

		$date = strtotime( '+24 hours', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_48_hours_returns_in2days_string() {
		$expected = 'in 2 days';

		$date = strtotime( '+48 hours', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_36_hours_returns_in3days_string() {
		$expected = 'in 3 days';

		$date = strtotime( '+72 hours', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_1_week_returns_nextweek_string() {
		$expected = 'next week';

		$date = strtotime( '+1 week', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_2_week_returns_in2weeks_string() {
		$expected = 'in 2 weeks';

		$date = strtotime( '+2 weeks', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_3_week_returns_in3weeks_string() {
		$expected = 'in 3 weeks';

		$date = strtotime( '+3 weeks', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_4_week_returns_inamonth_string() {
		$expected = 'in a month';

		$date = strtotime( '+4 weeks', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_5_weeks_returns_in2month_string() {
		$expected = 'in over a month';

		$date = strtotime( '+5 weeks', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_6_months_returns_in6months_string() {
		$expected = 'in 6 months';

		$date = strtotime( '+6 months', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_12_months_returns_inayear_string() {
		$expected = 'in a year';

		$date = strtotime( '+12 months', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_13_months_returns_inoverayear_string() {
		$expected = 'in over a year';

		$date = strtotime( '+13 months', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_24_months_returns_in2years_string() {
		$expected = 'in 2 years';

		$date = strtotime( '+24 months', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}

	public function test_friendly_date_given_date_plus_5years_returns_in5years_string() {
		$expected = 'in 5 years';

		$date = strtotime( '+5 years', self::TODAY_TIMESTAMP );

		$actual = $this->instance->friendly_date( $date );

		$this->assertEquals( $expected, $actual );
	}
}
