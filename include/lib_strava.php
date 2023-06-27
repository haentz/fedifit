<?php
include $basedir.'/vendor/autoload.php';
require_once('../include/db.inc.php');
require_once $basedir.'/include/lib_activities.php';
require_once $basedir.'/include/lib_images.inc.php';
require_once($basedir.'/include/db_tactivity.inc.php');

use Strava\API\OAuth;
use Strava\API\Exception;
use Strava\API\Service\REST;
use Strava\API\Client;
use GuzzleHttp\Client as GuzzleClient;

//refresh accesstoken
function refreshAccessToken(int $stravaIdUser, String $refreshToken) {
    global $orm;

    global $STRAVA_CLIENT_ID;
    global $STRAVA_CLIENT_SECRET;
    global $stravaRedirectURI;

    $options = [
        'clientId'     => $STRAVA_CLIENT_ID,
        'clientSecret' => $STRAVA_CLIENT_SECRET,
        'redirectUri'  => $stravaRedirectURI
    ];
 

    $oauth = new OAuth($options);
    // request tokens
    $token = $oauth->getAccessToken('refresh_token', [
        'refresh_token' => $refreshToken
    ]);



//https://oauth2-client.thephpleague.com/usage/

   $user =  $orm(User::class)->where('strava_athlete_id')->is($stravaIdUser)
        ->get();

    $user->setStravaAccessToken($token->getToken());
    $expiretime=new DateTime();
    $expiretime->setTimestamp($token->getExpires());
    $user->setStravaExpirationdate($expiretime);
    $user->setStravaRefreshToken($token->getRefreshToken());
    $user->setStravaId($stravaIdUser);

    $orm->save($user); 

    error_log("new access token: ".print_r($user,true));
   
}


function saveActivity($stravaActivity, $user) {
    global $orm;




    $text = $stravaActivity->name;
    $heroimageFilename = "";
    // error_log("id".$stravaActivity->stravaId);
    $activity =  $orm(ActivityTable::class)->where('strava_activity_id')->is($stravaActivity->stravaId)
    ->get();

   
    //if activity_id does not exist yet, create new, otherwise it will be updates
    if($activity==null){
        error_log("Activity is new!");
        $activity = $orm->create(ActivityTable::class);
    } 
    
    
    if($stravaActivity->summary_polyline!="") {
    
        error_log("strava activity has map data",0);
            
            $heroimageFilename = hash('ripemd128', "heroimagesalt".$user->getId().time()).".jpg"; 
            
            if(saveRouteToImage($stravaActivity,$heroimageFilename)) {
                $activity->setHeroImage($heroimageFilename);
            }
            
    } else {
               
            $heroimageFilename = null;
            error_log("strava activity does not have map data",0);
            $text.="<br>soundsoviele km";
        
    }



   
    $activity->setFkiduser($user->getId());
    $activity->setCreationdate(new DateTime());
    $activity->setStrava_activity_id($stravaActivity->stravaId);
    $activity->setText($text);
    if($heroimageFilename!=null) {
        $activity->setHeroImage($heroimageFilename);
    }
    $activity->setReleased(1);
    $activity->setDownloaded(1);
   
    $orm->save($activity);

}


function getNewActivity($activityId, $stravaiduser) {
    //test if $iduser accesstioken still valid
    global $orm;

    $user =  $orm(User::class)->where('strava_athlete_id')->is($stravaiduser)
    ->get();

    //refresh access token
    $accesstokenValidtill = $user->getStravaExpirationdate();
    error_log("accesstoken ".($accesstokenValidtill<new DateTime()?"expired":"not expired"));
    if($accesstokenValidtill<new DateTime()) {
        //expired
        refreshAccessToken($user->getStravaId(),$user->getStravaRefreshToken());
        $user =  $orm(User::class)->where('strava_athlete_id')->is($stravaiduser)
        ->get();
    
    }


    
    //get activity
    // REST adapter (We use `Guzzle` in this project)
   
    
    $adapter = new GuzzleClient(['base_uri' => 'https://www.strava.com/api/v3/']);
    // Service to use (Service\Stub is also available for test purposes)
    $service = new REST($user->getStravaAccessToken(), $adapter);
   
    // Receive the athlete!
    $client = new Client($service);
    $stravaActivity = $client->getActivity($activityId);
    
    $activity = new Activity();
    return $activity->parseActivity($stravaActivity);
    //parse into activity object
    
   
    // save activity to db

}

function updateActivity($activityId, $accessToken) {


}

