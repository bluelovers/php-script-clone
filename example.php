<?php
/**
* @file
* Astro_MoonPhase Class Example
*
* Sample code for using {@link http://www.sentry.net/~obsid/moonphase
* Astro_MoonPhase.php}.
*
* @author Stephen A. Zarkos <obsid@sentry.net>
*   - Translation to PHP 2007
*   - Fixed broken phasehunt function, 2008-10-27
*   - Added phaselist function, 2008-10-27
*
* @author Christopher Hopper <christopher.jf.hopper@gmail.com>
*   - Conversion to a PHP class 2009-02-03 
*   - Doxygen style comments 2009-02-03
*   - Rewrote the examples to use Class methods 2009-02-04
*   - Renaming of Constants to avoid conflicts 2009-02-05
*
* @package Astro
* @version 0.60 2008-02-05
* @license CPAN Astro::MoonPhase module is distributed under the public
*   domain, and so is this PHP translation.
* @link http://search.cpan.org/perldoc?Astro::MoonPhase
*   Astro::MoonPhase - Information about the phase of the Moon
* @link http://www.obsid.org/2008/05/calculate-moon-phase-data-with-php.html
*   Steve's Journal: Calculate Moon Phase Data with PHP
* @link http://www.sentry.net/~obsid/moonphase
*/

require 'lib/Astro_MoonPhase.php';

/**
* Print
* 
* If printing to the Browser, output will be converted to HTML. If 
* printing to a command line, output will be printed raw. 
* 
* @param string $s
* @return void
*/
function p($s) {
  switch (strtolower(PHP_SAPI)) {
    case 'cli' :
      break;
    case 'apache2handler' :
    default :
      $s = htmlentities($s);
      $s = nl2br($s);
  }
  print $s;
  return;
}

/**
* Execute the phasehunt() Example
*/
p("Example: phasehunt()". PHP_EOL);
do_phasehunt();
p(PHP_EOL . PHP_EOL);


/**
* Execute the phaselist() Example
*/
p("Example: phaselist()". PHP_EOL);
$start = strtotime( "2008-10-01 00:00:00 PST" );
$stop = strtotime( "2008-10-31 00:00:00 PST" );
do_phaselist( $start, $stop );
p(PHP_EOL . PHP_EOL);


/**
* Execute the phase() Example
*/
$date = "2008-10-31";
$time = "00:00:00";
$tzone = "PST";
p("Example: phase() ($date $time $tzone)". PHP_EOL);
do_phase( $date, $time, $tzone );
p(PHP_EOL . PHP_EOL);



/**
* phasehunt() Example
*
* @return void
*/
function do_phasehunt()  {
  $phases = array();
  $phases = Astro_MoonPhase::phasehunt();
  p(date("D M j G:i:s T Y", $phases[0]) . PHP_EOL);
  p(date("D M j G:i:s T Y", $phases[1]) . PHP_EOL);
  p(date("D M j G:i:s T Y", $phases[2]) . PHP_EOL);
  p(date("D M j G:i:s T Y", $phases[3]) . PHP_EOL);
  p(date("D M j G:i:s T Y", $phases[4]) . PHP_EOL);
}



/**
* phaselist() Example
*
* @param int $start
* @param int $stop
* @return void
*/
function do_phaselist( $start, $stop )  {
  $name = array ( "New Moon", "First quarter", "Full moon", "Last quarter" );
  $times = Astro_MoonPhase::phaselist( $start, $stop );

  foreach ( $times as $time )  {
    // First element is the starting phase (see $name).
    if ( $time == $times[0] )  {
      p($name[$times[0]] . PHP_EOL);
    }
    else  {
      p(date("D M j G:i:s T Y", $time) . PHP_EOL);
    }
  }
}



/**
* phase() Example
*
* @param string $date
* @param string $time
* @param string $tzone
* @return void
*/
function do_phase ( $date, $time, $tzone )  {
  $moondata = Astro_MoonPhase::phase(strtotime($date . ' ' . $time . ' ' . $tzone));

  $MoonPhase = $moondata[0];
  $MoonIllum = $moondata[1];
  $MoonAge   = $moondata[2];
  $MoonDist  = $moondata[3];
  $MoonAng   = $moondata[4];
  $SunDist   = $moondata[5];
  $SunAng    = $moondata[6];

  $phase = 'Waxing';
  if ( $MoonAge > ASTRO_SYNMONTH/2 )  {
    $phase = 'Waning';
  }

  // Convert $MoonIllum to percent and round to whole percent.
  $MoonIllum = round( $MoonIllum, 2 );
  $MoonIllum *= 100;
  if ( $MoonIllum == 0 )  {
    $phase = "New Moon";
  }
  if ( $MoonIllum == 100 )  {
    $phase = "Full Moon";
  }

  p("Moon Phase: $phase". PHP_EOL);
  p("Percent Illuminated: $MoonIllum% ". PHP_EOL);
}

