import IoRTmobile as iort
import sys

iort.init(sys.argv[1:])

iort.calibrate()
iort.right(1, 90.0)
iort.forward(1, 5.0)
iort.update_pos()
