import IoRTarm as iort
import time

# connect to  "tomotake" environment
iort.register_user()
while 1:
    stat = iort.check_table_status();
    print("table id: %d status: %s (%s), position %d" % (stat['t_id'], stat['status'], stat['time'], stat['pos']))
    time.sleep(0.5)
