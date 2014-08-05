Visual-Cron
===========

A grid layout of hours and minutes by date to get a heatmap view of how many crons are run at a particular minute.

Ideally there'd be a way to auto-grab the list of crons, but for now, it's just a config array in the crons.php file.

And no, it's not meant to be super clean in the HTML and such dropped in the script. It's more of a proof of concept.

# Install
Copy crons-sample.php to crons.php, fill up the `$jobs` array, and load the page in a browser. Boom.
