from __future__ import print_function

import math
import time
import random
import sys, getopt
import httplib, json
import requests as rq
import os
import IoRTcar2 as iort2

# http header
headers = { "charset" : "utf-8", "Content-Type": "application/json" }

robot_id = 100
username = "24-662"

def reg_car(v1, v2=None):
    if v2 == None:
        v2 = '%s' % username
    print("register car")
    
#def reg_prog(v1, v2):
#   conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
#    pdata = { "u_name" : v1,
#              "p_name" : v2 }
#    jdata = json.dumps(pdata, ensure_ascii = 'False')
#    #print(jdata)
#    conn.request("POST", "/IoRT/php/car_prog_w.php", jdata, headers) # read from DB
#    response = conn.getresponse()
#    #print(response.read())
#    #pdata = json.loads(response.read())

def write_path(v1, v2, v3=None):
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    if v3 == None:
        v3 = '%s' % username
    pdata = { "u_name" : v3,
              "p_name" : v1,
              "path"   : v2 }
    jdata = json.dumps(pdata, ensure_ascii = 'False')
    #print(jdata)
    conn.request("POST", "/IoRT/php/car_path_w.php", jdata, headers) # read from DB
    response = conn.getresponse()
    #print(response.read())
    #pdata = json.loads(response.read())

def read_path(v1, v2=None):
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    if v2 == None:
        v2 = '%s' % username
    pdata = { "u_name" : v2,
              "p_name" : v1 }
    jdata = json.dumps(pdata, ensure_ascii = 'False')
    #print(jdata)
    conn.request("POST", "/IoRT/php/car_path_r.php", jdata, headers) # read from DB
    response = conn.getresponse()
    #print(response.read())
    pdata = json.loads(response.read())
    return pdata["path"]

def read_map(var) :
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    pdata = { "m_name" : var }
    jdata = json.dumps(pdata, ensure_ascii = 'False')
    conn.request("POST", "/IoRT/php/car_map_r.php", jdata, headers) # read from DB
    response = conn.getresponse()
    #print(response.read())
    pdata = json.loads(response.read())
    #print(pdata)
    return pdata

def read_traffic_map(v1, v2=None) :
    if v2 == None:
        v2 = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime())
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    pdata = { "m_name" : v1,
              "t_time" : v2 }
    jdata = json.dumps(pdata, ensure_ascii = 'False')
    #print(jdata)
    conn.request("POST", "/IoRT/php/car_traffic_map_r.php", jdata, headers) # read from DB
    response = conn.getresponse()
    #print(response.read())
    pdata = json.loads(response.read())
    #print(pdata)
    return pdata

def write_map_img(camera, aux, timestamp, file):
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    pdata = { "c_id" : camera,
              "aux" : aux,
              "file" : file,
              "c_time" : timestamp }
    jdata = json.dumps(pdata, ensure_ascii = 'False')
    #print(jdata)
    conn.request("POST", "/IoRT/php/car_map_img_w1.php", jdata, headers) # write db
    response = conn.getresponse()
    #print(response.read())
    pdata = json.loads(response.read())
    if pdata['ret']:
        url = 'http://cerlab29.andrew.cmu.edu/IoRT/php/car_map_img_w2.php'
        fn = os.path.basename(pdata['data']['c_url']);
        #print(fn, file)
        f = {'file': (fn, open(file, 'rb'))}
        r = rq.post(url, files=f)
        return pdata['data']
    else:
        return {}

def read_map_img(camera, ts1=None, ts2=None):
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    pdata = { "c_id" : camera }
    if ts1 != None:
        pdata["ts1"] = ts1
    if ts2 != None:
        pdata["ts2"] = ts2
    jdata = json.dumps(pdata, ensure_ascii = 'False')
    #print(jdata)
    conn.request("POST", "/IoRT/php/car_map_img_r.php", jdata, headers) # read from db
    response = conn.getresponse()
    #print(response.read())
    pdata = json.loads(response.read())
    return pdata;


def init(argv):
    iort2.init(argv)

def calibrate():
    iort2.calibrate()

def write_pos(pos_array):
    conn = httplib.HTTPConnection("cerlab29.andrew.cmu.edu")
    jdata = json.dumps(pos_array, ensure_ascii = 'False')
    # conn = httplib.HTTPConnection("localhost")
    # conn.request("POST", "/IoRT/php/pos_w_echo.php", jdata, headers)
    print(jdata)
    conn.request("POST", "/IoRT/php/car_pos_w.php", jdata, headers) # write to db
    response = conn.getresponse()
    #print(response.read())
    pdata = json.loads(response.read())
    return pdata;
