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

foreach ( $lines as $i => $line )
{
    $parts = explode( ' ', $line );
    $time = array_slice($parts, 0, 5);

    $jobs[$i]['time'] = implode( $time, ' ' );
    $jobs[$i]['command'] = implode( array_diff( $parts, $time ), ' ' );
}

// do like 5 at a time?
$chunks = array_chunk( $jobs, 5 );
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
$mnth = date('n'); // (1-12)
$d = date('j'); // (1-31)
$w = date('w');
$maxcount = 0;
echo '<div class="month">Prev Month</div>';
echo '<div class="month">';
    echo '<div class="day">Prev Day</div>';
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
                    in_array( $w, $times['days_of_week'] )
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
    echo '<div class="day">Next Day</div>';
echo '</div>';
echo '<div class="month">Next Month</div>';
?>
</body>
</html>
