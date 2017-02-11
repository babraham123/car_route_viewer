!/bin/sh
python table_text.py &
echo "kill " $! > ~/p_list.txt
python camera_text.py &
echo "kill " $! >> ~/p_list.txt
python arm_scara.py &
echo "kill " $! >> ~/p_list.txt
python cell_status.py &
echo "kill " $! >> ~/p_list.txt
python table_btn.py &
echo "kill " $! >> ~/p_list.txt
cat ~/p_list.txt
