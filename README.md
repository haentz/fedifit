# ~~fedifit~~Fediride
Strava to Activitypub Gateway





Very much work in progress



rewrite /.well-known/webfinger to /.well-known/webfinger.php
nginx:
       location /.well-known/webfinger {
                try_files $uri $uri/ /.well-known/webfinger.php?$args;
        }


todo:
rename and move files, what a mess 😂

todo:
image genration with base php gdlib is incredibly ugly
switch image genration to headless JS or imagemagick:
https://www.markhneedham.com/blog/2017/04/29/leaflet-strava-polylines-osm/


changes in libs:
league/oauth2-client/src/Token/AccessToken.php
Line 95
if (empty($options['access_token'])) {
    ->     if (empty($options['access_token']) && empty($options['refresh_token'])) {










       ## Activitypubb:
https://knuspermagier.de/posts/2022/der-kirby-blog-als-fediverse-teilnehmer-in-vierhundert-einfachen-schritten
        > "Servers performing delivery to the inbox or sharedInbox properties of actors on other servers MUST provide the object property in the activity: Create, Update, Delete, Follow, Add, Remove, Like, Block, > > Undo. Additionally, servers performing server to server delivery of the following activities **MUST also provide the target property: Add, Remove.**"


