(function(){

var _self = this;
var url = "http://cerlab29.andrew.cmu.edu/",
    map = {nodes: [], edges: []},
    username = null,
    currCar = null,
    currRoute = null;

var init = function() {
    getMap();
    getCars();

    // if failure???
    $('#usernameform').submit(function(e) {
        username = $('#usernameinput').val();
        console.log('user: ' + username);
        getRoutes( username );
        e.preventDefault();
    });

    $('#carsTable').on('click', '.clickable-row', function(e) {
        $(this).addClass('active').siblings().removeClass('active');

        currCar = $(this).children('th').first().text(); // .attr('id');
        console.log(currCar);
        enableUploadBtn();
    });

    $('#routesTable').on('click', '.clickable-row', function(e) {
        $(this).addClass('active').siblings().removeClass('active');

        currRoute = $(this).children('th').first().text(); // .attr('id');
        console.log(currRoute);
        getRouteNodes( currRoute );
        enableUploadBtn();
    });

    $('#uploadBtn').on('click', function (e) {
        uploadRoute();
    });

    // $('#clearbtn').click(function() {
    //     // do something
    // });

    console.log('initialized');
}

var getMap = function() {
    map = {nodes: [], edges: []};
    $.post(url + "IoRT/php/car_map_r.php",
        {},
        function(data) {
            map.nodes = dict2list(data.node);
            map.edges = dict2list(data.edge);

            console.log('map: ' + JSON.stringify(map) );
        },
        'json'
    ).fail(function() {
        console.log("car_map_r error");
    });
}

var getCars = function() {
    $.post(url + "IoRT/php/car_r.php",
        {},
        function(data) {
            var cars = [];
            $.each(data.data, function(i, val) {
                cars.push({"r_id":val.r_id, "r_name":val.r_name});
            });

            console.log('cars: ' + JSON.stringify(cars) );
            updateCarTable(cars);
        },
        'json'
    ).fail(function() {
        console.log("car_r error");
    });
}

var getRoutes = function(_user) {
    $.post(url + "IoRT/php/car_prog_r.php",
        JSON.stringify({u_name: _user}),
        function(data) {
            var routes = [];
            $.each(data.data, function(i, val) {
                routes.push({"p_id":val.p_id, "p_name":val.p_name});
            });

            console.log("routes: " + JSON.stringify(routes));
            updateRouteTable(routes);
        },
        'json'
    ).fail(function() {
        console.log("car_prog_r error");
    });
}

var updateCarTable = function(_cars) {
    var template = $('#tCar0');
    var pane = $('#carsPane');
    
    if(pane.is(":visible")) {
        pane.slideUp();
    }

    // delete visible children
    template.parent().children(':visible').remove();

    $.each(_cars, function(i, val) {
        var row = template.clone();
        row.children('th').text(val.r_name);
        row.attr('id', String(val.r_id));
        row.show().appendTo( template.parent() );
    });

    pane.slideDown();
}

var updateRouteTable = function(_routes) {
    var template = $('#tRoute0');
    var pane = $('#routesPane');

    if(pane.is(":visible")) {
        pane.slideUp();
    }

    // delete visible children
    template.parent().children(':visible').remove();

    $.each(_routes, function(i, val) {
        var row = template.clone();
        row.children('th').text(val.p_name);
        row.attr('id', String(val.p_id));
        row.show().appendTo( template.parent() );
    });

    pane.slideDown();
}

// use route name, not id
// node = {"seq":"1","pos":[1910,40],"name":"n020"}
var getRouteNodes = function(_route) {

    $.post(url + "IoRT/php/car_path_r.php",
        JSON.stringify({u_name: username, p_name: _route}),
        function(data) {
            // console.log(JSON.stringify(data));

            var path = data.path;
            path.sort(function(n1, n2) {
                return (n1.seq - n2.seq);
            });

            var routeNodes = [],
                x = [];
                y = [];

            for (var i = 0; i < path.length; i++) {
                routeNodes.push(path[i].name);
                x.push(path[i].pos[0]);
                y.push(path[i].pos[1]);
            }

            console.log("route nodes: " + JSON.stringify(routeNodes));
            updateRouteViz(routeNodes);
        },
        'json'
    );
    // .fail(function() {
    //     console.log("car_path_r error");
    // });
}

var updateRouteViz = function(_nodes) {
    var pane = $('#vizPane');
    if(pane.is(":visible")) {
        pane.slideUp();
    }
    $('#vizText').text('Route Nodes: ' + JSON.stringify(_nodes));

    pane.slideDown();

    createNetworkX(_nodes);
    createSigma(_nodes);    
}

var createNetworkX = function(routeNodes) {
    $('#vizCanvas2').children('svg').remove();
    var graph = new jsnx.Graph();

    $.each(map.nodes, function(i, val) {
        graph.addNode(val.name, {color: 'black'});
    });

    $.each(map.edges, function(i, val) {
        if(($.inArray(val.n1, routeNodes) == -1) || ($.inArray(val.n2, routeNodes) == -1)) {
            graph.addEdge(val.n1, val.n2, {color: 'black'});
        }
    });

    for (var i = 0; i < (routeNodes.length - 1); i++) {
        graph.addEdge(routeNodes[i], routeNodes[i+1], {color: 'green'});
    }

    jsnx.draw(graph, {
        element: '#vizCanvas2',
        withLabels: true,
        nodeStyle: {
            fill: function(d) {
                return d.data.color;
            }
        },
        edgeStyle: { 
            fill: function(d) {
                return d.data.color;
            },
            'stroke-width': 10
        },
        labelStyle: {
            fill: 'white',
            'font-size': '12px'
        },
        stickyDrag: true
    });
}

var createSigma = function(nodes) {
    var data = {nodes: [], edges: []};

    $.each(map.nodes, function(i, n) {
        node = {"id": n.name, "label": n.name, "x": n.pos[0], "y": n.pos[1], "size": 3};
        data.nodes.push(node);
    });

    $.each(map.edges, function(i, e) {
        if(($.inArray(e.n1, nodes) == -1) || ($.inArray(e.n2, nodes) == -1)) {
            edge = {"id": "e"+e.name, "source": e.n1, "target": e.n2, "size": 2, "color": '#000000'};
            data.edges.push(edge);
        }
    });

    for (var i = 0; i < (nodes.length - 1); i++) {
        edge = {"id": "p"+i.toString(), "source": nodes[i], "target": nodes[i+1], "size": 2, "color": '#40a823'};
        data.edges.push(edge);
    }

     var s = new sigma({
        graph: data,
        container: 'vizCanvas',
        settings: {
            maxNodeSize: 3,
            minNodeSize: 3,
            minEdgeSize: 2,
            maxEdgeSize: 2
        }
    });
}

var dict2list = function(_dict) {
    var _list = [];
    $.each(_dict, function(key, val) {
        _list.push(val);
    });
    return _list;
}

var enableUploadBtn = function() {
    if(currCar && currRoute) {
        $('#uploadBtn').prop('disabled', false);
    } else {
        $('#uploadBtn').prop('disabled', true);
    }
}

var uploadRoute = function() {
    if(currCar && currRoute) {
        // TODO: make an API call to cause the car to follow the chosen route

        console.log("Upload (car,route): (" + currCar + ", " + currRoute + ")");
    }
}

init();

})();
