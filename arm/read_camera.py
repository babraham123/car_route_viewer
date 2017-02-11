# note
# use scikit-image to download image from url
# ubuntu: sudo apt-get install python-skimage
# python: pip install scikit-image

import time
import IoRTarm as iort
import cv2
from skimage import io
from skimage.viewer import ImageViewer

t = time.time()

ts = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(t))
obj = iort.read_camera_2d(1, ts)
print obj
if len(obj) > 0:
    print "downloading %s" % (obj[0]['c_url'])
    image = io.imread(obj[0]['c_url'])
    viewer = ImageViewer(image)
    viewer.show()
    
    cv2.imshow("OpenCV wrong RGB order", image)
    image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    cv2.imshow("OpenCV color correct", image)
    cv2.waitKey(0)
    #cv2.imwrite("test.jpg", image)


