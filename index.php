<?php
require_once( __DIR__ . '/tdcron.php');
require_once( __DIR__ . '/tdcron_entry.php');

// read from file? input? whatever
/*
crons.php should hold something like the following:
$lines = array(
    '0 5 * * * dummy run',
    '* * * * * other dummy job',
    ...
);
*/
require_once( __DIR__ . '/crons.php' );

// split out the cron time and the command
foreach ( $lines as $i => $line )
{
    $parts = explode( ' ', $line );
    $time = array_slice($parts, 0, 5);

    $jobs[$i]['time'] = implode( $time, ' ' );
    $jobs[$i]['command'] = implode( array_diff( $parts, $time ), ' ' );
}

foreach ( $jobs as $job )
{
    // order of the segment array
    // 0 minutes (0 - 59)
    // 1 hours (0 - 23)
    // 2 days (1 - 31)
    // 3 months (1 - 12)
    // 4 day of week (0 - 6) (0 = Sunday)
    $parsed = tdCronEntry::parse( $job['time'] );

    $minutes = $parsed[0];
    $hours = $parsed[1];
    $days = $parsed[2];
    $months = $parsed[3];
    $days_of_week = $parsed[4];

    // do this instead of a huge multidemensional array for memory purposes
    $crons[$job['command']]['minutes'] = $minutes;
    $crons[$job['command']]['hours'] = $hours;
    $crons[$job['command']]['days'] = $days;
    $crons[$job['command']]['months'] = $months;
    $crons[$job['command']]['days_of_week'] = $days_of_week;
}
?>

<html>
<head>
    <style>
        body {
            background:#111;
            font-size:12px;
            font-family:Helvetica;
        }

        div {
            margin:5px;
            padding:5px;
        }

        a, a:visited {
            color:#000;
        }

        .command {
            margin-left:14px;
            border:1px solid white;
            padding:2px 4px;
        }
        .month {
            border:2px solid white;
            margin:10px auto;
            text-align:center;
            color:white;
            width:1152px;
        }

        .month a, .month a:visited {
            color:#fff;
        }

        .day a, .day a:visited {
            color:#000;
        }

        .day {
            border:2px solid white;
            background:#ccc;
            text-align:center;
            color:#000;
        }

        .hour {
            background:#fefefe;
            border:1px solid #999;
            float:left;
            padding:2px;
            text-align:center;
            margin:5px 0px;
            width:41px;
        }

        .minute {
            border:1px solid #ccc;
            text-align:center;
            font-size:smaller;
            padding:2px;
            min-height:14px;
        }

        .active {
            border:1px solid #000;
            background:#000;
            color:white;
            opacity:0.1;
        }

        .clear {
            clear:both;
            padding:0;
            margin:0;
            border:0;
        }
    </style>
</head>
<body>

<?php
$mnth = $_GET['m'] ?: date('n'); // (1-12)
$prev_mnth = $mnth - 1;
$next_mnth = $mnth + 1;
if ( $prev_mnth == 0 ) { $prev_mnth = 12; }
if ( $next_mnth == 13 ) { $next_mnth = 1; }

$d = $_GET['d'] ?: date('j'); // (1-31)
$prev_d = $d - 1;
$next_d = $d + 1;
if ( $prev_d == 0 ) { $prev_d = 31; }
if ( $next_d == 32 ) { $next_d = 1; }

$dow = $_GET['dow'] ?: date('w'); // (0-6) (0 = Sunday)
$prev_w = $dow - 1;
$next_w = $dow + 1;
if ( $prev_w == -1 ) { $prev_w = 6; }
if ( $next_w == 7 ) { $next_w = 0; }

$maxcount = 0;
echo '<div class="month">
    <a href="?d='.$d.'&m='.$prev_mnth.'&dow='.$dow.'">Prev Month</a>&nbsp;&nbsp;&nbsp;//&nbsp;&nbsp;&nbsp;
    <a href="?d='.$prev_d.'&m='.$mnth.'&dow='.$dow.'">Prev Day</a>&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
    <a href="?d='.$next_d.'&m='.$mnth.'&dow='.$dow.'">Next Day</a>&nbsp;&nbsp;&nbsp;\\\\&nbsp;&nbsp;&nbsp;
    <a href="?d='.$d.'&m='.$next_mnth.'&dow='.$dow.'">Next Month</a>
</div>';
echo '<div class="month">';
    echo '<div class="day">';
    for ( $h = 0; $h < 24; $h++ )
    {
        echo '<div class="hour">';
        echo $h;
        for ( $m = 0; $m < 60; $m++ )
        {
            $active = '';
            $commands = array();
            foreach ( $crons AS $command => $times )
            {
                if (
                    in_array( $dow, $times['days_of_week'] )
                    && in_array( $m, $times['minutes'] )
                    && in_array( $h, $times['hours'] )
                    && in_array( $d, $times['days'] )
                    && in_array( $mnth, $times['months'] )
                )
                {
                    $active = 'active';
                    $commands[] = $command;
                }
            }

            $count = count( $commands );
            if ( $count > $maxcount )
            {
                $maxcount = $count;
            }
            $this_time = str_pad( $h, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $m, 2, '0', STR_PAD_LEFT );
            $opacity = $count ? $count * 0.05 : 1;
            echo '<div style="opacity:' . $opacity . '" class="minute '. $active . '" title="' . $this_time . ($commands ? ' => ' . "\n" . implode( "\n\n", $commands ) : '' ) . '">';
            if ( $count )
            {
                echo $count;
            }
            echo '</div>';
        }
        echo '</div>';
    }
    echo '<div class="clear">Max count: ' . $maxcount . '</div>';
    echo '</div>';
echo '</div>';
echo '<div class="month">
    <a href="?d='.$d.'&m='.$prev_mnth.'&dow='.$dow.'">Prev Month</a>&nbsp;&nbsp;&nbsp;//&nbsp;&nbsp;&nbsp;
    <a href="?d='.$prev_d.'&m='.$mnth.'&dow='.$dow.'">Prev Day</a>&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;
    <a href="?d='.$next_d.'&m='.$mnth.'&dow='.$dow.'">Next Day</a>&nbsp;&nbsp;&nbsp;\\\\&nbsp;&nbsp;&nbsp;
    <a href="?d='.$d.'&m='.$next_mnth.'&dow='.$dow.'">Next Month</a>
</div>';

?>
</body>
</html>
