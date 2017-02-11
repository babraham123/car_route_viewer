import numpy as np
import cv2
import time
cap0 = cv2.VideoCapture(0)
cap1 = cv2.VideoCapture(1)

cap0.set(3, 1920) # WIDTH : MAX 1920, 3 ... CV_CAP_PROF_WIDTH
cap0.set(4, 1080) # HEIGHT: MAX 1080, 4 ... CV_CAP_PROF_HEIGHT
cap0.set(5, 60)   # FPS   : MAX 60,   5 ... CV_CAP_PROF_FPS
cap1.set(3, 1920) # WIDTH : MAX 1920, 3 ... CV_CAP_PROF_WIDTH
cap1.set(4, 1080) # HEIGHT: MAX 1080, 4 ... CV_CAP_PROF_HEIGHT
cap1.set(5, 60)   # FPS   : MAX 60,   5 ... CV_CAP_PROF_FPS


while(True):
# Capture frame-by-frame
  ret0, frame0 = cap0.read()
  assert ret0
  ret1, frame1 = cap1.read()
  assert ret1
  t = str(time.time());

  # Our operations on the frame come here
  gray0 = cv2.cvtColor(frame0, cv2.COLOR_BGR2GRAY)
  gray1 = cv2.cvtColor(frame1, cv2.COLOR_RGB2GRAY)
  # light on check ... (100 might not be a good threshould) ...
  a_row = np.average(gray0, axis=0)
  a_val = np.average(a_row, axis=0)
  if a_val > 100:
    # write the frame to the file
    file0 = "c0_" + t + ".jpg"
    file1 = "c1_" + t + ".jpg"
    #cv2.imwrite(file0, gray0);
    #cv2.imwrite(file1, gray1);

  # Display the resulting frame
  cv2.imshow('frame0',gray0)
  cv2.imshow('frame1',gray1)
  if cv2.waitKey(1) & 0xFF == ord('q'):
    break

# When everything done, release the capture
cap0.release()
cap1.release()
cv2.destroyAllWindows()
