<?php
$wpchimpcs_time_to_release = wpchimpcs_time_to_release();
if(!$wpchimpcs_time_to_release)
    return;

$wpchimpcs_days_to_release = $wpchimpcs_time_to_release['days'];

// translators: Counter plural days format. %s will be replaced
$wpchimpcs_days_format = __('%sD', 'wpchimp-countdown');
// translators: Counter singular day format. %s will be replaced
$wpchimpcs_day_format = __('%sD', 'wpchimp-countdown');

$wpchimpcs_hours_to_release = $wpchimpcs_time_to_release['hours'];

// translators: Counter plural hours format. %s will be replaced
$wpchimpcs_hours_format = __('%sh', 'wpchimp-countdown');
// translators: Counter singular hour format. %s will be replaced
$wpchimpcs_hour_format = __('%sh', 'wpchimp-countdown');

$wpchimpcs_minutes_to_release = $wpchimpcs_time_to_release['minutes'];

// translators: Counter plural minutes format. %s will be replaced
$wpchimpcs_minutes_format = __('%sm', 'wpchimp-countdown');
// translators: Counter singular day format. %s will be replaced
$wpchimpcs_minute_format = __('%sm', 'wpchimp-countdown');

$wpchimpcs_seconds_to_release = $wpchimpcs_time_to_release['seconds'];

// translators: Counter plural seconds format. %s will be replaced
$wpchimpcs_seconds_format = __('%ss', 'wpchimp-countdown');
// translators: Counter singular second format. %s will be replaced
$wpchimpcs_second_format = __('%ss', 'wpchimp-countdown');

?>
<span id="days" class="part">
    <?php echo sprintf($wpchimpcs_days_to_release == 1 ? $wpchimpcs_day_format : $wpchimpcs_days_format, $wpchimpcs_days_to_release); ?>
</span>
<span id="hours" class="part">
    <?php echo sprintf($wpchimpcs_hours_to_release == 1 ? $wpchimpcs_hour_format : $wpchimpcs_hours_format, $wpchimpcs_hours_to_release); ?>
</span>
<span id="minutes" class="part">
    <?php echo sprintf($wpchimpcs_minutes_to_release == 1 ? $wpchimpcs_minute_format : $wpchimpcs_minutes_format, $wpchimpcs_minutes_to_release); ?>
</span>
<span id="seconds" class="part">
    <?php echo sprintf($wpchimpcs_seconds_to_release == 1 ? $wpchimpcs_second_format : $wpchimpcs_seconds_format, $wpchimpcs_seconds_to_release); ?>
</span>
<script type="text/javascript">
// Inline script to spare a request
(function() {
    var time = {
        days: <?php echo $wpchimpcs_time_to_release['days']; ?>,
        day_format: "<?php echo $wpchimpcs_day_format; ?>",
        days_format: "<?php echo $wpchimpcs_days_format; ?>",

        hours: <?php echo $wpchimpcs_time_to_release['hours']; ?>,
        hour_format: "<?php echo $wpchimpcs_hour_format; ?>",
        hours_format: "<?php echo $wpchimpcs_hours_format; ?>",

        minutes: <?php echo $wpchimpcs_time_to_release['minutes']; ?>,
        minute_format: "<?php echo $wpchimpcs_minute_format; ?>",
        minutes_format: "<?php echo $wpchimpcs_minutes_format; ?>",

        seconds: <?php echo $wpchimpcs_time_to_release['seconds']; ?>,
        second_format: "<?php echo $wpchimpcs_second_format; ?>",
        seconds_format: "<?php echo $wpchimpcs_seconds_format; ?>"
    };

    function format(singular, plural, count)
    {
        return (count === 1 ? singular : plural).replace("%s", count);
    }

    window.setInterval(function() {        
        time.seconds--;
        if(time.seconds < 0)
        {
            time.seconds = 59;
            time.minutes--;

            if(time.minutes < 0)
            {
                time.minutes = 59;
                time.hours--;

                if(time.hours < 0)
                {
                    time.hours = 23;
                    time.days--;
                }
            }
        }

        var days = format(time.day_format, time.days_format, time.days);
        var hours = format(time.hour_format, time.hours_format, time.hours);
        var minutes = format(time.minute_format, time.minutes_format, time.minutes);
        var seconds = format(time.second_format, time.seconds_format, time.seconds);

        document.getElementById("days").innerHTML = days;
        document.getElementById("hours").innerHTML = hours;
        document.getElementById("minutes").innerHTML = minutes;
        document.getElementById("seconds").innerHTML = seconds;
    }, 1000);
})();
</script>