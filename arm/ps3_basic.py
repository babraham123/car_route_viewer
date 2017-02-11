import IoRTarm as iort

iort.register_user()
data = iort.read_object_2d(1, "ps3-1-2")
print(data)
# ps4
iort.write_object_2d(1, data['obj'])

# path planning

prog_array = []

prog = {'cmd': 'home'}
prog_array.append(prog)

prog = {'cmd': 'move', 'pos_x': 275.0, 'pos_y': 60.0, 'pos_z' : 100.0}
prog_array.append(prog)

prog = {'cmd': 'approach', 'pos_z': 100.0}
prog_array.append(prog)

prog = {'cmd': 'capture'}
prog_array.append(prog)

prog = {'cmd': 'depart', 'pos_z': 100.0}
prog_array.append(prog)

prog = {'cmd': 'move', 'pos_x': 185.0, 'pos_y': 20.0, 'pos_z' : 100.0}
prog_array.append(prog)

prog = {'cmd': 'approach', 'pos_z': 100.0}
prog_array.append(prog)

prog = {'cmd': 'release'}
prog_array.append(prog)

prog = {'cmd': 'depart', 'pos_z': 100.0}
prog_array.append(prog)

iort.write_prog("test2", "tomotake", data['o_key'], prog_array)

prg = iort.read_prog("test2", "tomotake")
#print(prg)

#obj = iort.read_object_2d(2, "ps3-1-2")
#print(obj)
#iort.write_object_2d(4, "tomotake", obj)


