(function(){

var _self = this;
var url = "http://cerlab29.andrew.cmu.edu/",
    cars = [],
    routes = [],
    username = null,
    currCar = null,
    currRoute = {nodes: [], edges: []},
    map = {nodes: [], edges: []},
    graph = null;

var init = function() {
    $('#usernameform').submit(function(e) {
        username = $('#usernameform input').text();
        getCars(username);
        e.preventDefault();
    });

    $('#carsTable').on('click', '.clickable-row', function(e) {
        $(this).addClass('active').siblings().removeClass('active');
        currCar = $(this).children('th').text(); // .attr('id')
        getRoutes( username, currCar );
    });

    $('#routesTable').on('click', '.clickable-row', function(e) {
        $(this).addClass('active').siblings().removeClass('active');
        currRoute = $(this).children('th').text(); // .attr('id')
        updateRouteViz();
    });

    // $('#clearbtn').click(function() {
    //     // do something
    // });

    getMap();

    graph = new jsnx.Graph();

    console.log('initialized');
}

var getMap = function() {
    $.post(url + "IoRT/php/car_map_r.php",
        {},
        function(data) {
            map.nodes = data.node;
            map.edges = data.edge;
        }
    );
}

var getCars = function(_user) {
    $.post(url + "IoRT/php/car_r.php",
        {"u_name":_user},
        function(data) {
            $.each(data, function(i, val) {
                cars.append({"p_id":val.p_id, "p_name":val.p_name});
            });

            updateCarTable(cars);
        }
    );
}

var getRoutes = function(_user, _car) {
    $.post(url + "IoRT/php/car_r.php",
        {"u_name":_user},
        function(data) {
            $.each(data, function(i, val) {
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
        row.children('th').value(val.p_name);
        row.attr('id', String(val.p_id));
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

var updateRouteViz = function(path) {
    var pane = $('#vizPane');

    if(pane.is(":visible")) {
        pane.slideUp();
    }

    // clear graph nodes

    $.each(path.nodes, function(i, val) {
        graph.addNode(val.n_name);
    });
    $.each(path.edges function(i, val) {
        graph.addEdge(val.n1_name, val.n2_name);
    });

    pane.show();

    jsnx.draw(graph, {
        element: '#vizPane',
        withLabels: true
    });
}

// merge the map and the current route using color attributes
var updateMapViz = function() {
    // add path nodes
    $.each(path.nodes, function(i, val) {
        graph.addNode(val.n_name, {color: 'red'});
    });
    $.each(path.edges function(i, val) {
        graph.addEdge(val.n1_name, val.n2_name);
    });

    // map - path
    // add map nodes

    jsnx.draw(graph, {
        element: '#mapPane',
        withLabels: true,
        nodeStyle: {
            fill: function(d) {
                return d.data.color;
            }
        }
    });
}

init();

})();
