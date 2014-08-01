<?php
require_once( __DIR__ . '/tdcron.php');
require_once( __DIR__ . '/tdcron_entry.php');

// read from file? input? whatever

$lines = array(
);

$colors = array( 'red', 'blue', 'yellow', 'gray', 'green' );

foreach ( $lines as $i => $line )
{
    $parts = explode( ' ', $line );
    $time = array_slice($parts, 0, 5);
    $jobs[$i]['time'] = implode( $time, ' ' );
    $jobs[$i]['command'] = implode( array_diff( $parts, $time ), ' ' );
    $jobs[$i]['color'] = array_shift($colors);

    if ( empty( $jobs[$i]['color'] ) )
    {
        $colors = array( 'red', 'blue', 'yellow', 'gray', 'green' );
    }
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

    foreach ( $months as $month )
    {
        foreach ( $days as $day )
        {
            foreach ( $hours as $i => $hour )
            {
                foreach ( $minutes as $minute )
                {
                    $crons[$job['command']][$month][$day][$hour][$minute] = true;
                    $crons[$job['command']][$month][$day]['time'] = str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $minute, 2, '0', STR_PAD_LEFT );
                    $crons[$job['command']]['days_of_week'] = $days_of_week;
                    $crons[$job['command']]['color'] = $job['color'];
                }
            }
        }
    }
}

echo '<style>
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
    background:green;
}

.day {
    border:2px solid white;
    background:orange;
}

.hour {
    border:2px solid red;
    background:#eee;
    margin-bottom:25px;
    float:left;
    padding:2px;
    text-align:center;
    margin:5px 7px;
    width:2.5%;
}

.minute {
    border:1px solid #ccc;
    text-align:center;
    font-size:smaller;
    padding:2px;
    min-height:12px;
}

.active {
    border:1px solid #000;
    background:#000;
    color:white;
    opacity:0.1;
}

.now {
    border:2px solid red;
}

</style>
';
for ( $mnth = 8; $mnth < 9; $mnth++ )
{
    echo '<div class="month">';
    for ( $d = 5; $d < 6; $d++ )
    {
        echo '<div class="day">';
        for ( $h = 0; $h < 24; $h++ )
        {
            echo '<div class="hour">';
            echo $h;
            for ( $m = 0; $m < 60; $m++ )
            {
                $color = '';
                $active = '';
                $now = '';
                $commands = array();
                foreach ( $crons AS $command => $times )
                {
                    if ( isset( $times[$mnth][$d][$h][$m] ) && in_array( date('w'), $times['days_of_week'] ) )
                    {
                        $active = 'active';
                        $commands[] = $command;
                        $color = $times['color'];
                    }
                }

                if ( $h == date( 'G' ) && str_pad( $m, 2, '0', STR_PAD_LEFT ) == date( 'i' ) )
                {
                    $now = 'now';
                }

                $count = count( $commands );
                $this_time = str_pad( $h, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $m, 2, '0', STR_PAD_LEFT );
                $opacity = $count ? $count * 0.2 : 1;
                echo '<div style="opacity:' . $opacity . '" class="minute '. $active . ' ' . $now . ' ' . $color . '" title="' . $this_time . ($commands ? ' => ' . implode( ', ', $commands ) : '' ) . '">';
                if ( $count )
                {
                    echo $count;
                }
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
    echo '</div>';
}








