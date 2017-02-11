import IoRTarm as iort
import time

iort.register_user()

val = "Completed"
iort.set_table_status(val)
iort.set_camera_status(val)
iort.set_arm_status(val)
