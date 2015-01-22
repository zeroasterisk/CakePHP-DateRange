<?php
/* Unit Test for DateRange */
App::uses('DateRange', 'Lib');
App::uses('AppTestCase','Lib');

/**
 * mock for class - internal method testing
 */
class DateRangeMock extends DateRange {
	public function __construct() {
	}
}

/**
 * Test Class
 */
class DateRangeTest extends AppTestCase {
	/**
	 * Autoset needed fixtures by Group
	 */
	public $fixtureGroups = array();

	/**
	 * Fixtures to load for this test case
	 */
	public $fixtures = array();

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		ClassRegistry::flush();
		parent::tearDown();
	}

	public function testConstruct() {

	}

	public function testSetStart() {
		$o = new DateRangeMock();
		$this->assertNull($o->getStart());

		$return = $o->setStart('2015-01-01 11:11:11');
		$this->assertTrue(is_object($return));

		$this->assertEqual(
			$o->getStart(),
			new DateTime('2015-01-01 11:11:11')
		);

		$return = $o->setStart('today');
		$this->assertTrue(is_object($return));

		$expect = new DateTime('today');
		$expect->setTime(00, 00, 00);
		$this->assertEqual(
			$o->getStart(),
			$expect
		);
	}

	public function testSetEnd() {
		$o = new DateRangeMock();
		$this->assertNull($o->getEnd());

		$return = $o->setEnd('2015-01-01 11:11:11');
		$this->assertTrue(is_object($return));

		$this->assertEqual(
			$o->getEnd(),
			new DateTime('2015-01-01 11:11:11')
		);

		$return = $o->setEnd('today');
		$this->assertTrue(is_object($return));

		$expect = new DateTime('today');
		$expect->setTime(23, 59, 59);
		$this->assertEqual(
			$o->getEnd(),
			$expect
		);
	}

	public function testSetTimezone() {
		$o = new DateRangeMock();
		$this->assertEqual(
			$o->setTimezone('America/New_York')->buildDate('2015-01-01 11:11:11')->format('Y-m-d H:i:sP'),
			'2015-01-01 11:11:11-05:00'
		);
		$this->assertEqual(
			$o->setTimezone('Pacific/Chatham')->buildDate('2015-01-01 11:11:11')->format('Y-m-d H:i:sP'),
			'2015-01-01 11:11:11+13:45'
		);
		$this->assertEqual(
			$o->setTimezone('Pacific/Nauru')->buildDate('2015-01-01 11:11:11')->format('Y-m-d H:i:sP'),
			'2015-01-01 11:11:11+12:00'
		);

		// note - this does not adjust an existing date
		//   it only sets the timezone for the NEXT buildDate()
		$o->setTimezone('America/New_York');
		$o->setStart('2015-01-01 11:11:11');
		$o->setTimezone('Pacific/Nauru');
		$this->assertEqual(
			$o->getStart()->format('Y-m-d H:i:sP'),
			'2015-01-01 11:11:11-05:00'  // still in New_York
		);

	}

	public function testBuildDate() {
		$o = new DateRangeMock();
		// now includes a time
		$this->assertEqual(
			$o->buildDate('now'),
			new DateTime(date('Y-m-d H:i:s'))
		);
		// today does not (which means it can be assigned default times)
		$this->assertEqual(
			$o->buildDate('today'),
			new DateTime(date('Y-m-d') . ' 00:00:00')
		);
		// basic epoch input tests
		$this->assertEqual(
			$o->buildDate(strtotime('2014-01-01 00:00:00')),
			new DateTime('2014-01-01 00:00:00')
		);
		// basic date input tests
		$this->assertEqual(
			$o->buildDate('2014-01-01 00:00:00'),
			new DateTime('2014-01-01 00:00:00')
		);
		$this->assertEqual(
			$o->buildDate('2014-01-01'),
			new DateTime('2014-01-01 00:00:00')
		);
		$this->assertEqual(
			$o->buildDate('2014-01-01', '00:00:00'),
			new DateTime('2014-01-01 00:00:00')
		);
		// setting a default time (ignored because time was passed in)
		$this->assertEqual(
			$o->buildDate('2014-01-01 01:00:00', '23:59:59'),
			new DateTime('2014-01-01 01:00:00')
		);
		// setting a default time (assigned because no time was passed in)
		$this->assertEqual(
			$o->buildDate('2014-01-01', '23:59:59'),
			new DateTime('2014-01-01 23:59:59')
		);
		// setting a default time (assigned because time passed in was 00:00:00)
		//   GOTCHA!
		$this->assertEqual(
			$o->buildDate('2014-01-01 00:00:00', '23:59:59'),
			new DateTime('2014-01-01 23:59:59')
		);

		// setTimezone() first - inherits
		$o->setTimezone('Pacific/Chatham');
		$this->assertEqual(
			$o->buildDate('2015-01-01 11:11:11')->format('Y-m-d H:i:sP'),
			'2015-01-01 11:11:11+13:45'
		);
	}

	public function testAdjustTimezone() {
		$o = new DateRange('2014-01-01', '2014-01-31', 'America/New_York');
		$this->assertEqual(
			$o->getStart()->format('Y-m-d H:i:sP'),
			'2014-01-01 00:00:00-05:00'
		);
		$this->assertEqual(
			$o->getEnd()->format('Y-m-d H:i:sP'),
			'2014-01-31 23:59:59-05:00'
		);

		// moving from -5 --> +13:45 (+18:45)
		$o->adjustTimezone('Pacific/Chatham');

		$this->assertEqual(
			$o->getStart()->format('Y-m-d H:i:sP'),
			'2014-01-01 18:45:00+13:45'
		);
		$this->assertEqual(
			$o->getEnd()->format('Y-m-d H:i:sP'),
			'2014-02-01 18:44:59+13:45'
		);
	}

	public function testAdjustTimesLitle() {
		$o = new DateRange('2014-01-01', '2014-01-31');
		$this->assertEqual(
			$o->getStart()->format('Y-m-d H:i:s'),
			'2014-01-01 00:00:00'
		);
		$this->assertEqual(
			$o->getEnd()->format('Y-m-d H:i:s'),
			'2014-01-31 23:59:59'
		);

		$o->adjustTimes('litle');

		$this->assertEqual(
			$o->getStart()->format('Y-m-d H:i:s'),
			'2013-12-31 21:00:00'
		);
		$this->assertEqual(
			$o->getEnd()->format('Y-m-d H:i:s'),
			'2014-01-31 20:59:59'
		);

		// do it over leap day
		$o = new DateRange('2016-02-29', '2016-03-01');
		$o->adjustTimes('litle');
		$this->assertEqual(
			$o->getStart()->format('Y-m-d H:i:s'),
			'2016-02-28 21:00:00'
		);
		$this->assertEqual(
			$o->getEnd()->format('Y-m-d H:i:s'),
			'2016-03-01 20:59:59'
		);

		$o = new DateRange('2016-02-28 11:11:11', '2016-02-29 23:59:59');
		$o->adjustTimes('litle');
		$this->assertEqual(
			$o->getStart()->format('Y-m-d H:i:s'),
			'2016-02-27 21:00:00'
		);
		$this->assertEqual(
			$o->getEnd()->format('Y-m-d H:i:s'),
			'2016-02-29 20:59:59'
		);

	}

	public function testAdjustTimesMidnight() {
		$o = new DateRange('2014-01-01 11:11:11', '2014-01-31 07:08:09');
		$o->adjustTimes();
		$this->assertEqual(
			$o->getStart()->format('Y-m-d H:i:s'),
			'2014-01-01 00:00:00'
		);
		$this->assertEqual(
			$o->getEnd()->format('Y-m-d H:i:s'),
			'2014-01-31 23:59:59'
		);
	}

	public function testValid() {
		$o = new DateRangeMock();
		$o->setStart('2014-01-31');
		$o->setEnd('2014-01-01');
		$this->assertFalse($o->valid());
		$o->setStart('2014-01-01');
		$o->setEnd('2014-01-31');
		$this->assertTrue($o->valid());
		$o->setStart('2014-01-01');
		$o->setEnd('2014-01-01');
		$this->assertTrue($o->valid());
		$o = new DateRange(null, '2014-01-31');
		$this->assertTrue($o->valid());
		$o = new DateRange('2014-01-01', null);
		$this->assertTrue($o->valid());
	}

	public function testContains() {
		$o = new DateRange('2014-01-01', '2014-01-31');
		$o->adjustTimes();
		$this->assertFalse($o->contains('2013-01-31'));
		$this->assertFalse($o->contains('2013-12-31 23:59:59', true));
		$this->assertFalse($o->contains('2013-12-31 23:59:59', false));
		// boundry (always includes the start datetime)
		$this->assertTrue($o->contains('2014-01-01 00:00:00', false));
		$this->assertTrue($o->contains('2014-01-01 00:00:00'));
		$this->assertTrue($o->contains('2014-01-01'));
		$this->assertTrue($o->contains('2014-01-01 00:00:01'));
		$this->assertTrue($o->contains('2014-01-01 11:11:11'));
		$this->assertTrue($o->contains('2014-01-31'));
		$this->assertTrue($o->contains('2014-01-31 23:59:59'));
		// boundry (optionally includes the end datetime)
		$this->assertFalse($o->contains('2014-01-31 23:59:59', false));
		$this->assertFalse($o->contains('2014-02-01 00:00:00', true));
		$this->assertFalse($o->contains('2014-02-01 00:00:00', false));
		$this->assertFalse($o->contains('2014-02-31'));
		$this->assertFalse($o->contains('2015-01-31'));
	}

	public function testContainsNoEnd() {
		$o = new DateRange('2014-01-01', null);
		$o->adjustTimes();
		$this->assertFalse($o->contains('2001-01-31'));
		$this->assertFalse($o->contains('2013-01-31'));
		$this->assertFalse($o->contains('2013-12-31 23:59:59', true));
		$this->assertFalse($o->contains('2013-12-31 23:59:59', false));
		// boundry (always includes the start datetime)
		$this->assertTrue($o->contains('2014-01-01 00:00:00', false));
		$this->assertTrue($o->contains('2014-01-01 00:00:00'));
		$this->assertTrue($o->contains('2014-01-01'));
		$this->assertTrue($o->contains('2014-01-01 00:00:01'));
		$this->assertTrue($o->contains('2014-01-01 11:11:11'));
		$this->assertTrue($o->contains('2014-01-31'));
		$this->assertTrue($o->contains('2014-01-31 23:59:59'));
		$this->assertTrue($o->contains('2014-01-31 23:59:59', false));
		$this->assertTrue($o->contains('2014-02-01 00:00:00', true));
		$this->assertTrue($o->contains('2014-02-01 00:00:00', false));
		$this->assertTrue($o->contains('2014-02-31'));
		$this->assertTrue($o->contains('2015-01-31'));
		$this->assertTrue($o->contains('2099-01-31'));
	}

	public function testContainsNoStart() {
		$o = new DateRange(null, '2014-01-31');
		$o->adjustTimes();
		$this->assertTrue($o->contains('2001-01-31'));
		$this->assertTrue($o->contains('2013-01-31'));
		$this->assertTrue($o->contains('2013-12-31 23:59:59', true));
		$this->assertTrue($o->contains('2013-12-31 23:59:59', false));
		$this->assertTrue($o->contains('2014-01-01 00:00:00', false));
		$this->assertTrue($o->contains('2014-01-01 00:00:00'));
		$this->assertTrue($o->contains('2014-01-01'));
		$this->assertTrue($o->contains('2014-01-01 00:00:01'));
		$this->assertTrue($o->contains('2014-01-01 11:11:11'));
		$this->assertTrue($o->contains('2014-01-31'));
		$this->assertTrue($o->contains('2014-01-31 23:59:59'));
		// boundry (optionally includes the end datetime)
		$this->assertFalse($o->contains('2014-01-31 23:59:59', false));
		$this->assertFalse($o->contains('2014-02-01 00:00:00', true));
		$this->assertFalse($o->contains('2014-02-01 00:00:00', false));
		$this->assertFalse($o->contains('2014-02-31'));
		$this->assertFalse($o->contains('2015-01-31'));
		$this->assertFalse($o->contains('2099-01-31'));
	}

	public function testStaticHelpers() {
		$this->assertTrue(
			DateRange::in('2014-01-01', '2014-01-31')->contains('2014-01-01')
		);
		$this->assertFalse(
			DateRange::in('2014-01-01', '2014-01-31')->contains('2014-02-01')
		);
		$this->assertFalse(
			DateRange::in('2014-01-01', '2014-01-31')->contains('2013-12-31')
		);

		$this->assertTrue(
			DateRange::in()
				->setTimezone('America/New_York')
				->setStart('2014-01-01')
				->setEnd('2014-01-31')
				->contains('2014-01-01')
		);
		$this->assertFalse(
			DateRange::in()
				->setTimezone('America/New_York')
				->setStart('2014-01-01')
				->setEnd('2014-01-31')
				->contains('2014-02-01')
		);
		$this->assertFalse(
			DateRange::in()
				->setTimezone('America/New_York')
				->setStart('2014-01-01')
				->setEnd('2014-01-31')
				->contains('2013-12-31')
		);
	}

}

