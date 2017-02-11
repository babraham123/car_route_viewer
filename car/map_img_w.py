# note
# use scikit-image to download image from url
# ubuntu: sudo apt-get install python-skimage
# python: pip install scikit-image

import time
import numpy as np
import cv2
import os
from skimage import io
import IoRTcar as iort

cap0 = cv2.VideoCapture(0)
cap1 = cv2.VideoCapture(1)

cap0.set(3, 1920) # WIDTH : MAX 1920, 3 ... CV_CAP_PROF_WIDTH
cap0.set(4, 1080) # HEIGHT: MAX 1080, 4 ... CV_CAP_PROF_HEIGHT
cap0.set(5, 60)   # FPS   : MAX 60,   5 ... CV_CAP_PROF_FPS
cap1.set(3, 1920) # WIDTH : MAX 1920, 3 ... CV_CAP_PROF_WIDTH
cap1.set(4, 1080) # HEIGHT: MAX 1080, 4 ... CV_CAP_PROF_HEIGHT
cap1.set(5, 60)   # FPS   : MAX 60,   5 ... CV_CAP_PROF_FPS

ret0, frame0 = cap0.read()
assert ret0
ret1, frame1 = cap1.read()
assert ret1

gray0 = cv2.cvtColor(frame0, cv2.COLOR_BGR2GRAY)
gray1 = cv2.cvtColor(frame1, cv2.COLOR_RGB2GRAY)

t = time.time()
s, ms = divmod(int(t*1000.), 1000)
ts = '{}.{:03d}'.format(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(t)), ms)

file0 = "c0_" + str(long(time.time()*1000.)) + ".jpg"
file1 = "c1_" + str(long(time.time()*1000.)) + ".jpg"

cv2.imwrite(file0, gray0);
cv2.imwrite(file1, gray1);

iort.write_map_img(1, 0, ts, file0)
iort.write_map_img(2, 0, ts, file1)

cap0.release()
cap1.release()

os.remove(file0)
os.remove(file1)
