<?php

/**
* @file
* Astro_MoonPhase Class
*
* Shared class for calculating information about the phase of the moon 
* at a given time.
*
* Based on the {@link 
* http://search.cpan.org/~brett/Astro-MoonPhase-0.60/MoonPhase.pm 
* CPAN module Astro::MoonPhase, version 0.60}.
* 
* @author John Walker 
*   - The moontool.c Release 2.0: A Moon for the Sun
*   - Designed and implemented in December 1987, 
*   - revised and updated in February of 1988.
* 
* @author Raino Pikkarainen <raino.pikkarainen@saunalahti.fi> 
*   - Initial Perl transcription 1998
* 
* @author Ron Hitchens 
*   - The moontool.c Release 2.4: Major enhancements 1989
* 
* @author Brett Hamilton http://simple.be/ 
*   - Bug fix, 2003 
*   - Second transcription and bugfixes, 2004
* 
* @author Christopher J. Madsen http://www.cjmweb.net/ 
*   - Added phaselist function, March 2007
* 
* @author Stephen A. Zarkos <obsid@sentry.net> 
*   - Translation to PHP 2007
*   - Fixed broken phasehunt function, 2008-10-27
*   - Added phaselist function, 2008-10-27
* 
* @author Christopher Hopper <christopher.jf.hopper@gmail.com>
*   - Conversion to a PHP class 2009-02-03 
*   - Doxygen style comments 2009-02-03
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

/**
* base class
*/
require_once 'PEAR.php';


/** 
* Error definitions.
*/
define( 'ASTRO_ERR_UNDEF',    -1 );

/** 
* Astronomical constants.
*/
define( 'ASTRO_EPOCH',    2444238.5 );    // 1980 January 0.0

/** 
* Constants defining the Sun's apparent orbit.
*/
define( 'ASTRO_ELONGE',    278.833540 );    // ecliptic longitude of the Sun at epoch 1980.0
define( 'ASTRO_ELONGP',    282.596403 );    // ecliptic longitude of the Sun at perigee
define( 'ASTRO_ECCENT',    0.016718   );    // eccentricity of Earth's orbit
define( 'ASTRO_SUNSMAX',   1.495985e8 );    // semi-major axis of Earth's orbit, km
define( 'ASTRO_SUNANGSIZ', 0.533128   );    // sun's angular size, degrees, at semi-major axis distance

/** 
* Elements of the Moon's orbit, epoch 1980.0.
*/
define( 'ASTRO_MMLONG',    64.975464   );    // moon's mean longitude at the epoch
define( 'ASTRO_MMLONGP',   349.383063  );    // mean longitude of the perigee at the epoch
define( 'ASTRO_MLNODE',    151.950429  );    // mean longitude of the node at the epoch
define( 'ASTRO_MINC',      5.145396    );    // inclination of the Moon's orbit
define( 'ASTRO_MECC',      0.054900    );    // eccentricity of the Moon's orbit
define( 'ASTRO_MANGSIZ',   0.5181      );    // moon's angular size at distance a from Earth
define( 'ASTRO_MSMAX',     384401.0    );    // semi-major axis of Moon's orbit in km
define( 'ASTRO_MPARALLAX', 0.9507      );    // parallax at distance a from Earth
define( 'ASTRO_SYNMONTH',  29.53058868 );    // synodic month (new Moon to new Moon)

/**
* Astrological Moon Phase Calculator Class
*/
class Astro_MoonPhase extends PEAR {

  /**
  * extract sign
  * 
  * @param mixed $arg
  * @return integer 
  *   Will return -1 if less than zero, 1 if greater than zero, or 0.
  */
  static function sgn ( $arg )      { return (($arg < 0) ? -1 : ($arg > 0 ? 1 : 0)); }

  /**
  * fix angle
  * 
  * @param mixed $arg
  * @return integer
  */
  static function fixangle ( $arg ) { return ($arg - 360.0 * (floor($arg / 360.0))); }

  /**
  * Sine from degrees
  * 
  * @param float $arg
  * @return float
  */
  static function dsin ( $arg )     { return (sin(deg2rad($arg))); }

  /**
  * Cosine from degrees
  * 
  * @param float $arg
  * @return float
  */
  static function dcos ( $arg )     { return (cos(deg2rad($arg))); }


