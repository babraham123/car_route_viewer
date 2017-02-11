import httplib, json
import time

headers = { "charset" : "utf-8", "Content-Type": "application/json" }

conn = httplib.HTTPConnection("localhost")

s, ms = divmod(int(time.time()*1000.), 1000)
sample = { "r_id" : 123,
           "p_time": '{}.{:03d}'.format(time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime(s)), ms),

           "pos_x" : 640.0, "pos_y": 380.0, "dir_x": 1.0, "dir_y": 0.0 }

sampleJson = json.dumps(sample, ensure_ascii = 'False')

# Send the JSON data as-is -- we don't need to URL Encode this
conn.request("POST", "/IoRT/api/pos_w.php", sampleJson, headers)

response = conn.getresponse()

print(response.read())

conn.close()
