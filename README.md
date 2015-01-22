# CakePHP DateRange

A basic DateRange class

### Install

    git submodule add git@github.com:zeroasterisk/CakePHP-DateRange.git app/Plugin/DateRange
	  or
    git clone git@github.com:zeroasterisk/CakePHP-DateRange.git app/Plugin/DateRange

add `app/Config/bootstrap.php`

    CakePlugin::load('DateRange', array('bootstrap' => false));

### Initialize the Lib

    App::uses('DateRange', 'DateRange.Lib');

### Usage as a Static Method:

    DateRange::in('2014-01-01', '2014-01-31')->contains('2014-01-01')

    DateRange::in('2014-01-01', '2014-01-31')
      ->setTimezone('America/New_York')
      ->setTimezone('America/New_York')
      ->adjustTimes('litle')
      ->contains('2014-01-01') === TRUE

    DateRange::in()
      ->setTimezone('America/New_York')
      ->setStart('2014-01-01')
      ->setEnd('2014-01-31')
      ->adjustTimes('litle')
      ->contains('2013-12-31')

### Usage as a Class -> Object:

    $DateRangeObject = new DateRange('2015-01-01', '2015-12-31');
    $DateRangeObject->contains('2014-12-31') === FALSE
    $DateRangeObject->contains('2015-01-01') === TRUE
    $DateRangeObject->contains('2015-01-01', false) === TRUE (not inclusive)
    $DateRangeObject->contains('now') ?

# TODO

build a Behavior to go with this Lib.
The Lib does the lifting the Behavior does the coordination / ease-of-use.

* Behavior to setup named ranges with a `start` and `stop` field
* Behavior to make conditions `dateInRange('now')`
* Behavior to check a record

# Acknowledgements

The original version of the Lib is from:
  https://github.com/imsamurai/CakePHP-DateRange-Utility
    compliments.