  /**
  * Convert internal date and time to astronomical Julian time (i.e. 
  * Julian date plus day fraction)
  * 
  * @param integer $timestamp
  * @return float
  */
  static function jtime ( $timestamp )  {
    $julian = ( $timestamp / 86400 ) + 2440587.5;    // (seconds / (seconds per day)) + julian date of epoch
    return $julian;
  }

  /**
  * Convert Julian date to a UNIX epoch
  * 
  * @param float $jday optional
  * @return integer
  */
  static function jdaytosecs ( $jday = 0 )  {
    $stamp = ( $jday - 2440587.5 ) * 86400;    // (juliandate - jdate of unix epoch) * (seconds per julian day)
    return $stamp;
  }

  /**
  * Convert Julian date to year, month, day.
  * 
  * @param float $td
  *   Julian date to convert
  * @return array 
  *   The date in the form array(year, month, day)
  */
  static function jyear ( $td )  {
    $td += 0.5;    // astronomical to civil.
    $z = floor( $td );
    $f = $td - $z;

    if ( $z < 2299161.0 )  {
      $a = $z;
    }
    else  {
      $alpha = floor( ($z - 1867216.25) / 36524.25 );
      $a = $z + 1 + $alpha - floor( $alpha / 4 );
    }

    $b = $a + 1524;
    $c = floor( ($b - 122.1) / 365.25 );
    $d = floor( 365.25 * $c );
    $e = floor( ($b - $d) / 30.6001 );

    $dd = $b - $d - floor( 30.6001 * $e ) + $f;
    $mm = $e < 14 ? $e - 1 : $e - 13;
    $yy = $mm > 2 ? $c - 4716 : $c - 4715;
    return array($yy, $mm, $dd);
  }



   
  /**
  * Calculates time of the mean new Moon for a given base date. 
  * 
  * @param float $sdate
  * @param float $k
  *   The precomputed synodic month index, given by 
  *     K = (year - 1900) * 12.3685 
  *   where year is expressed as a year and fractional year.
  */
  static function meanphase ( $sdate, $k )  {

    // Time in Julian centuries from 1900 January 0.5
    $t = ( $sdate - 2415020.0 ) / 36525;
    $t2 = $t * $t;    // Square for frequent use 
    $t3 = $t2 * $t;    // Cube for frequent use 

    $nt1 = 2415020.75933 + ASTRO_SYNMONTH * $k
        + 0.0001178 * $t2
        - 0.000000155 * $t3
        + 0.00033 * Astro_MoonPhase::dsin( 166.56 + 132.87 * $t - 0.009173 * $t2 );

    return ( $nt1 );
  }



