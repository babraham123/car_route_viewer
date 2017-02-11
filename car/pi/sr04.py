#

# reference
# https://www.modmypi.com/blog/hc-sr04-ultrasonic-range-sensor-on-the-raspberry-pi

import RPi.GPIO as gpio
import time

gpio.setmode(gpio.BCM)

# sr04 trig is directly connect to GPIO
trig = 21
# sr04 echo is connected 1k -> GPIO -> 2k -> GND
echo = 23

gpio.setup(trig, gpio.OUT)
gpio.setup(echo, gpio.IN)

gpio.output(trig, False)
print "Waiting censor to settle"
time.sleep(1)

try:
    gpio.output(trig, True)
    time.sleep(0.00001)
    gpio.output(trig, False)


    while gpio.input(echo) == 0:
        pulse_start = time.time()

    while gpio.input(echo) == 1:
        pulse_end = time.time()

    pulse_duration = plus_end - plus_start

    distance = pluse_duration * 17150

    distance = round(distance, 2)

    print "Distance:", distance, "cm"
    gpio.cleanup()
except:
    gpio.cleanup()
