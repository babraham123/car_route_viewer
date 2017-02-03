(function(){

var _self = this;
var url = "http://cerlab29.andrew.cmu.edu/",
    map = {nodes: [], edges: []},
    username = null,
    currCarId = null;

var init = function() {
    // if failure???
    $('#usernameform').submit(function(e) {
        username = $('#usernameform input').text();
        getRoutes( username );
        e.preventDefault();
    });

    $('#carsTable').on('click', '.clickable-row', function(e) {
        $(this).addClass('active').siblings().removeClass('active');
        var currCarId = $(this).attr('id');
    });

    $('#routesTable').on('click', '.clickable-row', function(e) {
        $(this).addClass('active').siblings().removeClass('active');
        var currRoute = $(this).children('th').first().value(); // .attr('id');
        getRouteNodes( currRoute );
    });

    // $('#clearbtn').click(function() {
    //     // do something
    // });

    getMap();
    getCars();
    console.log('initialized');
}

var getMap = function() {
    map = {nodes: [], edges: []};
    $.post(url + "IoRT/php/car_map_r.php",
        {},
        function(data) {
            map.nodes = data.node;
            map.edges = data.edge;
        }
    );
}

var getCars = function() {
    var cars = [];
    $.post(url + "IoRT/php/car_r.php",
        {},
        function(data) {
            $.each(data.data, function(i, val) {
                cars.append({"r_id":val.r_id, "r_name":val.r_name});
            });

            updateCarTable(cars);
        }
    );
}

var getRoutes = function(_user) {
    var routes = [];
    $.post(url + "IoRT/php/car_prog_r.php",
        {"u_name":_user},
        function(data) {
            $.each(data.data, function(i, val) {
                routes.append({"p_id":val.p_id, "p_name":val.p_name});
            });

            updateRouteTable(routes);
        }
    );
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
        row.children('th').value(val.r_name);
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
        row.children('th').value(val.p_name);
        row.attr('id', String(val.p_id));
        row.show().appendTo( template.parent() );
    });

    pane.slideDown();
}

// use route name, not id
// node = {"seq":"1","pos_x":"1910","pos_y":"40","name":"n020"}
var getRouteNodes = function(_route) {
    var routeNodes = [],
        x = [];
        y = [];

    $.post(url + "IoRT/php/car_path_r.php",
        {"u_name":username, "p_name": _route},
        function(data) {
            var path = data.path;
            path.sort(function(n1, n2) {
                return (n1.seq - n2.seq);
            });

            for (i = 0; i < path.length; i++) {
                routeNodes.append(path[i].name);
                x.append(path[i].pos_x);
                y.append(path[i].pos_y);
            }

            updateRouteViz(routeNodes);
        }
    );
}

var updateRouteViz = function(routeNodes) {
    var pane = $('#vizPane');

    if(pane.is(":visible")) {
        pane.slideUp();
    }

    var graph = new jsnx.Graph();
    pane.children('canvas').remove();

    $.each(map.nodes, function(i, val) {
        graph.addNode(val.n_name, {color: 'black'});
    });

    for (i = 0; i < (routeNodes.length - 1); i++) {
        graph.addEdge(routeNodes[i], routeNodes[i+1], {color: 'green'});
    }

    $.each(map.edges, function(i, val) {
        if(($.inArray(val.n1_name, routeNodes) == -1) || ($.inArray(val.n2_name, routeNodes) == -1)) {
            graph.addEdge(val.n1_name, val.n2_name, {color: 'black'});
        }
    });

    pane.slideDown();

    jsnx.draw(graph, {
        element: '#vizPane',
        withLabels: true,
        nodeStyle: {
            fill: function(d) {
                return d.data.color;
            }
        },
        edgeStyle: { 
            stroke: function(d) {
                return d.data.color;
            } 
            //'stroke-width': 2
        }
    });
}

init();

})();
