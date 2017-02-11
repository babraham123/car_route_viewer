import IoRTarm as iort
import sys
import time

def run_prog(seq, prog):
    if seq >= len(prog):
        iort.reset_status()
        return False;
    cmd = prog[seq]
    print("Arm command %s %f %f %f" % (cmd['cmd'], cmd['pos_x'], cmd['pos_y'], cmd['pos_z']))
    return True

def stop_arm():
    print("Stopping arm")
    # once success
    iort.reset_status()
    
def build_obj(obj_key):
    data = iort.read_object_2d(None, None, obj_key)
    #print(data)
    obj_array = []
    obj_array.append({'type': 'table', 'center' : [0,0,0], 'size' : 500})
    for obj in data['obj']:
        obj_array.append({'type': str(obj['o_type']), 'pos' : [obj['pos_x'], obj['pos_y'], obj['rot']]})
    #print(obj_array)
    return obj_array

stat = {}

iort.register_user()

pp = ""
pu = ""
prog = []
obj_key = 0;
obj_tbl = []

while 1:
    stat = iort.check_status();
    #print(stat)
    if stat['status'] == 'Stop':
        stop_arm()
    elif stat['status'] == 'Running':
        # send program to robot (I am not sure how this program works in non-blocking mode)
        if stat['p_name'] != pp or stat['u_name'] != pu:
            print("program changed! Owner:%s Program: %s" % (stat['p_name'], stat['u_name']))
            data = iort.read_prog(stat['p_name'], stat['u_name'])
            pp = stat['p_name']
            pu = stat['u_name']
            obj_tbl = build_obj(int(data['o_key']))
            prog = data['path']
        if run_prog(stat['p_seq'], prog):
            iort.inc_prog_seq()
    elif stat['status'] == 'Completed':
        # no program on this robot, therefore, wait
        print("Empty program queue")
    #print("waiting");
    time.sleep(2)

