import requests as rq

url = 'http://cerlab29.andrew.cmu.edu/IoRT/php/arm_camera_2d_w2.php'
f = {'file': ('afo.jpg', open('test.jpg', 'rb'))}
r = rq.post(url, files=f)

print r.text
