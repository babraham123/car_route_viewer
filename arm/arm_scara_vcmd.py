import sys
#import time
import scara as scr
import numpy as np
import matplotlib.pyplot as plt
import copy
import IoRTarm as iort

h_pos = np.r_[300.0, 0.0, 100.0, 0.] # home position
t_pos = np.r_[300.0, 0.0, 100.0, 0.] # target position
p_pos = np.r_[300.0, 0.0, 100.0, 0.] # previous position

stat = {}

plt_fig = plt.figure('animated_view')
plt_fig.add_subplot(111, projection='3d')

scr.calculateInverseConfig(h_pos[0], h_pos[1], h_pos[2], h_pos[3], plt_fig)
plt.waitforbuttonpress(0.005)

def showMove(t_pos, p_pos, fig, speed=250.):
    v = np.r_[t_pos[0] - p_pos[0], t_pos[1] - p_pos[1], t_pos[2] - p_pos[2]]
    #print(v)
    l = np.linalg.norm(v)

    c = int(l/speed * 100. + 0.5) # 0.01 is x 100.
    #print(c)

    dx = np.linspace(p_pos[0], t_pos[0], c)
    dy = np.linspace(p_pos[1], t_pos[1], c)
    dz = np.linspace(p_pos[2], t_pos[2], c)
    de = np.linspace(p_pos[3], t_pos[3], c)

    for i in range(len(dx)):
        plt.cla()
        scr.calculateInverseConfig(dx[i], dy[i], dz[i], de[i], fig)
        plt.waitforbuttonpress(0.01)

def run_prog(seq, prog, fig):
    global h_pos, t_pos, p_pos
    if seq >= len(prog):
        iort.reset_status()
        return False;
    cmd = prog[seq]
    print("Arm command %s %f %f %f" % (cmd['cmd'], cmd['pos_x'], cmd['pos_y'], cmd['pos_z']))
    if cmd['cmd'] == 'move':
        t_pos[0] = cmd['pos_x']
        t_pos[1] = cmd['pos_y']
        t_pos[2] = cmd['pos_z']
        t_pos[3] = cmd['dir_z'] * np.pi / 180.0;

        showMove(t_pos, p_pos, fig)
        p_pos = copy.copy(t_pos)
    elif cmd['cmd'] == 'home':
        t_pos = copy.copy(h_pos)

        showMove(t_pos, p_pos, fig)
        p_pos = copy.copy(t_pos)
    elif cmd['cmd'] == 'approach':
        t_pos = copy.copy(p_pos)
        t_pos[2] -= cmd['pos_z']
        
        showMove(t_pos, p_pos, fig)
        p_pos = copy.copy(t_pos)
    elif cmd['cmd'] == 'depart':
        t_pos = copy.copy(p_pos)
        t_pos[2] += cmd['pos_z']
        
        showMove(t_pos, p_pos, fig)
        p_pos = copy.copy(t_pos)
    return True

def stop_arm():
    print("Stopping arm")
    # once success
    iort.reset_status()

def build_obj(obj_key):
    data = iort.read_object_2d(None, obj_key)
    #print(data)
    obj_array = []
    obj_array.append({'type': 'table', 'center' : [0,0,0], 'size' : 500})
    for obj in data['obj']:
        obj_array.append({'type': str(obj['o_type']), 'pos' : [obj['pos_x'], obj['pos_y'], obj['rot']]})
    #print(obj_array)
    return obj_array

iort.register_user()
pp = ""
pu = ""
prog = []
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
        if run_prog(stat['p_seq'], prog, plt_fig):
            iort.inc_prog_seq()
    elif stat['status'] == 'Completed':
        # no program on this robot, therefore, wait
        print("Empty program queue")
        plt.waitforbuttonpress(1)
    #print("waiting");

