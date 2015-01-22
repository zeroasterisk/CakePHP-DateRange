<?php

/**
 * A basic DateRange class
 *
 *   App::uses('DateRange', 'DateRange.Lib');
 *
 * Usage as a Static Method:
 *
 *   DateRange::in('2014-01-01', '2014-01-31')->contains('2014-01-01')
 *
 *   DateRange::in('2014-01-01', '2014-01-31')
 *     ->setTimezone('America/New_York')
 *     ->setTimezone('America/New_York')
 *     ->adjustTimes('litle')
 *     ->contains('2014-01-01') === TRUE
 *
 *   DateRange::in()
 *     ->setTimezone('America/New_York')
 *     ->setStart('2014-01-01')
 *     ->setEnd('2014-01-31')
 *     ->adjustTimes('litle')
 *     ->contains('2013-12-31')
 *
 * Usage as a Class -> Object:
 *
 *   $DateRangeObject = new DateRange('2015-01-01', '2015-12-31');
 *   $DateRangeObject->contains('2014-12-31') === FALSE
 *   $DateRangeObject->contains('2015-01-01') === TRUE
 *   $DateRangeObject->contains('2015-01-01', false) === TRUE (not inclusive)
 *   $DateRangeObject->contains('now') ?
 *
 *
 * @author: alan blount <alan@zeroasterisk.com>
 * @author: imsamurai <im.samuray@gmail.com> (original idea)
 * @link: https://github.com/zeroasterisk/CakePHP-DateRange.git
 * @package: DateRange
 */

/**
 * Class for date range
 *
 * @package DateRange
 * @subpackage Utility
 */
class DateRange {

	/**
	 * Start date
	 *
	 * @var DateTime
	 */
	protected $_startDate = null;

	/**
	 * End date
	 *
	 * @var DateTime
	 */
	protected $_endDate = null;

	/**
	 * TimeZone
	 *
	 * @var DateTimeZone
	 */
	protected $_timezone = null;

	/**
	 * Static class factory
	 *
	 *
	 * @param DateTime|string|int $start Start date
	 * @param DateTime|string|int $end End date
	 *
	 * @return DateRange $DateRange object
	 */
	static function in($start, $end = null) {
		return DateRange($start, $end);
	}

	/**
	 * Constructor
	 *
	 * @param DateTime|string|int $start Start date
	 * @param DateTime|string|int $end End date
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct($start, $end = null) {
		if (!empty($start)) {
			$this->setStart($start);
		}
		if (!empty($end)) {
			$this->setEnd($end);
		}
		if (!$this->valid()) {
			throw new InvalidArgumentException('Start date must be less or equal to end date');
		}
	}

	/**
	 * Get Start DateTime
	 *
	 * @return DateTime $DateTime
	 */
	public function getStart() {
		return $this->_startDate;
	}

	/**
	 * Get End DateTime
	 *
	 * @return DateTime $DateTime
	 */
	public function getEnd() {
		return $this->_endDate;
	}

	/**
	 * Get Timezone
	 *
	 * @return DateTimeZone $DateTimeZone
	 */
	public function getTimezone() {
		return $this->_timezone;
	}


	/**
	 * If you are not sure about your times,
	 * you can adjust them here
	 *
	 *   - how = 'midnight' (default) start at 00:00:00 and end at 23:59:59
	 *   - how = 'litle' start at 21:00:00 (-1 day) and end at 20:59:59 (today)
	 *
	 * @param string $how
	 *
	 * @return DateRange $this (chainable)
	 */
	public function setStart($date) {
		$this->_startDate = $this->buildDate($date, '00:00:00');
		return $this;
	}

	/**
	 * If you are not sure about your times,
	 * you can adjust them here
	 *
	 *   - how = 'midnight' (default) start at 00:00:00 and end at 23:59:59
	 *   - how = 'litle' start at 21:00:00 (-1 day) and end at 20:59:59 (today)
	 *
	 * @param string $how
	 *
	 * @return DateRange $this (chainable)
	 */
	public function setEnd($date) {
		$this->_endDate = $this->buildDate($date, '23:59:59');
		return $this;
	}

	/**
	 * If you are not sure about your times,
	 * you can adjust them here
	 *
	 *   - how = 'midnight' (default) start at 00:00:00 and end at 23:59:59
	 *   - how = 'litle' start at 21:00:00 (-1 day) and end at 20:59:59 (today)
	 *
	 * @param DateTimeZone|string $timezone
	 *
	 * @return DateRange $this (chainable)
	 */
	public function setTimezone($timezone = null) {
		if ($timezone instanceof DateTimeZone) {
			$this->_timezone = clone $timezone;
		} elseif (empty($timezone)) {
			$this->_timezone = null;
		} else {
			$this->_timezone = new DateTimeZone($timezone);
		}
		return $this;
	}

