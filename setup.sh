#!/bin/sh

cp --preserve=timestamps index.html /var/www/html/IoRT
cp --preserve=timestamps car/car.html /var/www/html/IoRT/car
cp --preserve=timestamps car/car.jpg /var/www/html/IoRT/car
cp --preserve=timestamps arm/arm.html /var/www/html/IoRT/arm
cp --preserve=timestamps app/app.html /var/www/html/IoRT/app
cp --preserve=timestamps php/car_*.php /var/www/html/IoRT/php
cp --preserve=timestamps php/arm_*.php /var/www/html/IoRT/php
cp --preserve=timestamps php/table_*.php /var/www/html/IoRT/php
cp --preserve=timestamps php/camera_*.php /var/www/html/IoRT/php
cp --preserve=timestamps php/common.php /var/www/html/IoRT/php
cp --preserve=timestamps -r viewer /var/www/html/IoRT/
ls -l /var/www/html/IoRT
ls -l /var/www/html/IoRT/car
ls -l /var/www/html/IoRT/arm
ls -l /var/www/html/IoRT/app
ls -l /var/www/html/IoRT/php
ls -l /var/www/html/IoRT/viewer