/*

Array
(
    [resource_state] => 3
    [athlete] => Array
        (
            [id] => 2321457
            [resource_state] => 1
        )

    [name] => Isartrails mit dem Jasper 😁
    [distance] => 23557.4
    [moving_time] => 5429
    [elapsed_time] => 6532
    [total_elevation_gain] => 216
    [type] => Ride
    [sport_type] => MountainBikeRide
    [workout_type] => 10
    [id] => 9332359898
    [start_date] => 2023-06-25T12:18:00Z
    [start_date_local] => 2023-06-25T14:18:00Z
    [timezone] => (GMT+01:00) Europe/Berlin
    [utc_offset] => 7200
    [location_city] => 
    [location_state] => 
    [location_country] => Germany
    [achievement_count] => 21
    [kudos_count] => 1
    [comment_count] => 0
    [athlete_count] => 1
    [photo_count] => 0
    [map] => Array
        (
            [id] => a9332359898
            [polyline] => stwdHcpreA@??KZALBfA{GA]E_@_@mAAO_@{@GICDAAH?GG@c@DULUBOLWTUVe@l@s@d@u@tAaBrAuBd@k@t@eAvAcC~@mA~@yA`BiBzB_Dr@iABCHAJFVn@?FGGCBB^Vx@P`@Ll@Vb@J\`@j@l@dBjAhCNp@Xr@d@x@nAnCj@rAr@nBlAhCRf@Vf@\bANX^b@LVr@lBZfAn@dB^v@h@v@`AxBb@pA|@xAfBhEVf@l@pAb@jAdCrDz@dAl@f@v@~@`@\j@v@PXd@fANTlD`Ef@`@`B|@^NlAPVAh@MbBCp@@fEMRBf@PL?\Ix@c@`DIfCk@b@OLAl@P\?XCv@Qz@MXAd@@XDtAXbAVx@\dAx@pBzBrAhBl@`AdAlAHL\pAd@hAd@r@vA~C`@p@`@v@j@t@Vv@\`@ZX`@z@h@h@f@dAd@v@l@jAj@l@d@tA\r@\nAn@fAHTf@h@r@xANTTFl@j@h@H^Nr@PRHt@LL@JCDGN]DCdAAtBF|AAr@EtAB|FATBr@EnAHnDCtA@n@ApCRLBx@`@n@FhBF^N\DRCb@a@NGJB`@Ab@aAPQPIf@DPJb@H^Rh@PlBtA`@d@jB~Ah@`@b@T^Xf@r@`@t@t@VPLj@n@d@l@rBtAPDTXZTXd@LH^b@PLb@Vj@Pp@^z@z@NH^LTLl@x@PH\Nn@b@\JXDLCX@h@LN?^HL@^Tv@TPNd@p@LXJLPLTTPDb@\JB^h@DCNFPAPd@PV^Rd@NPLRTLDJFd@HXHh@F\JFBJTp@d@PCR@|@j@^JJBb@?RFLATER?F@PTRBJJXCF@\CZ@NDFFb@Jt@XL?FCXCVFXDZOFHVJLHHBNLFJHDPANFVARFXGLIRCJEZ@`AErCu@l@u@RKTA^SV[PIVc@h@[PWRSRCHEJGDK`@GNGj@]h@e@f@WFAFDPA\M\?HAROFADEJ?HJDALETCdAYL?HDRBH?b@KR@NDf@EVBHDLAHHD?ZIFIZGVQZKD@NAFEDIFAVFZELHF?HJFB^Df@ZJDVBTPLXP@ZCD@FHH@PA^V`@CTEXTJBX?JI^?NTL?LJHBDFNJV^Rb@fAxA|@`@d@^VJ^HNXf@NPPPDFFPBF?FE\Df@@LJ\d@PDDFl@LVPH@LCHBJJNBRIH@B@JPb@BNLXCD@HLLBLJ\?LLJOLCLB^CVHb@n@DBPBXTLTPf@DFVHHHJ@JHH@h@\JJPF\RVj@f@r@H@NL`@AX^D^F?BDHFJ@DFNDLP^x@PT^ZHVNv@L`@`@l@Pb@TdAAf@@h@@NJLDf@Jl@Bl@?j@Jj@Hv@J~ABPJLDb@?ZGd@D|@Ll@?f@FjAGj@Af@Lv@?h@Ld@B\GJ?JF@Kl@?VI|@JnA?dBF~@^fBX|@b@dAb@tARTXd@bAxB`@Lj@\j@x@bAl@RNZHn@F`@ATGH@JHVD^\RFl@F|@^PR\f@j@jBf@ZzAOxAxALDn@`@|@PzAjA\H|@?ZN^\~@XP@XGV@XRTXJFT?LFD@DCMZAJD`@DPLPLd@Ff@?X^jATnA`@x@Dl@HZl@zBJL?RD\FRZf@PBBBBRVl@Ld@HJZJ\VNF^b@Xb@RBV^P`@LNh@LJJXFP?j@Ib@DXADE@{@BEd@MT[VMF?BKHCHBP?LIIm@CADPLTCj@DfAl@jJRvDCXEPMN[RIRIj@BRBFD?BE]_@c@Aq@FKCUSi@UOA]A[EmA[UMOO?GIGS_@IEc@KQAi@FY?KKYkAFo@Yw@KQKKSc@[_@Yg@OKQg@k@}@K]MIEDCAKu@I]MWwA{AUOMUIG@GCIDGJALB?Y_@eBSm@I_@KKO_@MQWCIEIGISE@MKo@WW]SIKKWIWCMMW_@a@a@c@aAOUWWWMu@RWGWBo@A{A[m@UwA]MKOEg@Ke@SeA_AMSQK{@y@UQi@WSOq@}@_@]SYi@i@g@_@s@m@KOkAgAY]SOE?IBGNKZEBKIQAO_@KMs@s@ASIQW?a@_@Mg@IK[AEMAa@NK@GKi@?YMMGSQWOICKK?CCCMGICQBYGEAEBMASKM?UKi@UVIBAMOKKUG_@G}@Bs@Ca@Ke@@KGGASGOHc@CMBOK]Cc@@KFE@MGM?a@BGOq@CYHm@TABIKYCQSs@?INYBQ?]I[@WBQAYOSA]ESCYGQ?gAg@sBMgA[u@G]OiA?[BUAGECCGMMMYCDM?MICG?EJUBWDEDO?QMKIWCWEKOQKGQEEu@OKKYBKCAPYTk@AQKMa@OoCaBs@U_@]i@_@g@O}@g@aA]{FkB}DeAc@SkA_@a@Gq@ScBo@k@Oc@Ou@OqDe@]AcABeATc@D{Ad@YJWRe@LgBt@w@Vk@Jc@?a@Ha@?SHM@a@Ro@P_@Bo@AcB^sAPo@?u@LiBRy@PMEG@}@HMBMHU\M\I^Rz@@JN`@Hj@Ph@A`@BJ?NBN?LEHBN@@BADOLkA@w@H_@BG`@MDJHp@Zn@@p@Dj@FNHF\^Xd@\Ft@h@ZJ~@v@RTn@RtAz@PZHd@RRBJ?JGbAGzDK`DObKOlC?tDExA?vAUj@WrAQfBO~@o@rEI^WdCu@bDE|B@rBG|C@r@GnAG\]`Aq@jAQh@s@zCWr@WfBO|AGxB@~@@t@PpBD`CAt@FjA?v@BTApACTe@nBGx@C|@EVMPe@Hi@DQLSl@GXKx@I|@?ZIh@KNMH[FWBIDEHeAnFeAvIk@`DQpAGfAAdBFlABvBBfGEpH?nH@v@?dDDlC@bGGNGBO@gAQO@]MsAWM?OJAH?XFrAAdAKpA?f@DnCKj@Qf@}@lB]dAk@pAa@lAMd@qA~CMn@MZQVUd@MNGBG?e@UIAGBGFoA`Da@nAuBrFw@bBET@NTd@BPENKNCRChABnBAjCMjB?dBEJGD_AJkAFiACG@WRILEl@AnAQvA[tAm@dDMd@O~@o@xCObA_@dBg@rCGRIDMCeBcBWQeA{@GCGBKPwAlFOZGBG?EEeAaAs@k@_@WY[WSGAE@OV
            [resource_state] => 3
            [summary_polyline] => mswdHibseAlAyBhDkEfKsO`BiB|DoFb@v@KBB^v@hCdAlBbDtItBhEvF`N|@tA~BzGhAnBbDdHpEnKdCrDnFbGfAvBlD`EhC~AlB`@~K_@hATvAm@`DIjD{@xANfDe@tC`@|Bt@vDtDfExFlAhD`E|HbAlBdC`DzBhEj@l@`BxEx@|AjBxCbAr@tEdAf@m@pb@BxEx@vEd@fAm@l@@t@sAx@C~Bz@lBtAzFvEhAhBfAd@pA|AdCzAjCpC`ChAz@z@dAd@l@x@~A|@fEh@vAj@pAhBhBjA^h@f@@b@|@jBfA`Dt@dA~@d@A|Av@zBBx@f@zACpBr@|BIxAbAlAJxHuAl@u@hAa@pCqC`Bm@|B{A|AKj@YdCYdFP~CkAzB`@`Bv@LXvAH^Vv@Id@XdAIjAx@rB|C~F`DfBDj@p@|Al@zAHbBb@pAj@XSdAHb@n@p@\d@dAhDfB~@~Az@LX^D^v@\~A|Bf@pBr@pATdA?pAL\v@rJTbAG`AZ~EIrA^dD?XUbCRtF^fB`BxEpBtDlAj@j@x@vA|@lCHbE|An@z@j@jBf@ZzAOxAxAzBx@zAjAzAHzBfAbACz@t@n@DOf@D`@`@hAF`At@zC`@x@NhAx@hCLdAp@n@h@fBrAv@lAjAv@pAnA`@zBEJgA~AcAr@IIm@Nd@@rB`AtOIx@s@v@In@PR]a@uADkAm@wCe@mAsAyBEa@kAB{@Yw@qBuCiAcCWEc@kBeCiCBYX@Ai@{@cDi@}@qD{Bo@MgAoAs@wAi@a@{CHmIaCaDyCsAy@}IcJYOc@r@]KoAaBKe@y@_@Ws@[AGo@PSKcAwAkBGyAWmA_@Ze@oAGsC[}AHaAOaAJ_@Cw@SkAHm@XKc@_BRu@E}Bc@qB?gAyAoGOiABq@g@{@c@KZ{Aa@gAm@_@Eu@[e@f@sAM_@eFgCiA}@gDuAqXqIgFu@kE\sI`DsCTsBp@aSzBm@zA`A~DBtBJOXcDd@Uj@lBNlB`AlAbEjCdCnAr@lB{@r\EfJm@~BsB`Pu@bDQrOiBtEkAnEg@dEExDRfDLbLi@dCQnCoBn@[fA_@|D}At@eAnFcCjQIlDNlME`RHnR_@TiEu@]JB|DEhGOv@uC`H}C~Iu@lAoAGaHrQAZXv@Ur@AdIOvEaFZe@Z
        )

    [trainer] => 
    [commute] => 
    [manual] => 
    [private] => 
    [visibility] => everyone
    [flagged] => 
    [gear_id] => b10355012
    [start_latlng] => Array
        (
            [0] => 48.131466871127
            [1] => 11.568824043497
        )

    [end_latlng] => Array
        (
            [0] => 48.08762608096
            [1] => 11.480455147102
        )

    [average_speed] => 4.339
    [max_speed] => 12.16
    [average_cadence] => 65
    [average_temp] => 27
    [average_watts] => 150.1
    [max_watts] => 869
    [weighted_average_watts] => 167
    [kilojoules] => 815
    [device_watts] => 1
    [has_heartrate] => 1
    [average_heartrate] => 158.2
    [max_heartrate] => 182
    [heartrate_opt_out] => 
    [display_hide_heartrate_option] => 1
    [elev_high] => 589.6
    [elev_low] => 525.8
    [upload_id] => 10010507126
    [upload_id_str] => 10010507126
    [external_id] => garmin_ping_281333323303
    [from_accepted_tag] => 
    [pr_count] => 3
    [total_photo_count] => 1
    [has_kudoed] => 
    [suffer_score] => 227
    [description] => 　1.63 new kilometers
-- From Wandrer.earth
    [calories] => 987
    [perceived_exertion] => 
    [prefer_perceived_exertion] => 
    [segment_efforts] => Array
        (
            [0] => Array
                (
                    [id] => 3108053379413303216
                    [resource_state] => 2
                    [name] => Gayride B.
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 63
                    [moving_time] => 63
                    [start_date] => 2023-06-25T12:20:29Z
                    [start_date_local] => 2023-06-25T14:20:29Z
                    [distance] => 384.7
                    [start_index] => 149
                    [end_index] => 212
                    [average_cadence] => 70.5
                    [device_watts] => 1
                    [average_watts] => 153.7
                    [average_heartrate] => 141.6
                    [max_heartrate] => 145
                    [segment] => Array
                        (
                            [id] => 16958197
                            [resource_state] => 2
                            [name] => Gayride B.
                            [activity_type] => Ride
                            [distance] => 384.7
                            [average_grade] => -0.5
                            [maximum_grade] => 16.3
                            [elevation_high] => 521.9
                            [elevation_low] => 516
                            [start_latlng] => Array
                                (
                                    [0] => 48.131237
                                    [1] => 11.571531
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.128732
                                    [1] => 11.575062
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [1] => Array
                (
                    [id] => 3108053379415152560
                    [resource_state] => 2
                    [name] => Frauenhofer bis Reichenbach
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 53
                    [moving_time] => 53
                    [start_date] => 2023-06-25T12:20:53Z
                    [start_date_local] => 2023-06-25T14:20:53Z
                    [distance] => 343.6
                    [start_index] => 173
                    [end_index] => 226
                    [average_cadence] => 64.8
                    [device_watts] => 1
                    [average_watts] => 147.5
                    [average_heartrate] => 142.4
                    [max_heartrate] => 145
                    [segment] => Array
                        (
                            [id] => 19061003
                            [resource_state] => 2
                            [name] => Frauenhofer bis Reichenbach
                            [activity_type] => Ride
                            [distance] => 343.6
                            [average_grade] => 0.7
                            [maximum_grade] => 13.9
                            [elevation_high] => 523
                            [elevation_low] => 516
                            [start_latlng] => Array
                                (
                                    [0] => 48.130369
                                    [1] => 11.572858
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.12807
                                    [1] => 11.575724
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [2] => Array
                (
                    [id] => 3108053379415057328
                    [resource_state] => 2
                    [name] => Ohl2mittl.Ring
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 402
                    [moving_time] => 377
                    [start_date] => 2023-06-25T12:22:22Z
                    [start_date_local] => 2023-06-25T14:22:22Z
                    [distance] => 2044.5
                    [start_index] => 262
                    [end_index] => 664
                    [average_cadence] => 51.8
                    [device_watts] => 1
                    [average_watts] => 137.5
                    [average_heartrate] => 147.4
                    [max_heartrate] => 158
                    [segment] => Array
                        (
                            [id] => 20518740
                            [resource_state] => 2
                            [name] => Ohl2mittl.Ring
                            [activity_type] => Ride
                            [distance] => 2044.5
                            [average_grade] => 0.2
                            [maximum_grade] => 6.3
                            [elevation_high] => 607.8
                            [elevation_low] => 601.2
                            [start_latlng] => Array
                                (
                                    [0] => 48.126725
                                    [1] => 11.57773
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.113498
                                    [1] => 11.560794
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [3] => Array
                (
                    [id] => 3108053379411267504
                    [resource_state] => 2
                    [name] => Radl-Autobahn - Flussaufwärts
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 626
                    [moving_time] => 605
                    [start_date] => 2023-06-25T12:22:54Z
                    [start_date_local] => 2023-06-25T14:22:54Z
                    [distance] => 3686.7
                    [start_index] => 294
                    [end_index] => 920
                    [average_cadence] => 57.6
                    [device_watts] => 1
                    [average_watts] => 159.1
                    [average_heartrate] => 148.3
                    [max_heartrate] => 158
                    [segment] => Array
                        (
                            [id] => 4120946
                            [resource_state] => 2
                            [name] => Radl-Autobahn - Flussaufwärts
                            [activity_type] => Ride
                            [distance] => 3686.7
                            [average_grade] => 0.4
                            [maximum_grade] => 6.1
                            [elevation_high] => 529.9
                            [elevation_low] => 507.7
                            [start_latlng] => Array
                                (
                                    [0] => 48.126468
                                    [1] => 11.577189
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.101073
                                    [1] => 11.551023
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Munich
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 3
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 3
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [4] => Array
                (
                    [id] => 3108053379413760944
                    [resource_state] => 2
                    [name] => Ohlmueller2Humboldt
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 154
                    [moving_time] => 133
                    [start_date] => 2023-06-25T12:22:56Z
                    [start_date_local] => 2023-06-25T14:22:56Z
                    [distance] => 791.5
                    [start_index] => 296
                    [end_index] => 450
                    [average_cadence] => 49.9
                    [device_watts] => 1
                    [average_watts] => 128.2
                    [average_heartrate] => 146.4
                    [max_heartrate] => 158
                    [segment] => Array
                        (
                            [id] => 10147179
                            [resource_state] => 2
                            [name] => Ohlmueller2Humboldt
                            [activity_type] => Ride
                            [distance] => 791.5
                            [average_grade] => 0.2
                            [maximum_grade] => 7.7
                            [elevation_high] => 525.9
                            [elevation_low] => 514
                            [start_latlng] => Array
                                (
                                    [0] => 48.126402
                                    [1] => 11.577092
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.122128
                                    [1] => 11.56879
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [5] => Array
                (
                    [id] => 3108053379412164528
                    [resource_state] => 2
                    [name] => Humboldt2Eisenbahn
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 130
                    [moving_time] => 130
                    [start_date] => 2023-06-25T12:25:32Z
                    [start_date_local] => 2023-06-25T14:25:32Z
                    [distance] => 647.4
                    [start_index] => 452
                    [end_index] => 582
                    [average_cadence] => 51.4
                    [device_watts] => 1
                    [average_watts] => 137
                    [average_heartrate] => 148.2
                    [max_heartrate] => 154
                    [segment] => Array
                        (
                            [id] => 10147195
                            [resource_state] => 2
                            [name] => Humboldt2Eisenbahn
                            [activity_type] => Ride
                            [distance] => 647.4
                            [average_grade] => 1.2
                            [maximum_grade] => 16.1
                            [elevation_high] => 522.6
                            [elevation_low] => 507.6
                            [start_latlng] => Array
                                (
                                    [0] => 48.122019
                                    [1] => 11.568566
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.117965
                                    [1] => 11.562529
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [6] => Array
                (
                    [id] => 3108053379412842416
                    [resource_state] => 2
                    [name] => Flat Grind
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 727
                    [moving_time] => 654
                    [start_date] => 2023-06-25T12:25:35Z
                    [start_date_local] => 2023-06-25T14:25:35Z
                    [distance] => 4083.94
                    [start_index] => 455
                    [end_index] => 1182
                    [average_cadence] => 53.1
                    [device_watts] => 1
                    [average_watts] => 146.6
                    [average_heartrate] => 149.7
                    [max_heartrate] => 163
                    [segment] => Array
                        (
                            [id] => 2600098
                            [resource_state] => 2
                            [name] => Flat Grind
                            [activity_type] => Ride
                            [distance] => 4083.94
                            [average_grade] => 0.5
                            [maximum_grade] => 15
                            [elevation_high] => 532.8
                            [elevation_low] => 506.6
                            [start_latlng] => Array
                                (
                                    [0] => 48.122203946158
                                    [1] => 11.568109150987
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.091602623507
                                    [1] => 11.550811668869
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Munich
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [7] => Array
                (
                    [id] => 3108053379412519856
                    [resource_state] => 2
                    [name] => Eisenbahn2Flaucher
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 298
                    [moving_time] => 298
                    [start_date] => 2023-06-25T12:27:43Z
                    [start_date_local] => 2023-06-25T14:27:43Z
                    [distance] => 2017.3
                    [start_index] => 583
                    [end_index] => 881
                    [average_cadence] => 63.1
                    [device_watts] => 1
                    [average_watts] => 174.9
                    [average_heartrate] => 148.9
                    [max_heartrate] => 155
                    [segment] => Array
                        (
                            [id] => 10147239
                            [resource_state] => 2
                            [name] => Eisenbahn2Flaucher
                            [activity_type] => Ride
                            [distance] => 2017.3
                            [average_grade] => 0.3
                            [maximum_grade] => 6.2
                            [elevation_high] => 530
                            [elevation_low] => 519.1
                            [start_latlng] => Array
                                (
                                    [0] => 48.117943
                                    [1] => 11.562435
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.102746
                                    [1] => 11.552575
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 3
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 3
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [8] => Array
                (
                    [id] => 3108053379413047216
                    [resource_state] => 2
                    [name] => 2-Brücken-Challange
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 400
                    [moving_time] => 400
                    [start_date] => 2023-06-25T12:29:21Z
                    [start_date_local] => 2023-06-25T14:29:21Z
                    [distance] => 2786.1
                    [start_index] => 681
                    [end_index] => 1081
                    [average_cadence] => 63.4
                    [device_watts] => 1
                    [average_watts] => 177.1
                    [average_heartrate] => 152.6
                    [max_heartrate] => 163
                    [segment] => Array
                        (
                            [id] => 9996745
                            [resource_state] => 2
                            [name] => 2-Brücken-Challange
                            [activity_type] => Ride
                            [distance] => 2786.1
                            [average_grade] => 0.2
                            [maximum_grade] => 8.4
                            [elevation_high] => 530.3
                            [elevation_low] => 518.2
                            [start_latlng] => Array
                                (
                                    [0] => 48.112509
                                    [1] => 11.561099
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.092346
                                    [1] => 11.550761
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 2
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 2
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [9] => Array
                (
                    [id] => 3108053379413574576
                    [resource_state] => 2
                    [name] => Tierpark Sprint Stadtauswärts
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 144
                    [moving_time] => 144
                    [start_date] => 2023-06-25T12:33:37Z
                    [start_date_local] => 2023-06-25T14:33:37Z
                    [distance] => 906.3
                    [start_index] => 937
                    [end_index] => 1081
                    [average_cadence] => 66.1
                    [device_watts] => 1
                    [average_watts] => 178.4
                    [average_heartrate] => 156.4
                    [max_heartrate] => 163
                    [segment] => Array
                        (
                            [id] => 9709394
                            [resource_state] => 2
                            [name] => Tierpark Sprint Stadtauswärts
                            [activity_type] => Ride
                            [distance] => 906.3
                            [average_grade] => 0.3
                            [maximum_grade] => 9.6
                            [elevation_high] => 528
                            [elevation_low] => 525
                            [start_latlng] => Array
                                (
                                    [0] => 48.100377
                                    [1] => 11.551035
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.092297
                                    [1] => 11.550653
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 2
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 2
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [10] => Array
                (
                    [id] => 3108053379413579696
                    [resource_state] => 2
                    [name] => isartrail from zoo part I
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 1190
                    [moving_time] => 907
                    [start_date] => 2023-06-25T12:36:05Z
                    [start_date_local] => 2023-06-25T14:36:05Z
                    [distance] => 4155.4
                    [start_index] => 1085
                    [end_index] => 2275
                    [average_cadence] => 47.2
                    [device_watts] => 1
                    [average_watts] => 143.5
                    [average_heartrate] => 168.4
                    [max_heartrate] => 182
                    [segment] => Array
                        (
                            [id] => 21788895
                            [resource_state] => 2
                            [name] => isartrail from zoo part I
                            [activity_type] => Ride
                            [distance] => 4155.4
                            [average_grade] => 0.1
                            [maximum_grade] => 23.7
                            [elevation_high] => 542
                            [elevation_low] => 525
                            [start_latlng] => Array
                                (
                                    [0] => 48.092242
                                    [1] => 11.550738
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.058299
                                    [1] => 11.538939
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [11] => Array
                (
                    [id] => 3108053379412496304
                    [resource_state] => 2
                    [name] => First Trail Sprint
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 123
                    [moving_time] => 123
                    [start_date] => 2023-06-25T12:37:33Z
                    [start_date_local] => 2023-06-25T14:37:33Z
                    [distance] => 752.8
                    [start_index] => 1173
                    [end_index] => 1296
                    [average_cadence] => 66.2
                    [device_watts] => 1
                    [average_watts] => 247.3
                    [average_heartrate] => 169.4
                    [max_heartrate] => 180
                    [segment] => Array
                        (
                            [id] => 9990461
                            [resource_state] => 2
                            [name] => First Trail Sprint
                            [activity_type] => Ride
                            [distance] => 752.8
                            [average_grade] => 1.4
                            [maximum_grade] => 14.8
                            [elevation_high] => 544.9
                            [elevation_low] => 531.3
                            [start_latlng] => Array
                                (
                                    [0] => 48.09201
                                    [1] => 11.550878
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.086472
                                    [1] => 11.546862
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 2
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 2
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [12] => Array
                (
                    [id] => 3108053379414468528
                    [resource_state] => 2
                    [name] => first sprint
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 348
                    [moving_time] => 268
                    [start_date] => 2023-06-25T12:37:51Z
                    [start_date_local] => 2023-06-25T14:37:51Z
                    [distance] => 1439.9
                    [start_index] => 1191
                    [end_index] => 1539
                    [average_cadence] => 47.7
                    [device_watts] => 1
                    [average_watts] => 167.3
                    [average_heartrate] => 171.1
                    [max_heartrate] => 180
                    [segment] => Array
                        (
                            [id] => 6927280
                            [resource_state] => 2
                            [name] => first sprint
                            [activity_type] => Ride
                            [distance] => 1439.9
                            [average_grade] => 0
                            [maximum_grade] => 21.2
                            [elevation_high] => 547.1
                            [elevation_low] => 535.3
                            [start_latlng] => Array
                                (
                                    [0] => 48.091533
                                    [1] => 11.551206
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.080731
                                    [1] => 11.543354
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Munich
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [13] => Array
                (
                    [id] => 3108053379414308784
                    [resource_state] => 2
                    [name] => ISAR_Trailsprint//APEX
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 668
                    [moving_time] => 535
                    [start_date] => 2023-06-25T12:37:56Z
                    [start_date_local] => 2023-06-25T14:37:56Z
                    [distance] => 2688.3
                    [start_index] => 1196
                    [end_index] => 1864
                    [average_cadence] => 49.5
                    [device_watts] => 1
                    [average_watts] => 158.3
                    [average_heartrate] => 170.6
                    [max_heartrate] => 182
                    [segment] => Array
                        (
                            [id] => 7488936
                            [resource_state] => 2
                            [name] => ISAR_Trailsprint//APEX
                            [activity_type] => Ride
                            [distance] => 2688.3
                            [average_grade] => 0.1
                            [maximum_grade] => 12.4
                            [elevation_high] => 545.3
                            [elevation_low] => 533
                            [start_latlng] => Array
                                (
                                    [0] => 48.0913
                                    [1] => 11.55118
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.069713
                                    [1] => 11.544034
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Munich
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [14] => Array
                (
                    [id] => 3108053379413936048
                    [resource_state] => 2
                    [name] => Chapter I - The TT Tunnel Time!
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 114
                    [moving_time] => 114
                    [start_date] => 2023-06-25T12:47:17Z
                    [start_date_local] => 2023-06-25T14:47:17Z
                    [distance] => 551.1
                    [start_index] => 1757
                    [end_index] => 1871
                    [average_cadence] => 60.7
                    [device_watts] => 1
                    [average_watts] => 173.3
                    [average_heartrate] => 172.1
                    [max_heartrate] => 178
                    [segment] => Array
                        (
                            [id] => 4121003
                            [resource_state] => 2
                            [name] => Chapter I - The TT Tunnel Time!
                            [activity_type] => Ride
                            [distance] => 551.1
                            [average_grade] => 1.3
                            [maximum_grade] => 16.2
                            [elevation_high] => 547.1
                            [elevation_low] => 536.9
                            [start_latlng] => Array
                                (
                                    [0] => 48.074042
                                    [1] => 11.542277
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.069381
                                    [1] => 11.544226
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 2
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 2
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [15] => Array
                (
                    [id] => 3108053379411389360
                    [resource_state] => 2
                    [name] => Short Trail NS
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 496
                    [moving_time] => 419
                    [start_date] => 2023-06-25T12:47:19Z
                    [start_date_local] => 2023-06-25T14:47:19Z
                    [distance] => 1900.7
                    [start_index] => 1759
                    [end_index] => 2255
                    [average_cadence] => 55.2
                    [device_watts] => 1
                    [average_watts] => 156.7
                    [average_heartrate] => 171.7
                    [max_heartrate] => 180
                    [segment] => Array
                        (
                            [id] => 14513517
                            [resource_state] => 2
                            [name] => Short Trail NS
                            [activity_type] => Ride
                            [distance] => 1900.7
                            [average_grade] => 0.2
                            [maximum_grade] => 20
                            [elevation_high] => 495
                            [elevation_low] => 481
                            [start_latlng] => Array
                                (
                                    [0] => 48.073963
                                    [1] => 11.542361
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.058725
                                    [1] => 11.539404
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => München
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 3
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 3
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [16] => Array
                (
                    [id] => 3108053379412162480
                    [resource_state] => 2
                    [name] => Chapter II - Mount Doom (aka Orodruin, Amon Amarth and Sammath Naur)
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 72
                    [moving_time] => 71
                    [start_date] => 2023-06-25T12:50:11Z
                    [start_date_local] => 2023-06-25T14:50:11Z
                    [distance] => 288.4
                    [start_index] => 1931
                    [end_index] => 2003
                    [average_cadence] => 77.8
                    [device_watts] => 1
                    [average_watts] => 225.7
                    [average_heartrate] => 174.1
                    [max_heartrate] => 177
                    [segment] => Array
                        (
                            [id] => 5171698
                            [resource_state] => 2
                            [name] => Chapter II - Mount Doom (aka Orodruin, Amon Amarth and Sammath Naur)
                            [activity_type] => Ride
                            [distance] => 288.4
                            [average_grade] => 1.8
                            [maximum_grade] => 5.7
                            [elevation_high] => 551
                            [elevation_low] => 545.5
                            [start_latlng] => Array
                                (
                                    [0] => 48.068901
                                    [1] => 11.54453
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.066063
                                    [1] => 11.543759
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 1
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 1
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [17] => Array
                (
                    [id] => 3108053379415068592
                    [resource_state] => 2
                    [name] => Shredinger's Cat
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 200
                    [moving_time] => 200
                    [start_date] => 2023-06-25T12:52:34Z
                    [start_date_local] => 2023-06-25T14:52:34Z
                    [distance] => 888.6
                    [start_index] => 2074
                    [end_index] => 2274
                    [average_cadence] => 62.2
                    [device_watts] => 1
                    [average_watts] => 141.5
                    [average_heartrate] => 171.1
                    [max_heartrate] => 176
                    [segment] => Array
                        (
                            [id] => 25117907
                            [resource_state] => 2
                            [name] => Shredinger's Cat
                            [activity_type] => Ride
                            [distance] => 888.6
                            [average_grade] => -0.6
                            [maximum_grade] => 11.9
                            [elevation_high] => 573.6
                            [elevation_low] => 566
                            [start_latlng] => Array
                                (
                                    [0] => 48.065483
                                    [1] => 11.543486
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.058174
                                    [1] => 11.53902
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Grünwald
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 3
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 3
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [18] => Array
                (
                    [id] => 3108053379412289456
                    [resource_state] => 2
                    [name] => Trail Zwischensprint
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 117
                    [moving_time] => 117
                    [start_date] => 2023-06-25T12:53:26Z
                    [start_date_local] => 2023-06-25T14:53:26Z
                    [distance] => 513.5
                    [start_index] => 2126
                    [end_index] => 2243
                    [average_cadence] => 65.3
                    [device_watts] => 1
                    [average_watts] => 157.5
                    [average_heartrate] => 173.4
                    [max_heartrate] => 176
                    [segment] => Array
                        (
                            [id] => 9990518
                            [resource_state] => 2
                            [name] => Trail Zwischensprint
                            [activity_type] => Ride
                            [distance] => 513.5
                            [average_grade] => -0.8
                            [maximum_grade] => 20.1
                            [elevation_high] => 552.1
                            [elevation_low] => 545
                            [start_latlng] => Array
                                (
                                    [0] => 48.063158
                                    [1] => 11.542045
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.059238
                                    [1] => 11.539667
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Grünwald
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 3
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 3
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [19] => Array
                (
                    [id] => 3108053376725168898
                    [resource_state] => 2
                    [name] => offiziell Trail SQ
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 417
                    [moving_time] => 356
                    [start_date] => 2023-06-25T13:01:13Z
                    [start_date_local] => 2023-06-25T15:01:13Z
                    [distance] => 1603.8
                    [start_index] => 2593
                    [end_index] => 3010
                    [average_cadence] => 48
                    [device_watts] => 1
                    [average_watts] => 154.6
                    [average_heartrate] => 165.7
                    [max_heartrate] => 172
                    [segment] => Array
                        (
                            [id] => 10099169
                            [resource_state] => 2
                            [name] => offiziell Trail SQ
                            [activity_type] => Ride
                            [distance] => 1603.8
                            [average_grade] => -1.1
                            [maximum_grade] => 19.1
                            [elevation_high] => 566.5
                            [elevation_low] => 540.4
                            [start_latlng] => Array
                                (
                                    [0] => 48.056209
                                    [1] => 11.528811
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.045739
                                    [1] => 11.517848
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Grünwald
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 1
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 1
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [20] => Array
                (
                    [id] => 3108053376723230466
                    [resource_state] => 2
                    [name] => The Honorary BkM Lap (Buy a Spezi at the Kisok)
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 260
                    [moving_time] => 212
                    [start_date] => 2023-06-25T13:06:04Z
                    [start_date_local] => 2023-06-25T15:06:04Z
                    [distance] => 729.2
                    [start_index] => 2884
                    [end_index] => 3144
                    [average_cadence] => 49.7
                    [device_watts] => 1
                    [average_watts] => 164.6
                    [average_heartrate] => 168.7
                    [max_heartrate] => 182
                    [segment] => Array
                        (
                            [id] => 4121073
                            [resource_state] => 2
                            [name] => The Honorary BkM Lap (Buy a Spezi at the Kisok)
                            [activity_type] => Ride
                            [distance] => 729.2
                            [average_grade] => -0.9
                            [maximum_grade] => 6.2
                            [elevation_high] => 553.1
                            [elevation_low] => 541
                            [start_latlng] => Array
                                (
                                    [0] => 48.048014
                                    [1] => 11.522274
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.043309
                                    [1] => 11.517855
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 1
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 1
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [21] => Array
                (
                    [id] => 3108053376724486914
                    [resource_state] => 2
                    [name] => kleine Traileinlage
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 112
                    [moving_time] => 84
                    [start_date] => 2023-06-25T13:15:39Z
                    [start_date_local] => 2023-06-25T15:15:39Z
                    [distance] => 283
                    [start_index] => 3459
                    [end_index] => 3571
                    [average_cadence] => 32.4
                    [device_watts] => 1
                    [average_watts] => 153.5
                    [average_heartrate] => 165.7
                    [max_heartrate] => 174
                    [segment] => Array
                        (
                            [id] => 8778788
                            [resource_state] => 2
                            [name] => kleine Traileinlage
                            [activity_type] => Ride
                            [distance] => 283
                            [average_grade] => -3.3
                            [maximum_grade] => 18.3
                            [elevation_high] => 543.8
                            [elevation_low] => 528.4
                            [start_latlng] => Array
                                (
                                    [0] => 48.043055
                                    [1] => 11.514163
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.045187
                                    [1] => 11.514953
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach im Isartal
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 3
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 3
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [22] => Array
                (
                    [id] => 3108053376723961602
                    [resource_state] => 2
                    [name] => Grünwalder Brücke - NW Descent
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 168
                    [moving_time] => 137
                    [start_date] => 2023-06-25T13:16:23Z
                    [start_date_local] => 2023-06-25T15:16:23Z
                    [distance] => 578.9
                    [start_index] => 3503
                    [end_index] => 3671
                    [average_cadence] => 32.3
                    [device_watts] => 1
                    [average_watts] => 103
                    [average_heartrate] => 171
                    [max_heartrate] => 178
                    [segment] => Array
                        (
                            [id] => 4121111
                            [resource_state] => 2
                            [name] => Grünwalder Brücke - NW Descent
                            [activity_type] => Ride
                            [distance] => 578.9
                            [average_grade] => -4.7
                            [maximum_grade] => 4
                            [elevation_high] => 580.8
                            [elevation_low] => 550.5
                            [start_latlng] => Array
                                (
                                    [0] => 48.043552
                                    [1] => 11.513942
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.047709
                                    [1] => 11.517935
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [23] => Array
                (
                    [id] => 3108053376724426498
                    [resource_state] => 2
                    [name] => RightSideFred
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 174
                    [moving_time] => 105
                    [start_date] => 2023-06-25T13:19:32Z
                    [start_date_local] => 2023-06-25T15:19:32Z
                    [distance] => 319.4
                    [start_index] => 3692
                    [end_index] => 3866
                    [average_cadence] => 23.2
                    [device_watts] => 1
                    [average_watts] => 57.5
                    [average_heartrate] => 168.1
                    [max_heartrate] => 177
                    [segment] => Array
                        (
                            [id] => 10139684
                            [resource_state] => 2
                            [name] => RightSideFred
                            [activity_type] => Ride
                            [distance] => 319.4
                            [average_grade] => 0.3
                            [maximum_grade] => 9
                            [elevation_high] => 553.6
                            [elevation_low] => 545.3
                            [start_latlng] => Array
                                (
                                    [0] => 48.048035
                                    [1] => 11.51856
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.050061
                                    [1] => 11.52105
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach im Isartal
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [24] => Array
                (
                    [id] => 3108053376723532546
                    [resource_state] => 2
                    [name] => wet snake
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 202
                    [moving_time] => 178
                    [start_date] => 2023-06-25T13:24:01Z
                    [start_date_local] => 2023-06-25T15:24:01Z
                    [distance] => 664
                    [start_index] => 3961
                    [end_index] => 4163
                    [average_cadence] => 34.6
                    [device_watts] => 1
                    [average_watts] => 70.7
                    [average_heartrate] => 159.4
                    [max_heartrate] => 178
                    [segment] => Array
                        (
                            [id] => 18944405
                            [resource_state] => 2
                            [name] => wet snake
                            [activity_type] => Ride
                            [distance] => 664
                            [average_grade] => -2.5
                            [maximum_grade] => 42
                            [elevation_high] => 562.9
                            [elevation_low] => 538.6
                            [start_latlng] => Array
                                (
                                    [0] => 48.053581
                                    [1] => 11.522597
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.057873
                                    [1] => 11.527481
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach im Isartal
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 3
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 3
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [25] => Array
                (
                    [id] => 3108053376723008258
                    [resource_state] => 2
                    [name] => 2. Jungle - Röhre - Double - SQ
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 600
                    [moving_time] => 347
                    [start_date] => 2023-06-25T13:25:06Z
                    [start_date_local] => 2023-06-25T15:25:06Z
                    [distance] => 1103
                    [start_index] => 4026
                    [end_index] => 4626
                    [average_cadence] => 29.7
                    [device_watts] => 1
                    [average_watts] => 85.9
                    [average_heartrate] => 168.1
                    [max_heartrate] => 180
                    [segment] => Array
                        (
                            [id] => 10004036
                            [resource_state] => 2
                            [name] => 2. Jungle - Röhre - Double - SQ
                            [activity_type] => Ride
                            [distance] => 1103
                            [average_grade] => 1
                            [maximum_grade] => 24.1
                            [elevation_high] => 556.5
                            [elevation_low] => 536.7
                            [start_latlng] => Array
                                (
                                    [0] => 48.055975
                                    [1] => 11.525107
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.059366
                                    [1] => 11.535903
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach im Isartal
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [26] => Array
                (
                    [id] => 3108053376723016450
                    [resource_state] => 2
                    [name] => Over the Pipe
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 227
                    [moving_time] => 104
                    [start_date] => 2023-06-25T13:26:44Z
                    [start_date_local] => 2023-06-25T15:26:44Z
                    [distance] => 308.7
                    [start_index] => 4124
                    [end_index] => 4351
                    [average_cadence] => 18.8
                    [device_watts] => 1
                    [average_watts] => 60.1
                    [average_heartrate] => 169.3
                    [max_heartrate] => 178
                    [segment] => Array
                        (
                            [id] => 22106937
                            [resource_state] => 2
                            [name] => Over the Pipe
                            [activity_type] => Ride
                            [distance] => 308.7
                            [average_grade] => -0.1
                            [maximum_grade] => 4.3
                            [elevation_high] => 529.8
                            [elevation_low] => 527.4
                            [start_latlng] => Array
                                (
                                    [0] => 48.057303
                                    [1] => 11.526313
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.058441
                                    [1] => 11.529368
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Grünwald
                            [state] => Bayern
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [27] => Array
                (
                    [id] => 3108053376723135234
                    [resource_state] => 2
                    [name] => Karl Ranseier ist tot.
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 470
                    [moving_time] => 257
                    [start_date] => 2023-06-25T13:28:13Z
                    [start_date_local] => 2023-06-25T15:28:13Z
                    [distance] => 736.7
                    [start_index] => 4213
                    [end_index] => 4683
                    [average_cadence] => 28.5
                    [device_watts] => 1
                    [average_watts] => 85.1
                    [average_heartrate] => 167.5
                    [max_heartrate] => 180
                    [segment] => Array
                        (
                            [id] => 5171358
                            [resource_state] => 2
                            [name] => Karl Ranseier ist tot.
                            [activity_type] => Ride
                            [distance] => 736.7
                            [average_grade] => 0.3
                            [maximum_grade] => 2.2
                            [elevation_high] => 559.6
                            [elevation_low] => 552.8
                            [start_latlng] => Array
                                (
                                    [0] => 48.057891
                                    [1] => 11.528177
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.059878
                                    [1] => 11.536556
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 
                    [achievements] => Array
                        (
                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [28] => Array
                (
                    [id] => 3108053376723763970
                    [resource_state] => 2
                    [name] => Uphill Grosshesseloher Brücke
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 141
                    [moving_time] => 135
                    [start_date] => 2023-06-25T13:44:18Z
                    [start_date_local] => 2023-06-25T15:44:18Z
                    [distance] => 280.7
                    [start_index] => 5178
                    [end_index] => 5319
                    [average_cadence] => 59.1
                    [device_watts] => 1
                    [average_watts] => 237.2
                    [average_heartrate] => 166.8
                    [max_heartrate] => 173
                    [segment] => Array
                        (
                            [id] => 4503089
                            [resource_state] => 2
                            [name] => Uphill Grosshesseloher Brücke
                            [activity_type] => Ride
                            [distance] => 280.7
                            [average_grade] => 2.5
                            [maximum_grade] => 16.8
                            [elevation_high] => 561.6
                            [elevation_low] => 550
                            [start_latlng] => Array
                                (
                                    [0] => 48.07457
                                    [1] => 11.539454
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.07447
                                    [1] => 11.53886
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 2
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 2
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

            [29] => Array
                (
                    [id] => 3108053376725191426
                    [resource_state] => 2
                    [name] => Höllerer Berg Climb
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 80
                    [moving_time] => 80
                    [start_date] => 2023-06-25T13:44:41Z
                    [start_date_local] => 2023-06-25T15:44:41Z
                    [distance] => 121.3
                    [start_index] => 5201
                    [end_index] => 5281
                    [average_cadence] => 62.6
                    [device_watts] => 1
                    [average_watts] => 310.6
                    [average_heartrate] => 167.8
                    [max_heartrate] => 173
                    [segment] => Array
                        (
                            [id] => 6915481
                            [resource_state] => 2
                            [name] => Höllerer Berg Climb
                            [activity_type] => Ride
                            [distance] => 121.3
                            [average_grade] => 9.2
                            [maximum_grade] => 13.7
                            [elevation_high] => 561
                            [elevation_low] => 549.9
                            [start_latlng] => Array
                                (
                                    [0] => 48.074848
                                    [1] => 11.539108
                                )

                            [end_latlng] => Array
                                (
                                    [0] => 48.074625
                                    [1] => 11.537581
                                )

                            [elevation_profile] => 
                            [climb_category] => 0
                            [city] => Pullach
                            [state] => BY
                            [country] => Germany
                            [private] => 
                            [hazardous] => 
                            [starred] => 
                        )

                    [pr_rank] => 3
                    [achievements] => Array
                        (
                            [0] => Array
                                (
                                    [type_id] => 3
                                    [type] => pr
                                    [rank] => 3
                                )

                        )

                    [kom_rank] => 
                    [hidden] => 
                )

        )

    [splits_metric] => Array
        (
            [0] => Array
                (
                    [distance] => 1001.6
                    [elapsed_time] => 292
                    [elevation_difference] => 3.6
                    [moving_time] => 259
                    [split] => 1
                    [average_speed] => 3.87
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 141.54090909091
                    [pace_zone] => 0
                )

            [1] => Array
                (
                    [distance] => 999.3
                    [elapsed_time] => 187
                    [elevation_difference] => 0.2
                    [moving_time] => 166
                    [split] => 2
                    [average_speed] => 6.02
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 147.64457831325
                    [pace_zone] => 0
                )

            [2] => Array
                (
                    [distance] => 1000.2
                    [elapsed_time] => 184
                    [elevation_difference] => 2.4
                    [moving_time] => 184
                    [split] => 3
                    [average_speed] => 5.44
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 147.08152173913
                    [pace_zone] => 0
                )

            [3] => Array
                (
                    [distance] => 1002.2
                    [elapsed_time] => 146
                    [elevation_difference] => 1.6
                    [moving_time] => 146
                    [split] => 4
                    [average_speed] => 6.86
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 148.8904109589
                    [pace_zone] => 0
                )

            [4] => Array
                (
                    [distance] => 999
                    [elapsed_time] => 162
                    [elevation_difference] => 5.4
                    [moving_time] => 162
                    [split] => 5
                    [average_speed] => 6.17
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 153.5
                    [pace_zone] => 0
                )

            [5] => Array
                (
                    [distance] => 1001.1
                    [elapsed_time] => 252
                    [elevation_difference] => 4.2
                    [moving_time] => 189
                    [split] => 6
                    [average_speed] => 5.3
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 156.1061452514
                    [pace_zone] => 0
                )

            [6] => Array
                (
                    [distance] => 999.1
                    [elapsed_time] => 278
                    [elevation_difference] => 0.4
                    [moving_time] => 212
                    [split] => 7
                    [average_speed] => 4.71
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 173.56060606061
                    [pace_zone] => 0
                )

            [7] => Array
                (
                    [distance] => 997.6
                    [elapsed_time] => 269
                    [elevation_difference] => 3.2
                    [moving_time] => 216
                    [split] => 8
                    [average_speed] => 4.62
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 171.53240740741
                    [pace_zone] => 0
                )

            [8] => Array
                (
                    [distance] => 1003.9
                    [elapsed_time] => 319
                    [elevation_difference] => 8
                    [moving_time] => 243
                    [split] => 9
                    [average_speed] => 4.13
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 172.51239669421
                    [pace_zone] => 0
                )

            [9] => Array
                (
                    [distance] => 997.5
                    [elapsed_time] => 362
                    [elevation_difference] => 2.6
                    [moving_time] => 252
                    [split] => 10
                    [average_speed] => 3.96
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 168.96385542169
                    [pace_zone] => 0
                )

            [10] => Array
                (
                    [distance] => 1000.7
                    [elapsed_time] => 202
                    [elevation_difference] => -0.2
                    [moving_time] => 202
                    [split] => 11
                    [average_speed] => 4.95
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 165.7135678392
                    [pace_zone] => 0
                )

            [11] => Array
                (
                    [distance] => 1000.5
                    [elapsed_time] => 300
                    [elevation_difference] => -2.6
                    [moving_time] => 244
                    [split] => 12
                    [average_speed] => 4.1
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 166.48971193416
                    [pace_zone] => 0
                )

            [12] => Array
                (
                    [distance] => 1002.6
                    [elapsed_time] => 558
                    [elevation_difference] => 19.8
                    [moving_time] => 330
                    [split] => 13
                    [average_speed] => 3.04
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 163.13907284768
                    [pace_zone] => 0
                )

            [13] => Array
                (
                    [distance] => 996.2
                    [elapsed_time] => 364
                    [elevation_difference] => -17
                    [moving_time] => 275
                    [split] => 14
                    [average_speed] => 3.62
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 170.48484848485
                    [pace_zone] => 0
                )

            [14] => Array
                (
                    [distance] => 998.6
                    [elapsed_time] => 279
                    [elevation_difference] => -4.2
                    [moving_time] => 279
                    [split] => 15
                    [average_speed] => 3.58
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 159.06274509804
                    [pace_zone] => 0
                )

            [15] => Array
                (
                    [distance] => 1001.2
                    [elapsed_time] => 679
                    [elevation_difference] => -1
                    [moving_time] => 391
                    [split] => 16
                    [average_speed] => 2.56
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 168.04545454545
                    [pace_zone] => 0
                )

            [16] => Array
                (
                    [distance] => 1001.1
                    [elapsed_time] => 210
                    [elevation_difference] => -2.6
                    [moving_time] => 210
                    [split] => 17
                    [average_speed] => 4.77
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 150.56666666667
                    [pace_zone] => 0
                )

            [17] => Array
                (
                    [distance] => 998.7
                    [elapsed_time] => 308
                    [elevation_difference] => 26.2
                    [moving_time] => 308
                    [split] => 18
                    [average_speed] => 3.24
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 160.33443708609
                    [pace_zone] => 0
                )

            [18] => Array
                (
                    [distance] => 999.4
                    [elapsed_time] => 226
                    [elevation_difference] => 11
                    [moving_time] => 226
                    [split] => 19
                    [average_speed] => 4.42
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 156.37168141593
                    [pace_zone] => 0
                )

            [19] => Array
                (
                    [distance] => 1000.8
                    [elapsed_time] => 219
                    [elevation_difference] => 2.2
                    [moving_time] => 219
                    [split] => 20
                    [average_speed] => 4.57
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 147.24299065421
                    [pace_zone] => 0
                )

            [20] => Array
                (
                    [distance] => 1001
                    [elapsed_time] => 188
                    [elevation_difference] => -3.4
                    [moving_time] => 188
                    [split] => 21
                    [average_speed] => 5.32
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 145.88829787234
                    [pace_zone] => 0
                )

            [21] => Array
                (
                    [distance] => 998
                    [elapsed_time] => 192
                    [elevation_difference] => -2.8
                    [moving_time] => 192
                    [split] => 22
                    [average_speed] => 5.2
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 145.375
                    [pace_zone] => 0
                )

            [22] => Array
                (
                    [distance] => 1000.4
                    [elapsed_time] => 216
                    [elevation_difference] => -2.2
                    [moving_time] => 216
                    [split] => 23
                    [average_speed] => 4.63
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 143.31944444444
                    [pace_zone] => 0
                )

            [23] => Array
                (
                    [distance] => 556.7
                    [elapsed_time] => 140
                    [elevation_difference] => -1.6
                    [moving_time] => 120
                    [split] => 24
                    [average_speed] => 4.64
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 139.40833333333
                    [pace_zone] => 0
                )

        )

    [splits_standard] => Array
        (
            [0] => Array
                (
                    [distance] => 1612.4
                    [elapsed_time] => 419
                    [elevation_difference] => 3.4
                    [moving_time] => 365
                    [split] => 1
                    [average_speed] => 4.42
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 142.57055214724
                    [pace_zone] => 0
                )

            [1] => Array
                (
                    [distance] => 1607.2
                    [elapsed_time] => 276
                    [elevation_difference] => 2.4
                    [moving_time] => 276
                    [split] => 2
                    [average_speed] => 5.82
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 148.73188405797
                    [pace_zone] => 0
                )

            [2] => Array
                (
                    [distance] => 1611.5
                    [elapsed_time] => 250
                    [elevation_difference] => 8
                    [moving_time] => 250
                    [split] => 3
                    [average_speed] => 6.45
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 150.708
                    [pace_zone] => 0
                )

            [3] => Array
                (
                    [distance] => 1611.9
                    [elapsed_time] => 349
                    [elevation_difference] => 3.4
                    [moving_time] => 286
                    [split] => 4
                    [average_speed] => 5.64
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 161.50724637681
                    [pace_zone] => 0
                )

            [4] => Array
                (
                    [distance] => 1607.1
                    [elapsed_time] => 484
                    [elevation_difference] => 2.4
                    [moving_time] => 365
                    [split] => 5
                    [average_speed] => 4.4
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 171.66666666667
                    [pace_zone] => 0
                )

            [5] => Array
                (
                    [distance] => 1609.6
                    [elapsed_time] => 460
                    [elevation_difference] => 9
                    [moving_time] => 384
                    [split] => 6
                    [average_speed] => 4.19
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 172.36553524804
                    [pace_zone] => 0
                )

            [6] => Array
                (
                    [distance] => 1609.8
                    [elapsed_time] => 477
                    [elevation_difference] => 5.4
                    [moving_time] => 367
                    [split] => 7
                    [average_speed] => 4.39
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 165.88611111111
                    [pace_zone] => 0
                )

            [7] => Array
                (
                    [distance] => 1606.5
                    [elapsed_time] => 760
                    [elevation_difference] => 15.4
                    [moving_time] => 476
                    [split] => 8
                    [average_speed] => 3.38
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 164.03571428571
                    [pace_zone] => 0
                )

            [8] => Array
                (
                    [distance] => 1609.9
                    [elapsed_time] => 506
                    [elevation_difference] => -20
                    [moving_time] => 417
                    [split] => 9
                    [average_speed] => 3.86
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 166.67980295567
                    [pace_zone] => 0
                )

            [9] => Array
                (
                    [distance] => 1608.7
                    [elapsed_time] => 874
                    [elevation_difference] => -3
                    [moving_time] => 586
                    [split] => 10
                    [average_speed] => 2.75
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 165.07456978967
                    [pace_zone] => 0
                )

            [10] => Array
                (
                    [distance] => 1608.4
                    [elapsed_time] => 348
                    [elevation_difference] => -0.6
                    [moving_time] => 348
                    [split] => 11
                    [average_speed] => 4.62
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 151.76023391813
                    [pace_zone] => 0
                )

            [11] => Array
                (
                    [distance] => 1611.8
                    [elapsed_time] => 435
                    [elevation_difference] => 35
                    [moving_time] => 435
                    [split] => 12
                    [average_speed] => 3.71
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 159.08735632184
                    [pace_zone] => 0
                )

            [12] => Array
                (
                    [distance] => 1609.7
                    [elapsed_time] => 331
                    [elevation_difference] => -0.8
                    [moving_time] => 331
                    [split] => 13
                    [average_speed] => 4.86
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 146.26380368098
                    [pace_zone] => 0
                )

            [13] => Array
                (
                    [distance] => 1609.7
                    [elapsed_time] => 328
                    [elevation_difference] => -2
                    [moving_time] => 328
                    [split] => 14
                    [average_speed] => 4.91
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 145.35365853659
                    [pace_zone] => 0
                )

            [14] => Array
                (
                    [distance] => 1023.2
                    [elapsed_time] => 235
                    [elevation_difference] => -4.8
                    [moving_time] => 215
                    [split] => 15
                    [average_speed] => 4.76
                    [average_grade_adjusted_speed] => 
                    [average_heartrate] => 140.02325581395
                    [pace_zone] => 0
                )

        )

    [laps] => Array
        (
            [0] => Array
                (
                    [id] => 31832399290
                    [resource_state] => 2
                    [name] => Lap 1
                    [activity] => Array
                        (
                            [id] => 9332359898
                            [resource_state] => 1
                        )

                    [athlete] => Array
                        (
                            [id] => 2321457
                            [resource_state] => 1
                        )

                    [elapsed_time] => 6531
                    [moving_time] => 6531
                    [start_date] => 2023-06-25T12:18:00Z
                    [start_date_local] => 2023-06-25T14:18:00Z
                    [distance] => 23557.4
                    [start_index] => 0
                    [end_index] => 6178
                    [total_elevation_gain] => 207.4
                    [average_speed] => 3.61
                    [max_speed] => 12.16
                    [average_cadence] => 65
                    [device_watts] => 1
                    [average_watts] => 150.1
                    [average_heartrate] => 158.2
                    [max_heartrate] => 182
                    [lap_index] => 1
                    [split] => 1
                )

        )

    [gear] => Array
        (
            [id] => b10355012
            [primary] => 
            [name] => Tarvo
            [nickname] => Tarvo
            [resource_state] => 2
            [retired] => 
            [distance] => 929326
            [converted_distance] => 929.3
        )

    [photos] => Array
        (
            [primary] => Array
                (
                    [unique_id] => 6413c343-984f-44d5-91ea-a7cc90db7919
                    [urls] => Array
                        (
                            [600] => https://dgtzuqphqg23d.cloudfront.net/fVdlGFYROvGe0Jmt_DyqBpGpWDq8hu1JuscparqH8GU-433x768.jpg
                            [100] => https://dgtzuqphqg23d.cloudfront.net/fVdlGFYROvGe0Jmt_DyqBpGpWDq8hu1JuscparqH8GU-72x128.jpg
                        )

                    [source] => 1
                    [media_type] => 1
                )

            [use_primary_photo] => 1
            [count] => 1
        )

    [stats_visibility] => Array
        (
            [0] => Array
                (
                    [type] => heart_rate
                    [visibility] => everyone
                )

            [1] => Array
                (
                    [type] => pace
                    [visibility] => everyone
                )

            [2] => Array
                (
                    [type] => power
                    [visibility] => everyone
                )

            [3] => Array
                (
                    [type] => speed
                    [visibility] => everyone
                )

            [4] => Array
                (
                    [type] => calories
                    [visibility] => everyone
                )

        )

    [hide_from_home] => 
    [device_name] => Garmin Edge 530
    [embed_token] => 08fc025f654c8ff4bb302f3c301e1fe6fd8a66c5
    [private_note] => 
    [available_zones] => Array
        (
            [0] => heartrate
            [1] => power
        )

)


*/
?>