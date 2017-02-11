import IoRTarm as iort

iort.register_user()

data = iort.read_object_2d(1, "ps3-1-2")
print(data)
iort.write_object_2d(1, data['obj'])


