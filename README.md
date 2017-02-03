# RC Car Route View website
Author: Bereket Abraham
API Author: Tomotake Furuhata

### Access code
```bash
ssh username@cerlab29.andrew.cmu.edu
ls /home/tomotake/IoRT/car
ls /home/tomotake/IoRT/php

ls /var/www/html/IoRT/php/
```

### Query data through API
```bash
# get paths
curl -i -H "Content-Type: application/json" -X POST http://cerlab29.andrew.cmu.edu/IoRT/php/car_r.php --data '{"u_name":"tomotake"}'

# read path
curl -i -H "Content-Type: application/json" -X POST http://cerlab29.andrew.cmu.edu/IoRT/php/car_path_r.php --data '{"u_name":"tomotake", "p_name":"prog1"}'

# read map
curl -i -H "Content-Type: application/json" -X POST http://cerlab29.andrew.cmu.edu/IoRT/php/car_map_r.php --data '{}'

# get camera image
curl -i -H "Content-Type: application/json" -X POST http://cerlab29.andrew.cmu.edu/IoRT/php/car_camera_map_r.php --data '{"c_id":1}'
```

### API Results
```json
{"ret":1,"data":[{"p_name":"prog1","p_id":"1","c_time":"2016-11-15 10:04:36"},{"p_name":"prog2","p_id":"2","c_time":"2016-11-15 10:04:36"}]}

{"result":true,"path":[{"seq":"1","pos_x":"1910","pos_y":"40","name":"n020"},{"seq":"2","pos_x":"1110","pos_y":"240","name":"n052"},{"seq":"3","pos_x":"510","pos_y":"240","name":"n046"},{"seq":"4","pos_x":"10","pos_y":"740","name":"n141"},{"seq":"5","pos_x":"10","pos_y":"1040","name":"n201"}]}
```

### Variables
u_name
p_name, p_id
m_name
r_id, r_name
c_id, c_time
n_name, pos_x, pos_y
e_name, n1_name, n2_name


mysql -u iort --password=ShimadaKenji
use iort;

MariaDB [iort]> select * from car_id;
+------+----------+
| r_id | r_name   |
+------+----------+
|   11 | car11    |
|   30 | car30    |
| 1001 | tomotake |

MariaDB [iort]> select * from car_map;
+--------+------+
| m_name | m_id |
+--------+------+
| all    |    1 |
| simple |    2 |
+--------+------+

MariaDB [iort]> select * from car_map_edge;
+--------+---------+---------+
| e_name | n1_name | n2_name |
+--------+---------+---------+
| 01     | n001    | n006    |
| 02     | n006    | n012    |
| 08     | n146    | n152    |

MariaDB [iort]> select * from car_map_node;
+--------+-------+-------+
| n_name | pos_x | pos_y |
+--------+-------+-------+
| n001   |    10 |    40 |
| n002   |   110 |    40 |
| n003   |   210 |    40 |

MariaDB [iort]> select * from car_map_info;
+------+--------+------+------+
| m_id | e_name | e_w1 | e_w2 |
+------+--------+------+------+
| 1    | 01     |    1 |    1 |
| 1    | 30     |    1 |  0.2 |
| 2    | 34     |    1 |    1 |

MariaDB [iort]> select * from car_prog;
+--------+----------+------+---------------------+
| p_name | u_name   | p_id | c_time              |
+--------+----------+------+---------------------+
| prog1  | tomotake |    1 | 2016-11-15 10:04:36 |
| prog2  | tomotake |    2 | 2016-11-15 10:04:36 |
| NULL   | NULL     |    3 | 2017-02-01 13:13:47 |
+--------+----------+------+---------------------+

MariaDB [iort]> select * from car_prog_path;
+------+-------+-----------+-----------+----------+---------------------+
| p_id | p_seq | tgt_pos_x | tgt_pos_y | tgt_name | c_time              |
+------+-------+-----------+-----------+----------+---------------------+
|    1 |     1 |      1910 |        40 | n020     | 2017-01-27 13:52:34 |
|    1 |     5 |        10 |      1040 | n201     | 2017-01-27 13:52:34 |
|    2 |     1 |        10 |      1040 | n201     | 2017-01-27 13:52:34 |
|    2 |     5 |      1910 |        40 | n020     | 2017-01-27 13:52:34 |