  /**
  * obtain the true, corrected phase time.
  * 
  * @param mixed $k
  *   Value used to determine the mean phase of the new moon
  * @param float $phase
  *   A phase selector (0.0, 0.25, 0.5, 0.75)
  * @return mixed
  */
  static function truephase ( $k, $phase )  {
    $apcor = 0;

    $k += $phase;            // add phase to new moon time
    $t = $k / 1236.85;        // time in Julian centuries from 1900 January 0.5
    $t2 = $t * $t;            // square for frequent use
    $t3 = $t2 * $t;            // cube for frequent use

    // mean time of phase
    $pt = 2415020.75933
        + ASTRO_SYNMONTH * $k
        + 0.0001178 * $t2
        - 0.000000155 * $t3
        + 0.00033 * Astro_MoonPhase::dsin( 166.56 + 132.87 * $t - 0.009173 * $t2 );

    // Sun's mean anomaly
    $m = 359.2242
        + 29.10535608 * $k
        - 0.0000333 * $t2
        - 0.00000347 * $t3;

    // Moon's mean anomaly
    $mprime = 306.0253
        + 385.81691806 * $k
        + 0.0107306 * $t2
        + 0.00001236 * $t3;

    // Moon's argument of latitude
    $f = 21.2964
        + 390.67050646 * $k
        - 0.0016528 * $t2
        - 0.00000239 * $t3;

    if ( ($phase < 0.01) || (abs($phase - 0.5) < 0.01) )  {
        // Corrections for New and Full Moon.
        $pt += ( 0.1734 - 0.000393 * $t ) * Astro_MoonPhase::dsin( $m )
            + 0.0021 * Astro_MoonPhase::dsin( 2 * $m  )
            - 0.4068 * Astro_MoonPhase::dsin( $mprime )
            + 0.0161 * Astro_MoonPhase::dsin( 2 * $mprime )
            - 0.0004 * Astro_MoonPhase::dsin( 3 * $mprime )
            + 0.0104 * Astro_MoonPhase::dsin( 2 * $f )
            - 0.0051 * Astro_MoonPhase::dsin( $m + $mprime )
            - 0.0074 * Astro_MoonPhase::dsin( $m - $mprime )
            + 0.0004 * Astro_MoonPhase::dsin( 2 * $f + $m )
            - 0.0004 * Astro_MoonPhase::dsin( 2 * $f - $m )
            - 0.0006 * Astro_MoonPhase::dsin( 2 * $f + $mprime )
            + 0.0010 * Astro_MoonPhase::dsin( 2 * $f - $mprime )
            + 0.0005 * Astro_MoonPhase::dsin( $m + 2 * $mprime );
        $apcor = 1;
    }
    elseif ( (abs($phase - 0.25) < 0.01 || (abs($phase - 0.75) < 0.01)) )  {
        $pt += ( 0.1721 - 0.0004 * $t ) * Astro_MoonPhase::dsin( $m )
            + 0.0021 * Astro_MoonPhase::dsin( 2 * $m )
            - 0.6280 * Astro_MoonPhase::dsin( $mprime )
            + 0.0089 * Astro_MoonPhase::dsin( 2 * $mprime )
            - 0.0004 * Astro_MoonPhase::dsin( 3 * $mprime )
            + 0.0079 * Astro_MoonPhase::dsin( 2 * $f )
            - 0.0119 * Astro_MoonPhase::dsin( $m + $mprime )
            - 0.0047 * Astro_MoonPhase::dsin( $m - $mprime )
            + 0.0003 * Astro_MoonPhase::dsin( 2 * $f + $m )
            - 0.0004 * Astro_MoonPhase::dsin( 2 * $f - $m )
            - 0.0006 * Astro_MoonPhase::dsin( 2 * $f + $mprime )
            + 0.0021 * Astro_MoonPhase::dsin( 2 * $f - $mprime )
            + 0.0003 * Astro_MoonPhase::dsin( $m + 2 * $mprime )
            + 0.0004 * Astro_MoonPhase::dsin( $m - 2 * $mprime )
            - 0.0003 * Astro_MoonPhase::dsin( 2 * $m + $mprime );
        if ( $phase < 0.5 )  {
            // First quarter correction.
            $pt += 0.0028 - 0.0004 * Astro_MoonPhase::dcos( $m ) + 0.0003 * Astro_MoonPhase::dcos( $mprime );
        }
        else {
            // Last quarter correction.
            $pt += -0.0028 + 0.0004 * Astro_MoonPhase::dcos( $m ) - 0.0003 * Astro_MoonPhase::dcos( $mprime );
        }
        $apcor = 1;
    }
    if ( !$apcor )  {
        Astro_MoonPhase::throwError("truephase() called with invalid phase selector", ASTRO_ERR_UNDEF, $phase);
    }
    return ( $pt );
  }



  /**
  * Find time of phases of the moon which surround the current date.
  * 
  * @param int $time
  *   A UNIX timestamp
  * @return array
  *   The times for five phases, starting and ending with 
  *   the new moons which bound the current lunation. 
  */
  static function phasehunt ( $time=-1 )  {

    $sdate = Astro_MoonPhase::jtime( $time );
    $sdate = Astro_MoonPhase::jtime( $time );
    $adate = $sdate - 45;
    list($yy, $mm, $dd) = Astro_MoonPhase::jyear($adate);
    $k1 = floor( ($yy + (($mm - 1) * (1.0 / 12.0)) - 1900) * 12.3685 );
    $adate = $nt1 = Astro_MoonPhase::meanphase( $adate,  $k1 );

    while (1)  {
        $adate += ASTRO_SYNMONTH;
        $k2 = $k1 + 1;
        $nt2 = Astro_MoonPhase::meanphase( $adate, $k2 );
        if (($nt1 <= $sdate) && ($nt2 > $sdate))  {
            break;
        }
        $nt1 = $nt2;
        $k1 = $k2;
    }

    return array (    Astro_MoonPhase::jdaytosecs( Astro_MoonPhase::truephase($k1, 0.0) ),
            Astro_MoonPhase::jdaytosecs( Astro_MoonPhase::truephase($k1, 0.25) ),
            Astro_MoonPhase::jdaytosecs( Astro_MoonPhase::truephase($k1, 0.5) ), 
            Astro_MoonPhase::jdaytosecs( Astro_MoonPhase::truephase($k1, 0.75) ),
            Astro_MoonPhase::jdaytosecs( Astro_MoonPhase::truephase($k2, 0.0) )
    );
  }



