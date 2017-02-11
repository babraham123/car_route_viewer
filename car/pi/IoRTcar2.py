import RPi.GPIO as gpio;
import time;

pwm12 = None
pwm11 = None
pwm13 = None
pwm15 = None

def gpio_init():
  global pwm12, pwm11, pwm13, pwm15
  gpio.setmode(gpio.BOARD);

  gpio.setup(12, gpio.OUT);
  gpio.setup(11, gpio.OUT);
  gpio.setup(13, gpio.OUT);
  gpio.setup(15, gpio.OUT);

  pwm12 = gpio.PWM(12, 100) # PWM cycle upto 500Hz
  pwm11 = gpio.PWM(11, 100)
  pwm13 = gpio.PWM(13, 100)
  pwm15 = gpio.PWM(15, 100)


def forward(tf, speed):
  global pwm12, pwm11, pwm13, pwm15
  speed = int(speed*100. + 0.5)
  #
  try:
    gpio_init();
    #
    pwm12.start(0)
    pwm11.start(speed)
    pwm13.start(speed)
    pwm15.start(0)
    #
    time.sleep(tf);
  finally:
    gpio.cleanup();

def backward(tf, speed):
  global pwm12, pwm11, pwm13, pwm15
  speed = int(speed * 100. + 0.5)
  #
  try:
    gpio_init();
    #
    pwm12.start(speed)
    pwm11.start(0)
    pwm13.start(0)
    pwm15.start(speed)
    #
    time.sleep(tf);
  finally:
    gpio.cleanup();

def turn_left(tf, speed):
  global pwm12, pwm11, pwm13, pwm15
  speed = int(speed * 100. + 0.5)
  #
  try:
    gpio_init()
    #
    pwm12.start(speed)
    pwm11.start(speed)
    pwm13.start(speed)
    pwm15.start(0)
    #
    time.sleep(tf)
  finally:
    gpio.cleanup();

def turn_right(tf, speed):
  global pwm12, pwm11, pwm13, pwm15
  speed = int(speed * 100. + 0.5)
  #
  try:
    gpio_init()
    #
    pwm12.start(0)
    pwm11.start(speed)
    pwm13.start(speed)
    pwm15.start(speed)
    #
    time.sleep(tf)
  finally:
    gpio.cleanup()

def spin_left(tf, speed):
  global pwm12, pwm11, pwm13, pwm15
  speed = int(speed * 100. + 0.5)
  #
  try:
    gpio_init()
    #
    pwm12.start(speed)
    pwm11.start(0)
    pwm13.start(speed)
    pwm15.start(0)
    #
    time.sleep(tf)
  finally:
    gpio.cleanup()

def spin_right(tf, speed):
  global pwm12, pwm11, pwm13, pwm15
  speed = int(speed * 100. + 0.5)
  try:
    gpio_init()
    #
    pwm12.start(0)
    pwm11.start(speed)
    pwm13.start(0)
    pwm15.start(speed)
    #
    time.sleep(tf)
  finally:
    gpio.cleanup()

def init(argv):
    
def calibrate():
