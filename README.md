# ~~fedifit~~Fediride
Strava to Activitypub Gateway

Very much work in progress

todo:
rename and move files, what a mess 😂

todo:
switch image genration to this later:
https://www.markhneedham.com/blog/2017/04/29/leaflet-strava-polylines-osm/


changes in libs:


league/oauth2-client/src/Token/AccessToken.php
Line 95
if (empty($options['access_token'])) {
    ->     if (empty($options['access_token']) && empty($options['refresh_token'])) {