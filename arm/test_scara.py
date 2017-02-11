import scara as scr
import numpy as np
import time
import matplotlib.pyplot as plt

plt_fig = plt.figure('animated_view')
plt_fig.add_subplot(111, projection='3d')

# forward
#s1_move = np.linspace(0, np.pi/2, 100)
#
#for s1 in s1_move:
#    plt.cla()
#    plt_fig = scr.showConfig(s1,s1,0,0, plt_fig)
#    plt.waitforbuttonpress(0.005)

s1_move = np.linspace(50., 300., 100)

for s1 in s1_move:
    plt.cla()
    scr.calculateInverseConfig(s1, 60., 100., 0., plt_fig)
    plt.waitforbuttonpress(0.005)
    
plt.waitforbuttonpress(-1)
