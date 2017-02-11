import IoRTmobile as iort
import sys

iort.init(sys.argv[1:])

iort.calibrate()

iort.update_pos()
iort.forward(1, 10.0)

iort.update_pos()
iort.right(1, 90.0)
iort.forward(1, 10.0)

iort.update_pos()
iort.right(1, 90.0)
iort.forward(1, 10.0)

iort.update_pos()
iort.right(1, 90.0)
iort.forward(1, 20.0)

iort.update_pos()
iort.left(1, 90.0)
iort.forward(1, 10.0)

iort.update_pos()
iort.left(1, 90.0)
iort.forward(1, 10.0)

iort.update_pos()
iort.left(1, 90.0)
iort.forward(1, 10.0)
iort.update_pos()