  /**
  * Find time of phases of the moon between two dates.
  * 
  * @param int $sdate
  *   Start date as a UNIX timestamp
  * @param int $edate
  *   End date as a UNIX timestamp
  * @return array
  */
  static function phaselist ( $sdate, $edate )  {
    if ( empty($sdate) || empty($edate) )  {
        return array();
    }

    $sdate = Astro_MoonPhase::jtime( $sdate );
    $edate = Astro_MoonPhase::jtime( $edate );

    $phases = array();
    $d = $k = $yy = $mm = 0;

    list($yy, $mm, $d) = Astro_MoonPhase::jyear($sdate);
    $k = floor(($yy + (($mm - 1) * (1.0 / 12.0)) - 1900) * 12.3685) - 2;

    while (1)  {
        ++$k;
        foreach ( array(0.0, 0.25, 0.5, 0.75) as $phase )  {
            $d = Astro_MoonPhase::truephase( $k, $phase );
            if ( $d >= $edate )  {
                return $phases;
            }
            if ( $d >= $sdate )  {
                if ( empty($phases) )  {
                    array_push( $phases, floor(4 * $phase) );
                }
                array_push( $phases, Astro_MoonPhase::jdaytosecs($d) );
            }
        }
    }  // End while(1)
  }



  /**
  * Solve the equation of Kepler
  * 
  * @param float $m
  * @param float $ecc
  * @return float
  */
  static function kepler ( $m, $ecc ) {
    $EPSILON = 1e-6;
    $m = deg2rad( $m );
    $e = $m;
    do  {
        $delta = $e - $ecc * sin( $e ) - $m;
        $e -= $delta / ( 1 - $ecc * cos($e) );
    } while ( abs($delta) > $EPSILON );
    return ( $e );
  }