	/**
	 * If your input was in one timezone and you need to change it
	 * you can adjust them here
	 *
	 * @param DateTimeZone|string $timezone (optional)
	 *
	 * @return DateRange $this (chainable)
	 */
	public function adjustTimezone($timezone = null) {
		if (!empty($timezone)) {
			$this->setTimezone($timezone);
		}
		$start = $this->getStart();
		$start->setTimezone($this->getTimezone());
		$this->setStart($start);
		$end = $this->getEnd();
		$end->setTimezone($this->getTimezone());
		$this->setEnd($end);
		return $this;
	}

	/**
	 * If you are not sure about your times,
	 * you can adjust them here
	 *
	 *   - how = 'midnight' (default) start at 00:00:00 and end at 23:59:59
	 *   - how = 'litle' start at 21:00:00 (-1 day) and end at 20:59:59 (today)
	 *
	 * @param string $how
	 *
	 * @return DateRange $this (chainable)
	 */
	public function adjustTimes($how = null) {
		if ($how == 'litle') {
			$start = $this->getStart();
			if (!empty($start)) {
				$start->sub(new DateInterval('P1D'));
				$start->setTime(21, 00, 00);
				$this->setStart($start);
			}
			$end = $this->getEnd();
			if (!empty($end)) {
				$end->setTime(20, 59, 59);
				$this->setEnd($end);
			}
			return $this;
		}
		// default to 'midnight'
		$start = $this->getStart();
		if (!empty($start)) {
			$start->setTime(00, 00, 00);
			$this->setStart($start);
		}
		$end = $this->getEnd();
		if (!empty($end)) {
			$end->setTime(23, 59, 59);
			$this->setEnd($end);
		}
		return $this;
	}

	/**
	 * Build a DateTime object from any valid inputs
	 *   - DateTime (passthrough)
	 *   - int = unix timestamp (aka epoch) = time() or strtotime() output
	 *   - string as date+time
	 *   - string as date (will assign the default time)
	 *
	 * @param DateTime|string|int $date Input date
	 * @param string              $defaultTime
	 *
	 * @return DateTime $DateTime
	 */
	public function buildDate($date, $defaultTime = '00:00:00') {
		$output = null;
		if ($date instanceof DateTime) {
			return clone $date;
		}
		if (is_numeric($date)) {
			$output = new DateTime(null, $this->getTimezone());
			$output->setTimestamp($date);
			return $output;
		}
		// assume this is a string date or datetime input
		$DateTime = new DateTime($date, $this->getTimezone());

		// if the defaultTime is empty, just return the DateTime object
		if (empty($defaultTime) || $defaultTime == '00:00:00') {
			return $DateTime;
		}

		// can we assign the defaultTime?
		//   based on the timestamp of the date passed in
		if ($DateTime->format('H:i:s') == '00:00:00') {
			$timeParts = explode(':', $defaultTime);
			while (count($timeParts) < 3) {
				$timeParts[] = '00';
			}
			$DateTime->setTime($timeParts[0], $timeParts[1], $timeParts[2]);
		}

		return $DateTime;
	}

	/**
	 * CreateIterator for given interval $time
	 *
	 * @param string $time
	 * @return ArrayIterator
	 */
	public function period($time) {
		$Dates = array();
		$NextDate = $this->getStart();
		$end = $this->getEnd();
		while ($NextDate <= $end) {
			$Dates[] = clone $NextDate;
			$NextDate->modify("+$time");
		}
		return new ArrayIterator($Dates);
	}


	/**
	 * Validates the current date ranges are not "backwards"
	 *
	 * @return boolean $isValid
	 */
	public function valid() {
		$start = $this->getStart();
		if (empty($start)) {
			return true;
		}
		$end = $this->getEnd();
		if (empty($end)) {
			return true;
		}
		return ($start <= $end);
	}

	/**
	 * Returns true if $Date belongs to this range
	 *
	 * @param DateTime|string|int $date Input date
	 * @param bool $inclusive True means include range edges
	 *
	 * @return boolean $date is in range
	 */
	public function contains($date = 'now', $inclusive = true) {
		$Date = $this->buildDate($date, date('H:i:s'));

		$end = $this->getEnd();
		if (!empty($end)) {
			// End Timeframe set
			if ($inclusive) {
				if (!($Date <= $end)) {
					return false;
				}
			} else {
				// not inclusive
				if (!($Date < $end)) {
					return false;
				}
			}
		}

		$start = $this->getStart();
		if (!empty($start)) {
			// Start Timeframe set
			if ($inclusive) {
				if (!($start <= $Date)) {
					return false;
				}
			} else {
				// not inclusive
				if (!($start < $Date)) {
					return false;
				}
			}
		}

		return true;
	}

}

function DateRange($start, $end) {
	return new DateRange($start, $end);
}
