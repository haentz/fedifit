<?php

Class Activity {
    
    public int $stravaId;
    public int $stravaAthleteId;
    public String $name;
    public int $distance;
    public int $movingTime;
    public int $elevationGain;
    public int $averageSpeed;
    public int $calories;
    public String $summary_polyline;

    // not parsed from strava:
    public int $id;
    public int $idUser; //fediride iduser
    public String $text;

    public function parseActivity($stravaActivity):Activity {
        $this->stravaId = $stravaActivity['id'];
        $this->stravaAthleteId = $stravaActivity['athlete']['id'];
        $this->name = $stravaActivity['name'];
        $this->distance = floor($stravaActivity['distance']/1000);  // meters
        $this->movingTime = floor($stravaActivity["moving_time"])/60;  // minutes
        $this->elevationGain = $stravaActivity["total_elevation_gain"]; // meters
        $this->averageSpeed = round($this->distance/$this->movingTime*60,1); // <km>
        $this->calories = floor($stravaActivity['kilojoules']/4);
        $this->summary_polyline = $stravaActivity['map']['summary_polyline'];

        
        return $this;
    }

}

/* User


                //                 Array
                // (
                //     [resource_state] => 2
                //     [athlete] => Array
                //         (
                //             [id] => 2321457
                //             [resource_state] => 1
                //         )

                //     [name] => Pullach im Isartal -  Evening Gravel Ride
                //     [distance] => 5000.5
                //     [moving_time] => 1273
                //     [elapsed_time] => 1297
                //     [total_elevation_gain] => 17
                //     [type] => Ride
                //     [sport_type] => GravelRide
                //     [workout_type] => 
                //     [id] => 9308924239
                //     [start_date] => 2023-06-21T17:18:45Z
                //     [start_date_local] => 2023-06-21T19:18:45Z
                //     [timezone] => (GMT+01:00) Europe/Berlin
                //     [utc_offset] => 7200
                //     [location_city] => 
                //     [location_state] => 
                //     [location_country] => Germany
                //     [achievement_count] => 0
                //     [kudos_count] => 2
                //     [comment_count] => 0
                //     [athlete_count] => 1
                //     [photo_count] => 0
                //     [map] => Array
                //         (
                //             [id] => a9308924239
                //             [summary_polyline] => {xmdHsqieA_ArBYd@_@v@o@hAe@pAwB|Ee@|AWj@Mb@e@hB]`Ag@~AQd@sAnF_AjDQf@Oz@y@vC{@nDkAbEoAdFUt@Qv@Eb@Sv@AJ@JNTd@Tj@b@l@j@fApAV^Xv@Zh@X\j@f@T`@`@d@XTJLTx@P`AJXb@jCr@hHJrB@n@DTHFD?^G`@C\KbAIF@D^JTJj@PlAL\RZpCfC\Ft@`An@p@FTGd@@l@Yn@ANTj@XtA@z@HfANx@BDH@XGN@j@Vx@J`@Bb@HPN^Np@J^CJ@JNHPDj@Jp@?lDCTCp@AnBG`@Ux@GPS\wApE}@`CS^K^[|@]dAu@fAQ?_@OG?GBMNw@rBs@`B_@hAeCnG[j@Cd@R\DXAPMPCXElCBl@Ed@?~@Y~BAPBz@Cl@KFMBeCHkACMJGP
                //             [resource_state] => 2
                //         )

                //     [trainer] => 
                //     [commute] => 
                //     [manual] => 
                //     [private] => 
                //     [visibility] => everyone
                //     [flagged] => 
                //     [gear_id] => b6717517
                //     [start_latlng] => Array
                //         (
                //             [0] => 48.08
                //             [1] => 11.53
                //         )

                //     [end_latlng] => Array
                //         (
                //             [0] => 48.09
                //             [1] => 11.48
                //         )

                //     [average_speed] => 3.928
                //     [max_speed] => 6.492
                //     [average_cadence] => 53.6
                //     [average_temp] => 28
                //     [average_watts] => 53.3
                //     [max_watts] => 330
                //     [weighted_average_watts] => 74
                //     [kilojoules] => 67.8   Energie durch 4,184 dividieren -> kalorien
                //     [device_watts] => 1
                //     [has_heartrate] => 
                //     [heartrate_opt_out] => 
                //     [display_hide_heartrate_option] => 
                //     [elev_high] => 574.8
                //     [elev_low] => 564.6
                //     [upload_id] => 9985732414
                //     [upload_id_str] => 9985732414
                //     [external_id] => garmin_ping_280705960980
                //     [from_accepted_tag] => 
                //     [pr_count] => 0
                //     [total_photo_count] => 0
                //     [has_kudoed] => 

 
                */


function loadActivity(int $stravaActivityId) {


    

}
?>