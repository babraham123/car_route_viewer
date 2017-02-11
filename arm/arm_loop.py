import IoRTarm as iort
import time
import win32com.client   # for Denso COM object

# create CAO engine object
eng = win32com.client.Dispatch("CAO.CaoEngine")

print "Connect to controller at 127.0.0.1"
ctrl = eng.Workspaces(0).AddController("RC1", "CaoProv.DENSO.RC8", "",
                                       "Server=127.0.0.1")
Arm1 = ctrl.AddRobot("Arm1", "")

# arm status
status = {}

while 1:
    # check tables on server
    status = iort.check_status()
    print(status)
    if status['cmd'] == 'Stop':
        iort.stop_arm(Arm1)
    elif status['cmd'] == 'Running':
        # send program to robot
        prog = iort.get_prog(status['p_name'])
        print(prog['path'][status['p_seq']])
        if iort.run_prog(status['p_seq'], prog['path'], Arm1):
            iort.inc_prog_seq()
    elif status['cmd'] == 'Completed':
        # no program on this robot, therefore, wait 0.1 sec
        print("No program")
    print("waiting")
    time.sleep(2)
