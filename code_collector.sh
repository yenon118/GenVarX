
cp -r /data/html/Prod/KBCommons_multi/resources/views/system/tools/GenVarX /home/chanye/projects/

mkdir -p /home/chanye/projects/GenVarX/controller
mkdir -p /home/chanye/projects/GenVarX/routes

cp -r /data/html/Prod/KBCommons_multi/app/Http/Controllers/System/Tools/KBCToolsGenVarXController.php /home/chanye/projects/GenVarX/controller/

cp -r /data/html/Prod/KBCommons_multi/public/system/home/GenVarX/* /home/chanye/projects/GenVarX/

grep "GenVarX" /data/html/Prod/KBCommons_multi/routes/web.php > /home/chanye/projects/GenVarX/routes/web.php
