import IoRTarm as iort
import time

iort.register_user()
while 1:
    stat = iort.check_camera_status();
    print("camera id: %d status: %s (%s), i_key %d, c_key %d" % (stat['c_id'], stat['status'], stat['time'], stat['i_key'], stat['o_key']))
    time.sleep(0.5)
