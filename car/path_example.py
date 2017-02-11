import IoRTcar as iort
import sys
import math
import networkx as nx

import matplotlib as mpl
mpl.use('Agg') # Agg backend insted of X
import matplotlib.pyplot as plt


def dist(n1, n2):
    dx = n1['pos'][0] - n2['pos'][0]
    dy = n1['pos'][1] - n2['pos'][1]
    return math.sqrt(dx*dx + dy*dy)

def gen_graph(pdata) :
    ret = nx.DiGraph()

    for k in pdata['node']:
        n = pdata['node'][k]
        ret.add_node(n['name'])

    for k in pdata['traffic']:
        edge = pdata['edge'][k]
        n1 = pdata['node'][edge['n1']]
        n2 = pdata['node'][edge['n2']]
        l = dist(n1, n2)
        w1 = pdata['traffic'][k][0]
        w2 = pdata['traffic'][k][1]
        #print(edge, n1, n2, w1, w2)
        if w1 >= 0.01 and w1 <= 1.0 :
            ret.add_edge(n1['name'], n2['name'], weight = l/w1)
        if w2 >= 0.01 and w2 <= 1.0 :
            ret.add_edge(n2['name'], n1['name'], weight = l/w2)
    return ret

# main
#iort.init(sys.argv[1:])

pdata = iort.read_traffic_map("all")
#pdata = iort.read_traffic_map("all", "2017-01-01 00:00:00")
#pdata = iort.read_traffic_map("all", "2017-02-07 10:00:00")

#print(pdata)
#print(pdata['node'])
#print(pdata['traffic'])
print(pdata['traffic']['30'])

# list to dictionary
G = gen_graph(pdata)


#print(G.nodes())
#print(G.edges(data=True))
#print(G.edges())


print("02/07/2017 10am car_map_traffic_status has asynchronous speed")
print("Edge 30: between node n152 and n060, n152->n060 is 100%, n060->n152 is 20%")
print("Shortest time path of n020->n201 and n201->n020 result different route")

# print shortest path
nlist = nx.dijkstra_path(G, source='n020', target='n201')
print(nlist)

path1 = []
for n in range(len(nlist)):
    path1.append({ 'seq': (n+1),
                   'pos': pdata['node'][nlist[n]]['pos'],
                   'name' : pdata['node'][nlist[n]]['name'] })

#print(path1)

iort.write_path("prog1", path1)
path = iort.read_path("prog1")

#print(path)

nlist = nx.dijkstra_path(G, source='n201', target='n020')
# print shortest path
print(nlist)

path2 = []
for n in range(len(nlist)):
    n1 = n+1
    path2.append({ 'seq': n1,
                   'pos': pdata['node'][nlist[n]]['pos'],
                   'name' : pdata['node'][nlist[n]]['name'] })

#print(path2)

iort.write_path("prog2", path2)
path = iort.read_path("prog2")

#print(path)

# following is to plot map...

#nx.draw(G)
#plt.savefig("map.png")

#
#elarge=[(u,v) for (u,v,d) in G.edges(data=True) if d['weight'] >0.5]
#esmall=[(u,v) for (u,v,d) in G.edges(data=True) if d['weight'] <=0.5]

#pos = nx.spring_layout(G) # positions for all nodes

# nodes
#nx.draw_networkx_nodes(G,pos, node_size=700)

# edges
#nx.draw_networkx_edges(G, pos, edgelist=elarge,
#                    width=6)
#nx.draw_networkx_edges(G, pos, edgelist=esmall,
#                    width=6,alpha=0.5,edge_color='b',style='dashed')

# labels
#nx.draw_networkx_labels(G,pos,font_size=20,font_family='sans-serif')
#
#plt.axis('off')
#plt.savefig("weighted_graph.png") # save as png
#plt.show() 
