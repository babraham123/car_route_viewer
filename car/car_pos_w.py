# note
# use scikit-image to download image from url
# ubuntu: sudo apt-get install python-skimage
# python: pip install scikit-image

import time
import IoRTcar as iort
import cv2
from skimage import io

# image capture from 2017 Jan 01 ... default: check car_camera_status table's c_time
ts1 = "2017-01-01 00:00:00.000";
# to Jan 31 ... 
ts2 = "2017-02-01 00:00:00.000";

# until now ... (this is default)
#t = time.time()
#s, ms = divmod(int(t*1000.), 1000)
#ts2 = '{}.{:03d}'.format(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(t)), ms)

obj = iort.read_map_img(1, ts1, ts2)
#print obj
ts1 = obj['ts1'];
ts2 = obj['ts2'];
pos_array = []
for img in obj['img']:
    print("downloading %s" % (img['c_url']))
    image = io.imread(img['c_url'])
    #cv2.imshow("camera data", image)
    #    image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    #    cv2.imshow("color correct", image)
    #cv2.imwrite("test.jpg", image)
    # image processing to register car 
    # to Mabaran: please gen r_id, pos[2], dir[2] and make array
    pos_array.append({'r_id': 11, 'c_time': img['c_time'], 'pos':[100.,100.],
                     'dir': [1., 0.], 'c_key': img['c_key'], 'c_url': img['c_url'] })
    pos_array.append({'r_id': 12, 'c_time': img['c_time'], 'pos':[200.,200.],
                     'dir': [0., 1.], 'c_key': img['c_key'], 'c_url': img['c_url'] })
#print(pos_array)
r = iort.write_pos(pos_array);
print(r);




