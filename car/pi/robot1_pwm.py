import RPi.GPIO as gpio
import time

gpio.setmode(gpio.BOARD)

gpio.setup(12, gpio.OUT)
gpio.setup(11, gpio.OUT)
gpio.setup(13, gpio.OUT)
gpio.setup(15, gpio.OUT)

try:
    #gpio.output(12, False)
    #gpio.output(11, True)
    #gpio.output(13, True)
    #gpio.output(15, False)

    pwm12 = gpio.PWM(12, 100) # PWM cycle upto 500Hz
    pwm11 = gpio.PWM(11, 100)
    pwm13 = gpio.PWM(13, 100)
    pwm15 = gpio.PWM(15, 100)

    # full speed (?)
    pwm12.start(0)
    pwm11.start(100)
    pwm13.start(100)
    pwm15.start(0)

    time.sleep(1.0)

    # target: half speed
    #pwm12.ChangeDutyCycle(50)
    pwm11.ChangeDutyCycle(50)
    pwm13.ChangeDutyCycle(50)
    #pwm15.ChangeDutyCycle(50)

    #gpio.output(12, False)
    #gpio.output(11, True)
    #gpio.output(13, True)
    #gpio.output(15, False)

    time.sleep(1.0)

finally:
    gpio.cleanup()
