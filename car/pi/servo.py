import RPi.GPIO as gpio
import time;

gpio.setmode(gpio.BOARD);
#gpio.setmode(gpio.BCM)

pin = 18
gpio.setup(pin, gpio.OUT)

#gpio.output(pin, True);
p = gpio.PWM(pin, 50)
p.start(7.5)

try:
    while True:
        print("ang 90")
        p.ChangeDutyCycle(7.5)
        time.sleep(0.5)
        print("ang 180.")
        p.ChangeDutyCycle(12.5)
        time.sleep(0.5)
        print("ang 90.")
        p.ChangeDutyCycle(7.5)
        time.sleep(0.5)
        print("ang 0.")
        p.ChangeDutyCycle(2.5)
        time.sleep(0.5)

except KeyboardInterrupt:
    p.stop()
    gpio.cleanup()        
