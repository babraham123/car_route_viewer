# note
# use scikit-image to download image from url
# ubuntu: sudo apt-get install python-skimage
# python: pip install scikit-image

import time
import IoRTarm as iort
import cv2
from skimage import io

t = time.time()

ts = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(t))
#obj = iort.write_camera_2d(1, 1, ts, 'robot.jpg')
obj = iort.write_camera_2d(1, 1, ts, 'image.jpg')
#obj = iort.write_camera_2d(1, 1, ts, 'tiger.png')

print obj


