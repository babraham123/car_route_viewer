from __future__ import print_function

import math
import time
import random
import sys, getopt
import httplib, json

# http header
headers = { "charset" : "utf-8", "Content-Type": "application/json" }

fw_r = 10 # 10 pixel / second
bw_r = 10 # 10 pixel / second
tr_r = 10. # turn right: 10 deg / second
tl_r = 10. # turn light: 10 deg / second
sr_r = 45 # spin right: 45 deg / second
sl_r = 45 # spin left:45 deg / second

dir = [0.0, 1.0] # toward y
pos = [0.0, 0.0] #
noise = 0.01     # noise of the each movement
dir_offset = 0.  #
cps = 10       # cycle per second
calibrated = 0
prog_time = 0.0
robot_id = 100

def store_status():
    global prog_time, pos, dir, robot_id
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    s, ms = divmod(int(prog_time*1000.), 1000)
    pdata = []
    pdata.append({ "r_id"  : robot_id,
                   "c_time": '{}.{:03d}'.format(time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime(s)), ms),
                   "pos" : pos,
                   "dir" : dir,
                   "c_key" : 0,
                   "c_url" : "" })

    jdata = json.dumps(pdata, ensure_ascii = 'False')
    # conn = httplib.HTTPConnection("localhost")
    # conn.request("POST", "/IoRT/php/pos_w_echo.php", jdata, headers)
    # print(jdata)
    conn.request("POST", "/IoRT/php/car_pos_w.php", jdata, headers) # write to db
    response = conn.getresponse()
    #print(response.read())
    conn.close()

    
def update_pos():
    # simulator does not need to update pos
    # prog_time = time.time();
    global prog_time, pos, dir, robot_id
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    pdata = { "r_id" : robot_id }
    jdata = json.dumps(pdata, ensure_ascii = 'False')
    conn.request("POST", "/IoRT/php/car_pos_r.php", jdata, headers) # read from DB
    response = conn.getresponse()
    pdata = json.loads(response.read())
    print("last position is (", pdata["pos_array"][0]["pos_x"], ",", pdata["pos_array"][0]["pos_y"], ")")
#    store_status()

def set_pos(val):
    global pos
    # real mobile robot ignore this
    pos[0] = val[0]
    pos[1] = val[1]
    
def inc_time():
    global prog_time
    # time.sleep(1.0 / cps)
    prog_time += 1.0 / cps

def move(d, tf, speed):
    global calibrated, id, pos, dir, fw_r, bw_r
    if calibrated == 0:
        print("[Warning] this robot is not calibrated")
    vec = [0.0, 0.0]
    if d == 0:
        vec[0] = dir[0] * fw_r / cps
        vec[1] = dir[1] * fw_r / cps
    else:
        vec[0] = -dir[0] * bw_r / cps
        vec[1] = -dir[1] * bw_r / cps
    #speed = float(speed) * 0.01
    vec[0] *= speed
    vec[1] *= speed
    
    for i in range(int(tf * cps)):
        store_status()
        pos[0] += vec[0]
        pos[1] += vec[1]
        inc_time()

    store_status()

def turn(d, tf, speed):
    global calibrated, id, pos, dir, fw_r, bw_r
    if calibrated == 0:
        print("[Warning] this robot is not calibrated")
    vec = [0.0, 0.0]
    if d == 0:
        delta = tr_r / cps / 180.0 * math.pi
    else:
        delta = - tl_r / cps / 180.0 * math.pi
    #speed = float(speed) * 0.01
    delta = delta * speed
    
    for i in range(int(tf * cps)):
        store_status()
        # change angle
        change_dir(delta)
        # move along direction
        if d == 0:
            vec[0] = dir[0] * fw_r / cps
            vec[1] = dir[1] * fw_r / cps
        else:
            vec[0] = -dir[0] * bw_r / cps
            vec[1] = -dir[1] * bw_r / cps
        vec[0] *= speed
        vec[1] *= speed
        pos[0] += vec[0]
        pos[1] += vec[1]
        
        inc_time()

    store_status()

def change_dir(delta):
    global dir
    ang = math.atan2(dir[1], dir[0])
    ang += delta
    dir[0] = math.cos(ang)
    dir[1] = math.sin(ang)

def rotate(d, tf, speed):
    global calibrated, pos, dir, sr_r, sl_r, cps

    
    if calibrated == 0:
        print("[Warning] this robot is not calibrated")

    if d == 0:
        delta = sr_r / cps / 180.0 * math.pi
    else:
        delta = - sl_r / cps / 180.0 * math.pi
    #speed = float(speed) * 0.01
    delta = delta * speed
    
    for i in range(int(tf * cps)):
        store_status()
        change_dir(delta)
        inc_time()

    store_status()


def forward(tf, speed):
    move(0, tf, speed)

def backward(tf, speed):
    move(1, tf, speed)

def turn_right(tf, speed):
    turn(0, tf, speed)

def turn_left(tf, speed):
    turn(1, tf, speed)

def spin_right(tf, speed):
    rotate(0, tf, speed)

def spin_left(tf, speed):
    rotate(1, tf, speed)

        
def init(argv):
    global fw_r, bw_r, tr_r, tl_r, sr_r, sl_r, pos, dir, dir_offset, calibrated, robot_id, prog_time
    fw_r = 10.0 * random.random()
    bw_r = 10.0 * random.random()
    tr_r = 10.0 * random.random()
    tl_r = 10.0 * random.random()
    sr_r = 45.0 * random.random()
    sl_r = 45.0 * random.random()
    pos[0] = 540.0
    pos[1] = 960.0
    dir[0] = 1.0
    dir[1] = 0.
    dir_offset = random.random();
    calibrated = 0
    robot_id = 100 + int(random.random() * 100.)
    prog_time = time.time();
    try:
        opts, args = getopt.getopt(argv,"r:", ["robotid="])
    except getopt.GetoptError:
        print("IoRTmobile.py argument parse error")
        sys.exit(2)
    for opt, arg in opts:
        if opt in ("-r", "--robotid"):
            robot_id = int(arg)
    #store_status()


def calibrate():
    global fw_r, bw_r, tr_r, tl_r, sr_r, sl_r, calibrated, prog_time
    fw_r = 10.0
    bw_r = 10.0
    tr_r = 10.0
    tl_r = 10.0
    sr_r = 45.0
    sl_r = 45.0
    calibrated = 1
    dir_offset = 0
    prog_time = time.time();
    #store_status()
        
    
