import time
import scara as scr
import numpy as np
import matplotlib.pyplot as plt
import copy
import IoRTarm as iort

h_pos = np.r_[300.0, 0.0, 100.0, 0.] # home position
t_pos = np.r_[300.0, 0.0, 100.0, 0.] # target position
p_pos = np.r_[300.0, 0.0, 100.0, 0.] # previous position

def showMove(p, fig, speed=250.):
    global p_pos
    for i in range(len(p)):
        v = np.r_[p[i][0] - p_pos[0], p[i][1] - p_pos[1], p[i][2] - p_pos[2]];
        l = np.linalg.norm(v);
        c = int(l/speed * 50. + 0.5) # 0.01, but each calculation might need another 10ms

        dx = np.linspace(p_pos[0], p[i][0], c)
        dy = np.linspace(p_pos[1], p[i][1], c)
        dz = np.linspace(p_pos[2], p[i][2], c)
        de = np.linspace(p_pos[3], p[i][3], c)

        for j in range(len(dx)):
            plt.cla() # clear panel
            scr.calculateInverseConfig(dx[j], dy[j], dz[j], de[j], fig) # inverse kinematics
            plt.waitforbuttonpress(0.01) # wait input for 10msec.
        # show the final position
        scr.calculateInverseConfig(p[i][0], p[i][1], p[i][2], p[i][3], fig)
        p_pos = copy.copy(p[i]);

def move_arm(camera, arm, fig, speed=250.0):
    status = "Planning"
    print(status)
    iort.set_arm_status(status)
    
    o_key = camera['o_key']
    obj = iort.read_object_2d(0, o_key)
    print("download %d object data" % (len(obj['obj'])))

    # this is for the drawing purpose.
    scr.obj_array = copy.copy(obj['obj'])
    
    # path planning
    # this section is not optimized, please change it to your
    # minimum distance path
    
    g = [] # green
    r = [] # red
    p = [] # path
    
    p.append([300.0, 0.0, 100.0, 0.0])
    for o in obj['obj']:
        if o['o_type'] == 'R':
            r.append(o)
        elif o['o_type'] == 'G':
            g.append(o)
            
    ang = 0.0;
    for i in range(len(g)):
        p.append([r[i]['pos_x'], r[i]['pos_y'], 100.0, ang]);
        p.append([r[i]['pos_x'], r[i]['pos_y'], 5.0, ang]);
        p.append([r[i]['pos_x'], r[i]['pos_y'], 100.0, ang]);
        ##
        ang += (g[i]['rot'] - r[i]['rot']) * np.pi; # this might not the shortest turn
        p.append([g[i]['pos_x'], g[i]['pos_y'], 100.0, ang]);
        p.append([g[i]['pos_x'], g[i]['pos_y'], 10.0, ang]);
        p.append([g[i]['pos_x'], g[i]['pos_y'], 100.0, ang]);

    p.append([300, 0.0, 100., 0.0])

    time.sleep(1)
    
    #print(p)
    # move
    status = "Running"
    print(status)
    iort.set_arm_status(status)
    #
    showMove(p, fig, speed);

    # clear status
    status = "Completed"
    print(status)
    iort.set_arm_status(status)

    
plt_fig = plt.figure('animated_view')
plt_fig.add_subplot(111, projection='3d')

scr.calculateInverseConfig(h_pos[0], h_pos[1], h_pos[2], h_pos[3], plt_fig)
plt.waitforbuttonpress(0.005)

print("RS and IoT 24-662 Robot Control")
iort.register_user()

while 1:
    table = iort.check_table_status();
    camera = iort.check_camera_status();
    arm = iort.check_arm_status();

    tt = time.strptime(str(table['time']), '%Y-%m-%d %H:%M:%S')
    ct = time.strptime(str(camera['time']), '%Y-%m-%d %H:%M:%S')
    at = time.strptime(str(arm['time']), '%Y-%m-%d %H:%M:%S')

    # in PS3/4, please change this if statement to the right synchronization condition
    if arm['status'] == 'Completed' and camera['status'] == 'Completed' and ct > at:
        move_arm(camera, arm, plt_fig)
    time.sleep(1)
