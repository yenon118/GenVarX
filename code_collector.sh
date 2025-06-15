
cp -r /data/sites/KBCommons/resources/views/system/tools/GenVarX /home/chanye/projects/

mkdir -p /home/chanye/projects/GenVarX/controller
mkdir -p /home/chanye/projects/GenVarX/routes

cp -r /data/sites/KBCommons/app/Http/Controllers/System/Tools/KBCToolsGenVarXController.php /home/chanye/projects/GenVarX/controller/

cp -r /data/sites/KBCommons/public/system/home/GenVarX/* /home/chanye/projects/GenVarX/

grep "GenVarX" /data/sites/KBCommons/routes/web.php > /home/chanye/projects/GenVarX/routes/web.php
