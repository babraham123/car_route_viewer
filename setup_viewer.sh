#!/bin/sh
rm -r /var/www/html/IoRT/viewer
cp --preserve=timestamps -r viewer /var/www/html/IoRT/
ls -l /var/www/html/IoRT/viewer

