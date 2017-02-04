(function(){

var _self = this;
var url = "http://cerlab29.andrew.cmu.edu/",
    map = {nodes: [], edges: []},
    username = null,
    currCarId = null;

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

        console.log($(this).children('th').first().text());
        var currCarId = $(this).attr('id');
    });

    $('#routesTable').on('click', '.clickable-row', function(e) {
        $(this).addClass('active').siblings().removeClass('active');
        var currRoute = $(this).children('th').first().text(); // .attr('id');

        console.log(currRoute);
        getRouteNodes( currRoute );
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
            map.nodes = data.node;
            map.edges = data.edge;

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
// node = {"seq":"1","pos_x":"1910","pos_y":"40","name":"n020"}
var getRouteNodes = function(_route) {

    $.post(url + "IoRT/php/car_path_r.php",
        JSON.stringify({u_name: username, p_name: _route}),
        function(data) {
            console.log(JSON.stringify(data));

            var path = data.path;
            path.sort(function(n1, n2) {
                return (n1.seq - n2.seq);
            });

            var routeNodes = [],
                x = [];
                y = [];

            for (var i = 0; i < path.length; i++) {
                routeNodes.push(path[i].name);
                x.push(path[i].pos_x);
                y.push(path[i].pos_y);
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

    for (var i = 0; i < (routeNodes.length - 1); i++) {
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
            fill: function(d) {
                return d.data.color;
            },
            'stroke-width': 10
        },
        labelStyle: {fill: 'white'},
        stickyDrag: true
    });
}

init();

})();
