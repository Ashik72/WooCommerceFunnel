jQuery( document ).ready( function( $ ) {

  jsPlumb.ready(function () {

      var instance = parent.jsp = window.jsp = jsPlumb.getInstance({
          // default drag options
          DragOptions: { cursor: 'pointer', zIndex: 2000 },
          // the overlays to decorate each connection with.  note that the label overlay uses a function to generate the label text; in this
          // case it returns the 'labelText' member that we set on each connection in the 'init' method below.
          ConnectionOverlays: [
              [ "Diamond", {
                  location: 1,
                  visible:true,
                  width:11,
                  length:11,
                  id:"ARROW",
                  events:{
                      //click:function() { alert("you clicked on the arrow overlay")}
                  }
              } ],
              [ "Label", {
                  location: 0.1,
                  id: "label",
                  cssClass: "aLabel",
                  events:{
                      //tap:function() { alert("hey"); }
                  }
              }]
          ],
          Container: "canvas"
      });


      var basicType = {
          connector: "StateMachine",
          paintStyle: { stroke: "red", strokeWidth: 4 },
          hoverPaintStyle: { stroke: "skyblue" },
          overlays: [
              "Arrow"
          ]
      };
      instance.registerConnectionType("basic", basicType);

      // this is the paint style for the connecting lines..
      var connectorPaintStyle = {
              strokeWidth: 5,
              stroke: "red",
              joinstyle: "round",
              outlineStroke: "white",
              outlineWidth: 2,
              connet_type: "downLevel"

          },

        connectorPaintStyle_2 = {
                  strokeWidth: 5,
                  stroke: "green",
                  joinstyle: "round",
                  outlineStroke: "white",
                  outlineWidth: 2,
                  connet_type: "upLevel"
              },

      // .. and this is the hover style.
          connectorHoverStyle = {
              strokeWidth: 3,
              stroke: "#216477",
              outlineWidth: 5,
              outlineStroke: "white"
          },
          endpointHoverStyle = {
              fill: "#216477",
              stroke: "#216477"
          },
      // the definition of source endpoints (the small blue ones)
          sourceEndpoint = {
              endpoint: "Dot",
              paintStyle: {
                  stroke: "#7AB02C",
                  fill: "green",
                  radius: 7,
                  strokeWidth: 1
              },
              isSource: true,
              connector: [ "Flowchart", { stub: [40, 60], gap: 10, cornerRadius: 5, alwaysRespectStubs: true } ],
              connectorStyle: connectorPaintStyle_2,
              hoverPaintStyle: endpointHoverStyle,
              connectorHoverStyle: connectorHoverStyle,
              dragOptions: {},
              overlays: [
                  [ "Label", {
                      location: [0.5, 1.5],
                      label: "Drag",
                      cssClass: "endpointSourceLabel",
                      visible:false
                  } ]
              ]
          },

          sourceEndpoint_2 = {
              endpoint: "Dot",
              paintStyle: {
                  stroke: "#7AB02C",
                  fill: "red",
                  radius: 7,
                  strokeWidth: 1
              },
              isSource: true,
              connector: [ "Flowchart", { stub: [40, 60], gap: 10, cornerRadius: 5, alwaysRespectStubs: true } ],
              connectorStyle: connectorPaintStyle,
              hoverPaintStyle: endpointHoverStyle,
              connectorHoverStyle: connectorHoverStyle,
              dragOptions: {},
              overlays: [
                  [ "Label", {
                      location: [0.5, 1.5],
                      label: "Drag",
                      cssClass: "endpointSourceLabel",
                      visible:false
                  } ]
              ],
              parameters: {

                "type_end" : "downlevel"

              }
          },

      // the definition of target endpoints (will appear when the user drags a connection)
          targetEndpoint = {
              endpoint: "Rectangle",
              paintStyle: { fill: "blue", radius: 7 },
              hoverPaintStyle: endpointHoverStyle,
              maxConnections: -1,
              dropOptions: { hoverClass: "hover", activeClass: "active" },
              isTarget: true,
              overlays: [
                  [ "Label", { location: [0.5, -0.5], label: "Drop", cssClass: "endpointTargetLabel", visible:false } ]
              ]
          },
          init = function (connection) {
              connection.getOverlay("label").setLabel(connection.sourceId.substring(15) + "-" + connection.targetId.substring(15));
          };

      var _addEndpoints = function (toId, sourceAnchors_1, sourceAnchors_2, targetAnchors) {
          for (var i = 0; i < sourceAnchors_1.length; i++) {
              var sourceUUID = toId + sourceAnchors_1[i];
              instance.addEndpoint("flowchart" + toId, sourceEndpoint, {
                  anchor: sourceAnchors_1[i], uuid: sourceUUID
              });
          }

          for (var i = 0; i < sourceAnchors_2.length; i++) {
              var sourceUUID = toId + sourceAnchors_2[i];
              instance.addEndpoint("flowchart" + toId, sourceEndpoint_2, {
                  anchor: sourceAnchors_2[i], uuid: sourceUUID
              });
          }

          if (typeof targetAnchors == 'undefined')
            return;

          for (var j = 0; j < targetAnchors.length; j++) {
              var targetUUID = toId + targetAnchors[j];
              instance.addEndpoint("flowchart" + toId, targetEndpoint, { anchor: targetAnchors[j], uuid: targetUUID });
          }
      };

      // suspend drawing and initialise.
      instance.batch(function () {

        // _addEndpoints("Window5", ["TopCenter", "BottomCenter"], ["LeftMiddle", "RightMiddle"]);
        //
        //   _addEndpoints("Window4", ["TopCenter", "BottomCenter"], ["LeftMiddle", "RightMiddle"]);
        //   _addEndpoints("Window2", ["LeftMiddle", "BottomCenter"], ["TopCenter", "RightMiddle"]);
        //   _addEndpoints("Window3", ["RightMiddle", "BottomCenter"], ["LeftMiddle", "TopCenter"]);
        //   _addEndpoints("Window1", ["LeftMiddle", "RightMiddle"], ["TopCenter", "BottomCenter"]);

          // listen for new connections; initialise them the same way we initialise the connections at startup.
          instance.bind("connection", function (connInfo, originalEvent) {
              init(connInfo.connection);
          });

          // make all the window divs draggable
          instance.draggable(jsPlumb.getSelector(".flowchart-demo .window"), { grid: [20, 20] });
          // THIS DEMO ONLY USES getSelector FOR CONVENIENCE. Use your library's appropriate selector
          // method, or document.querySelectorAll:
          //jsPlumb.draggable(document.querySelectorAll(".window"), { grid: [20, 20] });

          // connect a few up
          // instance.connect({uuids: ["Window2BottomCenter", "Window3TopCenter"], editable: true});
          // instance.connect({uuids: ["Window2LeftMiddle", "Window4LeftMiddle"], editable: true});
          // instance.connect({uuids: ["Window4TopCenter", "Window4RightMiddle"], editable: true});
          // instance.connect({uuids: ["Window3RightMiddle", "Window2RightMiddle"], editable: true});
          // instance.connect({uuids: ["Window4BottomCenter", "Window1TopCenter"], editable: true});
          // instance.connect({uuids: ["Window3BottomCenter", "Window1BottomCenter"], editable: true});
          //
          // instance.connect({uuids: ["Window1TopCenter", "Window5BottomCenter"], editable: true});

          //

          //
          // listen for clicks on connections, and offer to delete connections on click.
          //
          instance.bind("click", function (conn, originalEvent) {
             // if (confirm("Delete connection from " + conn.sourceId + " to " + conn.targetId + "?"))
               //   instance.detach(conn);
              conn.toggleType("basic");
          });

          instance.bind("connectionDrag", function (connection) {
              //console.log("connection " + connection.id + " is being dragged. suspendedElement is ", connection.suspendedElement, " of type ", connection.suspendedElementType);
          });

          instance.bind("connectionDragStop", function (connection) {
              //console.log("connection " + connection.id + " was dragged");
          });

          instance.bind("connectionMoved", function (params) {
              //console.log("connection " + params.connection.id + " was moved");
          });
      });

      jsPlumb.fire("jsPlumbDemoLoaded", instance);


/////Custom


var customMod = {

  changeHeight: function() {

    //console.log($(".jtk-demo-canvas").height(window.innerHeight));
  },

  addProduct: function() {
    //"input[name='addProduct']"
    var el_count = 0;

    window.parent_product_id = 0;

    $(document).on('click', ".selectSingleProduct" ,function() {


      var count = parseInt($("#canvas.flowchart-demo").find("div.window.jtk-node").length)+1;
      var src = $(this).find('img').data('src');
      var title = $(this).find('h2').data('title');
      var id = $(this).data("id");

      var html = "<img src='"+src+"' style='width: 100%' > <div>"+title+" ("+id+")</div>";

      var formatted_html = '<div data-count="'+count+'" data-product_id="'+id+'" style="left: 50px; top: 100px; height: 200px; width: 150px;" class="window jtk-node" id="flowchartWindow'+count+'"><strong>'+html+'</strong><br/><br/></div>';

      el_count = 0;

      //console.log(el_count);

      if ($("#canvas.flowchart-demo div.window").length == 0) {
        window.parent_product_id = id;
        formatted_html = '<div data-count="'+count+'" data-main_product="1" data-product_id="'+id+'" style="left: 50px; top: 100px; height: 200px; width: 150px;" class="window jtk-node" id="flowchartWindow'+count+'"><strong>'+html+'</strong><br/><br/></div>';
        el_count = 1;
      }

      //console.log(el_count);

      $("#canvas.flowchart-demo").append(formatted_html);
      //_addEndpoints("Window"+count, ["TopCenter", "BottomCenter"], ["LeftMiddle", "RightMiddle"]);

      if (el_count == 1)
        _addEndpoints("Window"+count, ["LeftMiddle"], ["RightMiddle"]);
      else
        _addEndpoints("Window"+count, ["LeftMiddle"], ["RightMiddle"],["TopCenter"]);


      instance.draggable('flowchartWindow'+count);

    })

  },

  manual_add: function() {

    var count = 3;
    var html = '<div data-count="3" data-product_id="12" style="left: 205px; top: 410px; height: 200px; width: 150px;" class="window jtk-node jtk-endpoint-anchor jtk-draggable jtk-connected" id="flowchartWindow3"><strong><img src="http://wc-up-down.sites.dev/wp-content/uploads/2016/12/Picture1.png" style="width: 100%"> <div>name 12 (12)</div></strong><br><br></div>';

    $("#canvas.flowchart-demo").append(html);
    _addEndpoints("Window"+count, ["LeftMiddle"], ["RightMiddle"],["TopCenter"]);

    instance.draggable('flowchartWindow'+count);


  },

  requestProducts: function(site_id, user_secret) {

    $.ajaxSetup({
        async: false
    });



    var data = {
      'user_secret' : user_secret,
      'id' : site_id
    };


    $.post("http://restdata.funnely.io/requests/index.php?get_products=1", data, function(response) {

      var data = response;
      var products = $.parseJSON(data);

      window.products = products = products.products;
      $.each(products, function(key, product) {

        var html = '<div class="col-sm-3">';
        html += '<div class="selectSingleProduct" data-id="'+product.id+'" id="'+product.id+'">';
        html += '<img data-src="'+product.featured_src+'" src="'+product.featured_src+'" style="width: 100%" >';
        html += '<h2 data-title="'+product.title+'" >'+product.title+'</h2>'
        html += '</div>';

        $(".container.productList .row:last-child").append(html);

        if ((key+1) % 3 == 0)
          $(".container.productList").append("<div class='row'></div>");



      })

  });

  $(".saveFunnel").css("display", "initial");

  $.ajaxSetup({
    async: true
  });


},

getParentUrlFull : function() {
    var isInIframe = (parent !== window),
        parentUrl = null;

    if (isInIframe) {
        parentUrl = document.referrer;
        var parser = document.createElement('a');
        parser.href = parentUrl;

        parentUrl = parser;
//         parser.protocol; // => "http:"
// parser.host;     // => "example.com:3000"
// parser.hostname; // => "example.com"
// parser.port;     // => "3000"
// parser.pathname; // => "/pathname/"
// parser.hash;     // => "#hash"
// parser.search;   // => "?search=test"
// parser.origin;   // => "http://example.com:3000"

    }

    return parentUrl;
},

saveFunnel : function() {

  $(document).on('click', ".saveFunnel" ,function(evt) {

    evt.preventDefault();

    var funnel_title = "";

    if (window.parent.document.getElementById('funnel_name_input_id').value.length == 0) {

      alert("Enter a title please!");

      return;

    };

    funnel_title = window.parent.document.getElementById('funnel_name_input_id').value;

    if (jsp.getAllConnections().length === 0)
      return;

    //jsp.getAllConnections()[0].source.id
//jsp.getAllConnections()[1].targetId
//jsp.getAllConnections()[1].getPaintStyle().connet_type


    var storeConnections = parent.storeConnections = window.storeConnections = {};
    var storeConnectionsUuids = parent.storeConnectionsUuids = window.storeConnectionsUuids = {};

    var srcIDs = [];
    var targetIDs = [];

    //////

    $.each(jsp.getAllConnections() , function(i, connection) {

      //console.log(connection);
      // console.log(connection);
      // console.log(connection.source.id);
      // console.log(connection.targetId);
      // console.log(connection.getPaintStyle().connet_type);

      var sourceID = connection.source.id;

      var targetID = connection.targetId;

      var sourceEl = connection.source.outerHTML;

      var targetEl = connection.target.outerHTML;

      storeConnectionsUuids[i] = connection.getUuids();

      var connectType = connection.getPaintStyle().connet_type;

      var metaKey = "";

      if (connectType === "upLevel") {
        metaKey = "productUpLevel";
      } else {
        metaKey = "productDownLevel";
      }

      isParent = parseInt($("#"+sourceID).data("main_product"));
      targetID = parseInt($("#"+targetID).data("product_id"));
      sourceID = parseInt($("#"+sourceID).data("product_id"));

      var targetCount = parseInt(connection.target.dataset.count);
      var sourceCount = parseInt(connection.source.dataset.count);

      // console.log(isParent);
      // console.log(targetID);
      // console.log(sourceID);
      // console.log(targetCount);
      // console.log(sourceCount);

      if (typeof storeConnections[targetID] == 'undefined')
        storeConnections[targetID] = {};

      if (typeof storeConnections[sourceID] == 'undefined')
        storeConnections[sourceID] = {};


        if (isParent)
          storeConnections[sourceID].isParent = isParent;

        storeConnections[sourceID].el = sourceEl;
        storeConnections[targetID].el = targetEl;


        if (metaKey == "productUpLevel") {

          // console.log("on up");

          // if (typeof storeConnections[targetID].up == 'undefined')
          //   storeConnections[targetID].up = {};

          if (typeof storeConnections[sourceID].up == 'undefined')
            storeConnections[sourceID].up = {};

          storeConnections[sourceID].up.sourceID = sourceID;
          storeConnections[sourceID].up.targetID = targetID;
          storeConnections[sourceID].up.targetCount = targetCount;
          storeConnections[sourceID].up.sourceCount = sourceCount;


        } else {

          // console.log("on down");

          // if (typeof storeConnections[targetID].down == 'undefined')
          //   storeConnections[targetID].down = {};

          if (typeof storeConnections[sourceID].down == 'undefined')
            storeConnections[sourceID].down = {};

          storeConnections[sourceID].down.sourceID = sourceID;
          storeConnections[sourceID].down.targetID = targetID;
          storeConnections[sourceID].down.targetCount = targetCount;
          storeConnections[sourceID].down.sourceCount = sourceCount;

        }

        // storeConnections[targetID].sourceID = sourceID;
        // storeConnections[sourceID].targetID = targetID;

      // $.ajax({
      //   method: "GET",
      //   url: "/requests/index.php?update_product=1",
      //   async: false,
      //   data: { metaKey : metaKey, sourceID : sourceID, targetID : targetID }
      // }).done(function(response) {
      //
      //     response = jQuery.parseJSON(response);
      //
      //     console.log(response);
      //
      // });



    })




    var funnel_id = ( (typeof customMod.getAllUrlParams(window.top.location.href).funnel_id != 'undefined') ? customMod.getAllUrlParams(window.top.location.href).funnel_id : 0 );

    var data = {
      'action': 'process_funnel_save_data',
      'storeConnections' : storeConnections,
      'storeConnectionsUuids' : storeConnectionsUuids,
      'funnel_title' : funnel_title,
      'funnel_id' : funnel_id
    };

    $.post(customMod.getParentUrlFull().protocol+"//"+customMod.getParentUrlFull().hostname+"/wp-admin/admin-ajax.php", data, function(response) {

      //response = $.parseJSON(response)

      // console.log("found response");
      //
      // console.log(response);

    });


  })

},

getParentUrl : function() {
    var isInIframe = (parent !== window),
        parentUrl = null;

    if (isInIframe) {
        parentUrl = document.referrer;

        var parser = document.createElement('a');
        parser.href = parentUrl;

        parentUrl = parser.hostname;
    }

    return parentUrl;
},



userStat: function() {

  // console.log("start");
  var data = {
    'action' : 'isUserLoggedIn'

  };

  var loggedResponse;

  $.post(this.getParentUrlFull().protocol+"//"+this.getParentUrlFull().hostname+"/wp-admin/admin-ajax.php", data, function(response) {

    response = $.parseJSON(response)
    loggedResponse = response;

  }).complete(function() {

    // console.log(customMod.getParentUrlFull().protocol);


                                // console.log(loggedResponse);

    if (loggedResponse.stat !== 1)
      return;

      var data = {
        'site_url' : customMod.getParentUrlFull().protocol+"//"+customMod.getParentUrlFull().hostname,
        'site_secret' : loggedResponse.secret

      };

      var site_id;

                          // console.log(data);


      $.post("http://restdata.funnely.io/", data, function(response) {

        response = $.parseJSON(response)

        site_id = response;

          // console.log(site_id + " site id");
          // console.log(loggedResponse.secret + " site");

      }).complete(function() {

        if (site_id.length == 0)
          return;

          customMod.requestProducts(site_id, loggedResponse.secret);

      });


  })



},

getAllUrlParams : function (url) {

  // get query string from url (optional) or window
  var queryString = url ? url.split('?')[1] : window.location.search.slice(1);

  // we'll store the parameters here
  var obj = {};

  // if query string exists
  if (queryString) {

    // stuff after # is not part of query string, so get rid of it
    queryString = queryString.split('#')[0];

    // split our query string into its component parts
    var arr = queryString.split('&');

    for (var i=0; i<arr.length; i++) {
      // separate the keys and the values
      var a = arr[i].split('=');

      // in case params look like: list[]=thing1&list[]=thing2
      var paramNum = undefined;
      var paramName = a[0].replace(/\[\d*\]/, function(v) {
        paramNum = v.slice(1,-1);
        return '';
      });

      // set parameter value (use 'true' if empty)
      var paramValue = typeof(a[1])==='undefined' ? true : a[1];

      // (optional) keep case consistent
      paramName = paramName.toLowerCase();
      paramValue = paramValue.toLowerCase();

      // if parameter name already exists
      if (obj[paramName]) {
        // convert value to array (if still string)
        if (typeof obj[paramName] === 'string') {
          obj[paramName] = [obj[paramName]];
        }
        // if no array index number specified...
        if (typeof paramNum === 'undefined') {
          // put the value on the end of the array
          obj[paramName].push(paramValue);
        }
        // if array index number specified...
        else {
          // put the value at that index number
          obj[paramName][paramNum] = paramValue;
        }
      }
      // if param name doesn't exist yet, set it
      else {
        obj[paramName] = paramValue;
      }
    }
  }

  return obj;
}


}

customMod.userStat();

customMod.changeHeight();
customMod.addProduct();
//customMod.requestProducts();
customMod.saveFunnel();

//customMod.manual_add();
//console.log(customMod.getAllUrlParams(window.top.location.href));


var load_data = {

  init: function() {

    this.load();

  },

  load: function() {

      var params = customMod.getAllUrlParams(window.top.location.href);
      if (typeof params.funnel_id == 'undefined')
        return;

      if (typeof params.nonce == 'undefined')
        return;

        var funnel_id = parseInt(params.funnel_id);

        var data = {
          'site_url' : customMod.getParentUrlFull().protocol+"//"+customMod.getParentUrlFull().hostname+"/wp-admin/admin-ajax.php",
          'nonce' : params.nonce,
          'funnel_id' : funnel_id,
          'action' : 'load_funnel_data'

        };

        $.post(data.site_url, data, function(response) {

          if (response.length == 0)
            return;


          response = $.parseJSON(response)

          if (typeof response.funnel_name == 'undefined')
            return;

          window.parent.document.getElementById('funnel_name_input_id').value = response.funnel_name;

            //console.log(response);

            response.funnel_detail =  $.parseJSON(response.funnel_detail);

            $.each(response.funnel_detail, function(i, single_detail) {

              var element = single_detail.el;
              element = element.replace(/\\"/g, '"');
              element_html = $.parseHTML(element);

              //console.log(element_html[0].attr('data-count'));

              var count = element_html[0].attributes['data-count'].value;

              $("#canvas.flowchart-demo").append(element);

              if (typeof element_html[0].attributes['data-main_product'] != 'undefined')
                _addEndpoints("Window"+count, ["LeftMiddle"], ["RightMiddle"]);
              else
                _addEndpoints("Window"+count, ["LeftMiddle"], ["RightMiddle"],["TopCenter"]);

                instance.draggable('flowchartWindow'+count);


            })

          response.uuids =  $.parseJSON(response.uuids);

          $.each(response.uuids, function(i, single_uuid) {

            window.jsp.connect({ uuids: single_uuid })


          })

        });


      //console.log(data);

  }

}

load_data.init();

  });



} );
