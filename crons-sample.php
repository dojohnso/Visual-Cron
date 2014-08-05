<?php

/*
$lines = array(
    '0 5 * * * dummy run',
    '* * * * * other dummy job',
    ...
);
*/
$lines = array(
    '0 10 * * * ./jobone.sh',
    '*/5 * * * * scheduler-phase-completions',
);
