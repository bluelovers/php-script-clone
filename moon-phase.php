<html>
<body>
<?php

require 'moon-phase.cls.php';

$dateAsTimeStamp = ''; // no need to pass the date if you want to use the current date
//$dateAsTimeStamp = strtotime('June 9 2003 21:00 UT');
$mp = new moonPhase($dateAsTimeStamp);

echo "<b>On this date: ", strftime ("%b %d %Y %H:%M:%S", $mp->getDateAsTimeStamp()), ":</b>";
echo "<br />\n";
echo "The position (phase) within the moon's cycle: ", $mp->getPositionInCycle();
echo "<br />\n";
echo "The phase name: ", $mp->getPhaseName();
echo "<br />\n";
echo "The percentage of lunar illumination is ", $mp->getPercentOfIllumination();
echo "<br />\n";
echo "The days until the next full moon are: ", $mp->getDaysUntilNextFullMoon();
echo "<br />\n";
echo "The days until the next new moon are: ", $mp->getDaysUntilNextNewMoon();
echo "<br />\n";
echo "The days until the next first quarter moon are: ", $mp->getDaysUntilNextFirstQuarterMoon();
echo "<br />\n";
echo "The days until the next last quarter moon are: ", $mp->getDaysUntilNextLastQuarterMoon();
echo "<br />\n<br />\n";
echo "<b>Moon phases for upcoming week:</b>";
echo "<br />\n";
$UpcomingWeekArray = $mp->getUpcomingWeekArray();
foreach($UpcomingWeekArray as $timeStamp => $phaseID)
	echo "&nbsp;&nbsp;", date('l',$timeStamp), ": ", $mp->getPhaseName($phaseID), "<br />\n";

?>
</body>
</html> 