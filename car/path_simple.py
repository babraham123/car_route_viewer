import IoRTcar as iort
import sys
import math
import networkx as nx

import matplotlib as mpl
mpl.use('Agg') # Agg backend insted of X
import matplotlib.pyplot as plt


def dist(n1, n2):
    dx = float(n1['pos_x']) - float(n2['pos_x']);
    dy = float(n1['pos_y']) - float(n2['pos_y']);
    return math.sqrt(dx*dx + dy*dy)

def searchEdge(array, val):
    for edg in array:
        if edg['e_name'] == val:
            return edg

def searchNode(array, val):
    for nod in array:
        if nod['n_name'] == val:
            return nod

def gen_graph(pdata) :
    ret = nx.DiGraph()
#    for n in pdata['node']:
#        ret.add_node(n['n_name'])

    for info in pdata['info']:
        edge = searchEdge(pdata['edge'], info['e_name'])
        #print(info['e_name'], edge)
        n1 = searchNode(pdata['node'], edge['n1_name'])
        n2 = searchNode(pdata['node'], edge['n2_name'])
        ret.add_node(n1['n_name'])
        ret.add_node(n2['n_name'])
        #print(edge['n1_name'], n1, edge['n2_name'], n2)
        l = dist(n1, n2)
        w1 = float(info['e_w1'])
        w2 = float(info['e_w2'])
        if w1 >= 0.01 and w1 <= 1.0 :
            ret.add_edge(n1['n_name'], n2['n_name'], weight = l/w1)
        if w2 >= 0.01 and w2 <= 1.0 :
            ret.add_edge(n2['n_name'], n1['n_name'], weight = l/w2)
    return ret

# main
iort.init(sys.argv[1:])


pdata = iort.read_map("simple")
#print(pdata)
# list to dictionary
node = {}
for n in pdata['node']:
    node[n['n_name']] = { 'name' : n['n_name'], 'pos_x' : float(n['pos_x']), 'pos_y' : float(n['pos_y']) }
    
G = gen_graph(pdata)

#print(G.nodes())
#print(G.edges(data=True))
#print(G.edges())

# print shortest path
nlist = nx.dijkstra_path(G, source='n001', target='n220')
print(nlist)


#path1 = []
#for n in range(len(nlist)):
#    n1 = n+1
#    #print(nlist[n])
#    path1.append({ 'seq': n1,
#                   'pos_x': node[nlist[n]]['pos_x'],
#                   'pos_y': node[nlist[n]]['pos_y'],
#                   'name' : node[nlist[n]]['name'] })

#print(path1)

#iort.reg_path("tomotake", "prog1", int(100), path1)
#path = iort.read_path("tomotake", "prog1")
#print(path)

nlist = nx.dijkstra_path(G, source='n220', target='n001')
print(nlist)

#pos = nx.spring_layout(G)
#nx.draw(G)
#plt.savefig("map.png")

