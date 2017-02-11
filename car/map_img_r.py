# note
# use scikit-image to download image from url
# ubuntu: sudo apt-get install python-skimage
# python: pip install scikit-image

import time
import IoRTcar as iort
import cv2
from skimage import io

t = time.time()
s, ms = divmod(int(t*1000.), 1000)


# image capture from 2017 Jan 01 ... default: check car_camera_status table's c_time
ts1 = "2017-01-01 00:00:00.000";
# to Jan 31 ... 
ts2 = "2017-02-01 00:00:00.000";
# until now ... (this is default)
#ts2 = '{}.{:03d}'.format(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(t)), ms)

obj = iort.read_map_img(1, ts1, ts2)
#print obj
ts1 = obj['ts1'];
ts2 = obj['ts2'];
for img in obj['img']:
    print("downloading %s" % (img['c_url']))
    image = io.imread(img['c_url'])
    cv2.imshow("camera data", image)
    #    image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    #    cv2.imshow("color correct", image)
    #cv2.imwrite("test.jpg", image)
cv2.waitKey(0)