  /**
  * Calculate phase of moon as a fraction
  *  
  * @param int $time optional
  *   The UNIX timestamp for which the phase information is requested. 
  * @return array
  *   Returns the terminator phase angle as a percentage of a full circle 
  *   (i.e., 0 to 1), the illuminated fraction of the Moon's disc, the 
  *   Moon's age in days and fraction, the distance of the Moon from the 
  *   centre of the Earth, and the angular diameter subtended by the Moon 
  *   as seen by an observer at the centre of the Earth. 
  */
  static function phase ( $time = 0 )  {
    if ( empty($time) || $time == 0 )  {
        $time = time();
    }
    $pdate = Astro_MoonPhase::jtime( $time );

    $pphase;    // illuminated fraction
    $mage;        // age of moon in days
    $dist;        // distance in kilometres
    $angdia;    // angular diameter in degrees
    $sudist;    // distance to Sun
    $suangdia;    // sun's angular diameter

  //    my ($Day, $N, $M, $Ec, $Lambdasun, $ml, $MM, $MN, $Ev, $Ae, $A3, $MmP,
  //       $mEc, $A4, $lP, $V, $lPP, $NP, $y, $x, $Lambdamoon, $BetaM,
  //       $MoonAge, $MoonPhase,
  //       $MoonDist, $MoonDFrac, $MoonAng, $MoonPar,
  //       $F, $SunDist, $SunAng,
  //       $mpfrac);

    // Calculation of the Sun's position.
    $Day = $pdate - ASTRO_EPOCH;                        // date within epoch
    $N = Astro_MoonPhase::fixangle( (360 / 365.2422) * $Day );            // mean anomaly of the Sun
    $M = Astro_MoonPhase::fixangle( $N + ASTRO_ELONGE - ASTRO_ELONGP );                // convert from perigee co-ordinates
                                    //   to epoch 1980.0
    $Ec = Astro_MoonPhase::kepler( $M, ASTRO_ECCENT );                    // solve equation of Kepler
    $Ec = sqrt( (1 + ASTRO_ECCENT) / (1 - ASTRO_ECCENT) ) * tan( $Ec / 2 );
    $Ec = 2 * rad2deg( atan($Ec) );                    // true anomaly
    $Lambdasun = Astro_MoonPhase::fixangle( $Ec + ASTRO_ELONGP );                // Sun's geocentric ecliptic longitude
    # Orbital distance factor.
    $F = ( (1 + ASTRO_ECCENT * cos(deg2rad($Ec))) / (1 - ASTRO_ECCENT * ASTRO_ECCENT) );
    $SunDist = ASTRO_SUNSMAX / $F;                    // distance to Sun in km
    $SunAng = $F * ASTRO_SUNANGSIZ;                    // Sun's angular size in degrees


    // Calculation of the Moon's position.

    // Moon's mean longitude.
    $ml = Astro_MoonPhase::fixangle( 13.1763966 * $Day + ASTRO_MMLONG );

    // Moon's mean anomaly.
    $MM = Astro_MoonPhase::fixangle( $ml - 0.1114041 * $Day - ASTRO_MMLONGP );

    // Moon's ascending node mean longitude.
    $MN = Astro_MoonPhase::fixangle( ASTRO_MLNODE - 0.0529539 * $Day );

    // Evection.
    $Ev = 1.2739 * sin( deg2rad(2 * ($ml - $Lambdasun) - $MM) );

    // Annual equation.
    $Ae = 0.1858 * sin( deg2rad($M) );

    // Correction term.
    $A3 = 0.37 * sin( deg2rad($M) );

    // Corrected anomaly.
    $MmP = $MM + $Ev - $Ae - $A3;

    // Correction for the equation of the centre.
    $mEc = 6.2886 * sin( deg2rad($MmP) );

    // Another correction term.
    $A4 = 0.214 * sin( deg2rad(2 * $MmP) );

    // Corrected longitude.
    $lP = $ml + $Ev + $mEc - $Ae + $A4;

    // Variation.
    $V = 0.6583 * sin( deg2rad(2 * ($lP - $Lambdasun)) );

    // True longitude.
    $lPP = $lP + $V;

    // Corrected longitude of the node.
    $NP = $MN - 0.16 * sin( deg2rad($M) );

    // Y inclination coordinate.
    $y = sin( deg2rad($lPP - $NP) ) * cos( deg2rad(ASTRO_MINC) );

    // X inclination coordinate.
    $x = cos(deg2rad($lPP - $NP));

    // Ecliptic longitude.
    $Lambdamoon = rad2deg( atan2($y, $x) );
    $Lambdamoon += $NP;

    // Ecliptic latitude.
    $BetaM = rad2deg( asin(sin(deg2rad($lPP - $NP)) * sin(deg2rad(ASTRO_MINC))) );


    // Calculation of the phase of the Moon.

    // Age of the Moon in degrees.
    $MoonAge = $lPP - $Lambdasun;

    // Phase of the Moon.
    $MoonPhase = (1 - cos(deg2rad($MoonAge))) / 2;

    // Calculate distance of moon from the centre of the Earth.
    $MoonDist = ( ASTRO_MSMAX * (1 - ASTRO_MECC * ASTRO_MECC)) / (1 + ASTRO_MECC * cos(deg2rad($MmP + $mEc)) );

    // Calculate Moon's angular diameter.
    $MoonDFrac = $MoonDist / ASTRO_MSMAX;
    $MoonAng = ASTRO_MANGSIZ / $MoonDFrac;

    // Calculate Moon's parallax.
    $MoonPar = ASTRO_MPARALLAX / $MoonDFrac;

    $pphase = $MoonPhase;
    $mage = ASTRO_SYNMONTH * ( Astro_MoonPhase::fixangle($MoonAge) / 360.0 );
    $dist = $MoonDist;
    $angdia = $MoonAng;
    $sudist = $SunDist;
    $suangdia = $SunAng;
    $mpfrac = Astro_MoonPhase::fixangle($MoonAge) / 360.0;

    return array ( $mpfrac, $pphase, $mage, $dist, $angdia, $sudist, $suangdia );
  }
}
