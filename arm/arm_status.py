import IoRTarm as iort
import time

iort.register_user()

while 1:
    stat = iort.check_arm_status();
    #print("arm id: %d status: %s (%s), program %s by %s line %s" % (stat['r_id'], stat['status'], stat['time'], stat['p_name'], stat['u_name'], stat['p_seq']))
    print("arm id: %d status: %s (%s)" % (stat['r_id'], stat['status'], stat['time']))
    time.sleep(0.5)
