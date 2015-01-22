<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: Feb 5, 2014
 * Time: 5:06:16 PM
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */

/**
 * AllDateRangeTest
 * 
 * @package DateRangeTest
 * @subpackage Test
 */
class AllDateRangeTest extends PHPUnit_Framework_TestSuite {

	/**
	 * 	All DateRange tests suite
	 *
	 * @return PHPUnit_Framework_TestSuite the instance of PHPUnit_Framework_TestSuite
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All DateRange Tests');
		$basePath = App::pluginPath('DateRange') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($basePath);
		return $suite;
	}

}
