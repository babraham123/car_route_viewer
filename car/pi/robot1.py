import RPi.GPIO as gpio;
import time;

gpio.setmode(gpio.BOARD);

gpio.setup(12, gpio.OUT);
gpio.setup(11, gpio.OUT);
gpio.setup(13, gpio.OUT);
gpio.setup(15, gpio.OUT);

gpio.output(12, False);
gpio.output(11, True);
gpio.output(13, True);
gpio.output(15, False);

time.sleep(1.0);

gpio.cleanup();
