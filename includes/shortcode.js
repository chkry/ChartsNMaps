//object to store map data
//can be used in the future to store more map specific data
var iwmMapObj = [];
var iwmgeocharts = [];
var apiversion = iwmparam[0]['apiversion'];
var iwmdata = [];
var iwmoptions = [];
var iwmApiKey = iwmparam[0]['apikey'];

//We're loading version 42 of the API, latest version had bugs for text labels 
google.charts.load('42', {
    packages: ['geochart'],
    mapsApiKey: iwmApiKey
});
google.charts.setOnLoadCallback(iwm_init);

//var options = {packages: ['geochart'], callback : iwmDrawVisualization};
//google.load('visualization', '1', options);


function iwm_init() {

    for (var key in iwmparam) {
        //zoom controls init
        var controls = iwmparam[key]['controls'];
        if(controls) {
            iwm_zoom(iwmparam[key]['id'],iwmparam[key]['controls_position'],iwmparam[key]['overlay']);
        }
    }

    iwmDrawVisualization();
}


function iwmDrawVisualization(skipNotVisible) {

    if (typeof google.visualization != "undefined") {

        var data = {};
        var values = {};
        var listener_actions = {};
        var listener_custom = {};
        var identifier = {};

        for (var key in iwmparam) {

            var mapid = iwmparam[key]['id'];

            if (skipNotVisible && iwmMapObj[mapid] && !iwmMapObj[mapid].div.is(':visible')) {
                continue;
            }

            var keydiv = document.getElementById("map_canvas_" + mapid);
            if (iwmparam[key]['region'] && keydiv) {

                var usehtml = parseInt(iwmparam[key]['usehtml']);

                /* Disable HTML tooltips on iOS */
                /*if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
                    usehtml = 0;
                }*/

                var iwmid = parseInt(iwmparam[key]['id']);
                var bgcolor = iwmparam[key]['bgcolor'];
                var stroke = parseInt(iwmparam[key]['stroke']);
                var bordercolor = iwmparam[key]['bordercolor'];
                var incolor = iwmparam[key]['incolor'];
                var actcolor = iwmparam[key]['actcolor'];
                var width = parseInt(iwmparam[key]['width']);
                var height = parseInt(iwmparam[key]['height']);
                var ratio = (iwmparam[key]['aspratio'] === '1');
                var interactive = (iwmparam[key]['interactive'] === 'true');
                var toolt = iwmparam[key]['tooltip'];
                var region = iwmparam[key]['region'];
                var resolution = iwmparam[key]['resolution'];
                var markersize = parseInt(iwmparam[key]['markersize']);
                var displaymode = iwmparam[key]['displaymode'];
                var placestxt = iwmparam[key]['placestxt'];
                var projection = iwmparam[key]['projection'];

                var magglass = iwmparam[key]['magglass'];
                var magglasszfactor = parseInt(iwmparam[key]['magglasszfactor']);

                var widthselector = iwmparam[key]['widthselector'];

                placestxt = placestxt.replace(/^\s+|\s+$/g, '');

                var action = iwmparam[key]['action'];
                var customaction = iwmparam[key]['custom_action'];

                identifier[mapid] = iwmid;
                listener_actions[mapid] = action;
                listener_custom[mapid] = customaction;

                var places = placestxt.split(";");

                data[mapid] = new google.visualization.DataTable();

                if (displaymode == "markers02" || displaymode == "text02") {



                    data[mapid].addColumn('number', 'Lat');
                    data[mapid].addColumn('number', 'Long');
                }


                data[mapid].addColumn('string', 'Country'); // Implicit domain label col.
                data[mapid].addColumn('number', 'Value'); // Implicit series 1 data col.
                data[mapid].addColumn({
                    type: 'string',
                    role: 'tooltip',
                    p: {
                        html: true
                    }
                }); // 

                var colorsmap = [];
                var colorsmapecho = "";

                values[mapid] = {};
                dataindex = {};

                //places.length-1 to eliminate empty value at the end
                for (var i = 0; i < places.length - 1; i++) {
                    var entry = places[i].split(",");

                    var ttitle = entry[1].replace(/&#59/g, ";");
                    ttitle = ttitle.replace(/&#44/g, ",");
                    var ttooltip = entry[2].replace(/&#59/g, ";");
                    ttooltip = ttooltip.replace(/&#44/g, ",");

                    /* Disable HTML content in tooltips on iOS */
                    /*if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
                        
                        ttooltip = ittooltip.replace(/(<([^>]+)>)/ig,"");

                    }*/

                    var iwmcode = entry[0];
                    iwmcode = iwmcode.replace(/^\s+|\s+$/g, '');

                    //we create an index, to use after with the setSelection functions
                    dataindex[iwmcode] = i;


                    //If data != markers02
                    if (displaymode != "markers02" && displaymode != "text02") {


                        data[mapid].addRows([
                            [{
                                v: iwmcode,
                                f: ttitle
                            }, i, ttooltip]
                        ]);
                        var index = iwmcode;

                    } else {

                        var trim = entry[0].replace(/^\s+|\s+$/g, "");
                        var latlon = trim.split(" ");
                        var lat = parseFloat(latlon[0]);
                        var lon = parseFloat(latlon[1]);


                        //data[mapid].addRows([[lat,lon,ttitle,i,ttooltip]]);
                        data[mapid].addRows([
                            [lat, lon, ttitle, i, ttooltip]
                        ]);

                        var index = lat;


                        //finally set dislay mode of markers02 to proper value
                        //displaymode = "markers";

                    }


                    var colori = entry[4];

                    values[mapid][index] = entry[3].replace(/&#59/g, ";");
                    values[mapid][index] = values[mapid][index].replace(/&#44/g, ",");



                    colorsmapecho = colorsmapecho + "'" + colori + "',";
                    colorsmap.push(colori);



                }


                defmaxvalue = 0;
                if ((places.length - 2) > 0) {
                    defmaxvalue = places.length - 2;
                }

                if (displaymode == "markers02") {
                    displaymode = "markers";
                }
                if (displaymode == "text02") {
                    displaymode = "text";
                }

                var htmltooltip = false;
                if (usehtml == 1) {
                    htmltooltip = true;
                }

                
                if(widthselector){

                    var wselector = jQuery(widthselector);
                    if(wselector) {
                        if(wselector.width()>0) {

                            width = wselector.width();
                            height = '';

                        }
                        
                    }

                }

                //in case there was a zoom event, we check the new sizes
                if(jQuery("#map_canvas_" + iwmid).attr('data-iwm-zwidth')) {

                    width = jQuery("#map_canvas_" + iwmid).attr('data-iwm-zwidth');
                    height = jQuery("#map_canvas_" + iwmid).attr('data-iwm-zheight');

                }


                var options = {
                    projection: projection,
                    backgroundColor: {
                        fill: bgcolor,
                        stroke: bordercolor,
                        strokeWidth: stroke
                    },
                    colorAxis: {
                        minValue: 0,
                        maxValue: defmaxvalue,
                        colors: colorsmap
                    },
                    legend: 'none',
                    backgroundColor: {
                        fill: bgcolor,
                        stroke: bordercolor,
                        strokeWidth: stroke
                    },
                    datalessRegionColor: incolor,
                    displayMode: displaymode,
                    enableRegionInteractivity: interactive,
                    resolution: resolution,
                    sizeAxis: {
                        minValue: 1,
                        maxValue: 1,
                        minSize: markersize,
                        maxSize: markersize
                    },
                    region: region,
                    keepAspectRatio: ratio,
                    width: width,
                    height: height,
                    magnifyingGlass: {
                        enable: magglass,
                        zoomFactor: magglasszfactor
                    },
                    tooltip: {
                        trigger: toolt,
                        isHtml: htmltooltip
                    },
                    //domain: 'IN'
                };


                var divid = "map_canvas_" + iwmid;

                iwmgeocharts[mapid] = new google.visualization.GeoChart(document.getElementById(divid));


                if (action != "none") {

                    google.visualization.events.addListener(iwmgeocharts[mapid], 'select', (function(x) {

                        return function() {

                            var selection = iwmgeocharts[x].getSelection();


                            if (selection.length == 1) {
                                var selectedRow = selection[0].row;
                                var selectedRegion = data[x].getValue(selectedRow, 0);


                                if (values[x][selectedRegion] != "") {

                                    //console.log(values[x][selectedRegion]);

                                    iwm_run_action(selectedRegion, values[x][selectedRegion], identifier[x], listener_actions[x], listener_custom[x]);
                                   // iwm_clearSelection(x);
                                }
                            }
                        }
                    })(mapid));

                }

                //set global variables
                iwmdata[mapid] = data[mapid];
                iwmoptions[mapid] = options;

                //test to order the entries
                //iwmdata[mapid].sort([{column: 3}]);

                iwmgeocharts[mapid].draw(iwmdata[mapid], iwmoptions[mapid]);

                //Create a new object for this map

                if (!iwmMapObj[mapid]) {

                    iwmMapObj[mapid] = {
                        div: jQuery('#' + divid),
                        data: dataindex
                    };

                }



                iwmMapObj[mapid].lastWidth = iwmMapObj[mapid].div.parent().width();

                google.visualization.events.addListener(iwmgeocharts[mapid], 'ready', function() {


                     jQuery('.iwm_map_canvas svg').fadeIn(300);

                    if (typeof iwm_callback == 'function') {
                        iwm_callback();
                    }

                   

                });


                //code to console log the image url data
                /* 
                google.visualization.events.addListener(geocharts[key], 'ready', function () {
                     var imgurl = geocharts[key].getImageURI();
                     console.log(imgurl);
                });
                */

                /* Code to create animation */
                /*
                google.visualization.events.addListener(iwmgeocharts[key], 'ready', function () {
                    var time = 50;
                    jQuery('#map_canvas_8 circle').each( function(){
                    var circle = jQuery(this);
                    setTimeout( function(){ circle.fadeTo( "slow", 1 ); }, time);
                    time +=50;
                    });
                 });
                */




            }
        }

    } else {
        console.log('API file not loaded yet');
    }

    //console.log(iwmgeocharts);

}


function iwm_run_action(selected, value, id, action, customaction) {

    //console.log('values for action:'+selected+';'+value+';'+id+';'+action+';'+customaction);

    if (action == 'i_map_action_open_url') {
        document.location = value;
    }

    if (action == 'i_map_action_alert') {

        alert(value);
    }

    if (action == 'i_map_action_open_url_new') {

        window.open(value);
    }

    if (action == 'i_map_action_content_below' || action == 'i_map_action_content_above') {
        document.getElementById('imap' + id + 'message').innerHTML = value;

        //we check if there's a dropdown so we set selection to be the same as region clicked
        var dropdown = document.getElementById('imap-dropdown-' + id);
        if (dropdown) {
            document.getElementById('imap' + id + '-' + selected).selected = true;
        }

    }

    if (action == 'i_map_action_content_below_scroll' || action == 'i_map_action_content_above_scroll') {
        document.getElementById('imap' + id + 'message').innerHTML = value;

        jQuery("html, body").animate({
            scrollTop: jQuery("#imap" + id + "message").position().top
        }, "slow");

        //we check if there's a dropdown so we set selection to be the same as region clicked
        var dropdown = document.getElementById('imap-dropdown-' + id);
        if (dropdown) {
            document.getElementById('imap' + id + '-' + selected).selected = true;
        }

    }

    if (action == 'i_map_action_colorbox_content') {

        jQuery.colorbox({
            html: value,
            maxWidth: '90%',
            onComplete: function() {
                jQuery.colorbox.resize();
            },
        });

    }


    if (action == 'i_map_action_colorbox_iframe') {

        jQuery.colorbox({
            open: true,
            href: value,
            iframe: true,
            width: "80%",
            height: "80%"
        });

    }

    if (action == 'i_map_action_colorbox_image') {

        jQuery.colorbox({
            open: true,
            href: value,
            photo: true
        });

    }

    if (action == 'i_map_action_colorbox_inline') {

        var inline = jQuery(value);
        jQuery.colorbox({
            inline: true,
            href: inline
        });

    }

    if (action == 'i_map_action_custom') {

        var name = "iwm_custom_action_" + id;
        window[name](value);
    }
}


// Functions to set selection and remove selection. 
// Can be used by externel elements to trigger the selection


function iwm_setSelection(code, map) {

    map = map || false;

    if (map) {

        //console.log(iwmMapObj[map]);

        var index = iwmMapObj[map].data[code];
        iwmgeocharts[map].setSelection([{
            row: index,
            column: null
        }]);

    }



}

function iwm_clearSelection(map) {
    map = map || false;
    if (map) {

        iwmgeocharts[map].setSelection(null);

    }
}

function iwm_select(code, map) {

    map = map || '0';
    var index = iwmMapObj[map].data[code];
    iwmgeocharts[map].setSelection([{
        row: index,
        column: null
    }]);
    google.visualization.events.trigger(iwmgeocharts[map], 'select', {});

}


/*
mapid - id of the map to apply the connections
marker01 - region code for marker to serve as base
marker02 - region code for destination marker
color - color of line
strokew - stroke/width of line
type - type of connection. Currently valid: 'normal', 'arrow', 'dashed', 'dashed-arrow', 'curved', 'dashed-curved', 'dashed-curved-arrow'
*/

//Will not work on Chrome
function iwm_connect_marker(mapid, marker01, marker02, color, strokew, type) {

    mapid = mapid || false;
    marker01 = marker01 || false;
    marker02 = marker02 || false;
    color = color || '#FFFFFF';
    strokew = strokew || '1';
    type = type || 'normal';

    if (mapid && marker01 && marker02 && iwmMapObj[mapid]) {

        var mi01 = iwmMapObj[mapid].data[marker01];
        var mi02 = iwmMapObj[mapid].data[marker02];

        var map = document.getElementById('map_canvas_' + mapid);

        if (map) {

            var mode = null;
            var markers = jQuery('#map_canvas_' + mapid + ' circle').length;
            var textlabels = jQuery('#map_canvas_' + mapid + ' text').length;

            if (textlabels > 0) {
                mode = 'text'
            }
            if (markers > 0) {
                mode = 'circle';
            }


            //check browser
            var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
                // Firefox 1.0+
            var isFirefox = typeof InstallTrigger !== 'undefined';
                // At least Safari 3+: "[object HTMLElementConstructor]"
            var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
                // Internet Explorer 6-11
            var isIE = /*@cc_on!@*/false || !!document.documentMode;
                // Edge 20+
            var isEdge = !isIE && !!window.StyleMedia;
                // Chrome 1+
            var isChrome = !!window.chrome && !!window.chrome.webstore;
                // Blink engine detection
            var isBlink = (isChrome || isOpera) && !!window.CSS;


            //if there are more than 10 markers, it won't work on chrome or opera
            if( (textlabels > 10 || markers > 10) && (isOpera || isChrome ) ) {
                return;
            }



            var marker01ck = map.getElementsByTagName(mode)[mi01];
            var marker02ck = map.getElementsByTagName(mode)[mi02];

            if (marker01ck && marker02ck) {

                if (mode == 'circle') {

                    var m01x = parseInt(marker01ck.getAttribute("cx"), 10);
                    var m01y = parseInt(marker01ck.getAttribute("cy"), 10);
                    var m02x = parseInt(marker02ck.getAttribute("cx"), 10);
                    var m02y = parseInt(marker02ck.getAttribute("cy"), 10);
                    var mr = parseInt(marker02ck.getAttribute("r"), 10);

                }

                if (mode == 'text') {

                    var m01x = parseInt(marker01ck.getAttribute("x"), 10);
                    var m01y = parseInt(marker01ck.getAttribute("y"), 10);
                    var m02x = parseInt(marker02ck.getAttribute("x"), 10);
                    var m02y = parseInt(marker02ck.getAttribute("y"), 10);
                    var mr2 = parseInt(marker02ck.getAttribute("font-size"), 10);

                    var bbox1 = marker01ck.getBBox();
                    var width1 = bbox1.width;
                    var height1 = bbox1.height;


                    var bbox2 = marker02ck.getBBox();
                    var width2 = bbox2.width;
                    var height2 = bbox2.height;

                    mr1 = parseInt(height1) / 2;
                    mr2 = parseInt(height2) / 2;


                    var m02y = parseInt(m02y, 10) - mr2;
                    var m01y = parseInt(m01y, 10) - mr1;




                }

                var lineid = new String(marker01 + marker02);
                lineid = lineid.replace(/[^a-zA-Z0-9|-]/g, "");




                //we add arrow head 
                if (type == 'arrow' || type == 'dashed-arrow' || type == 'curved-arrow' || type == 'dashed-curved-arrow') {

                    //we need to calculate new end point for arrow
                    if (mode == 'circle') {
                        mr = parseInt(mr, 10) + parseInt(strokew, 10) * 4; //to add the arrow size to the radius
                        var dx = parseInt(m01x, 10) - parseInt(m02x, 10),
                            dy = parseInt(m01y, 10) - parseInt(m02y, 10),
                            dist = Math.sqrt(dx * dx + dy * dy),
                            m02x = parseInt(m02x, 10) + parseInt(dx, 10) * parseInt(mr, 10) / dist,
                            m02y = parseInt(m02y, 10) + dy * parseInt(mr, 10) / dist;
                    }

                    if (mode == 'text') {
                        mr2 = parseInt(mr2, 10) + parseInt(strokew, 10) * 4; //to add the arrow size to the radius
                        var dx2 = parseInt(m01x, 10) - parseInt(m02x, 10),
                            dy2 = parseInt(m01y, 10) - parseInt(m02y, 10),
                            dist2 = Math.sqrt(dx2 * dx2 + dy2 * dy2),
                            m02x = parseInt(m02x, 10) + parseInt(dx2, 10) * parseInt(mr2, 10) / dist2,
                            m02y = parseInt(m02y, 10) + dy2 * parseInt(mr2, 10) / dist2;

                        mr1 = parseInt(mr1, 10) - 5; //to add the arrow size to the radius
                        var dx1 = parseInt(m02x, 10) - parseInt(m01x, 10),
                            dy1 = parseInt(m02y, 10) - parseInt(m01y, 10),
                            dist1 = Math.sqrt(dx1 * dx1 + dy1 * dy1),
                            m01x = parseInt(m01x, 10) + parseInt(dx1, 10) * parseInt(mr1, 10) / dist1,
                            m01y = parseInt(m01y, 10) + dy1 * parseInt(mr1, 10) / dist1;
                    }


                    //console.log(newX+' '+newY);
                    //console.log(m02x+'|'+m02y);


                    var larrow = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
                    larrow.setAttribute('id', 'arrow' + lineid);
                    larrow.setAttribute('markerWidth', '5');
                    larrow.setAttribute('markerHeight', '5');
                    larrow.setAttribute('refX', '-2');
                    larrow.setAttribute('refY', '0');
                    larrow.setAttribute('orient', 'auto');
                    larrow.setAttribute('markerUnits', 'strokeWidth');
                    larrow.setAttribute('Box', '0 0 10 10');
                    larrow.setAttribute('viewBox', '-5 -5 10 10');

                    var currarrow = jQuery('#map_canvas_' + mapid + ' svg #defs #arrow' + lineid);
                    if (currarrow.length == 0) {
                        jQuery('#map_canvas_' + mapid + ' svg #defs').append(larrow);
                    }

                    //add arrow head to marker
                    var arrowpath = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                    arrowpath.setAttribute('points', '-2,0 -5,5 5,0 -5,-5');
                    arrowpath.setAttribute('fill', color);
                    arrowpath.setAttribute('stroke', color);
                    arrowpath.setAttribute('stroke-width', '0px');
                    arrowpath.setAttribute('id', 'poly' + lineid);

                    var currarrowpath = jQuery('#map_canvas_' + mapid + ' svg #poly' + lineid);
                    if (currarrowpath.length == 0) {
                        jQuery('#map_canvas_' + mapid + ' svg #arrow' + lineid).append(arrowpath);
                    }

                }

                if (type == 'curved' || type == 'curved-arrow' || type == 'dashed-curved' || type == 'dashed-curved-arrow') {

                    var cx = (m01x + m02x) / 2;
                    var cy = (m01y + m02y) / 2;
                    var dx = (m02x - m01x) / 2;
                    var dy = (m02y - m01y) / 2;
                    var dd = Math.sqrt(dx * dx + dy * dy);

                    var ex = cx + dy / dd * 20;
                    var ey = cy - dx / dd * 20;

                    if(!ex) { ex = 0; }
                    if(!ey) { ey = 0; }

                    var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.setAttribute('id', lineid);
                    path.setAttribute('d', 'M' + m01x + ',' + m01y + ' Q' + ex + ',' + ey + ' ' + m02x + ',' + m02y);
                    path.setAttribute('stroke', color);
                    path.setAttribute('stroke-width', strokew);
                    path.setAttribute('fill', 'transparent');

                    if (type == 'dashed-curved-arrow' || type == 'dashed-curved') {

                        path.setAttribute('stroke-dasharray', '5,5');

                    }


                    if (type == 'curved-arrow' || 'dashed-curved-arrow') {

                        path.setAttribute('marker-end', 'url(#arrow' + lineid + ')');

                    }



                }

                if (type == 'dashed' || type == 'dashed-arrow' || type == 'arrow' || type == 'normal') {

                    var path = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    path.setAttribute('id', lineid);
                    path.setAttribute('x1', m01x);
                    path.setAttribute('y1', m01y);
                    path.setAttribute('x2', m02x);
                    path.setAttribute('y2', m02y);
                    path.setAttribute('stroke', color);
                    path.setAttribute('stroke-width', strokew);

                    if (type == 'dashed' || type == 'dashed-arrow') {

                        path.setAttribute('stroke-dasharray', '5,5');

                    }


                    if (type == 'arrow' || type == 'dashed-arrow') {

                        path.setAttribute('marker-end', 'url(#arrow' + lineid + ')');

                    }

                }

                var currpath = jQuery('#map_canvas_' + mapid + ' line#' + lineid);
                if (currpath.length == 0) {
                    if (mode == 'circle') {
                        jQuery('#map_canvas_' + mapid + ' svg circle').first().parent().before(path);

                    }
                    if (mode = 'text') {
                        jQuery('#map_canvas_' + mapid + ' svg text').first().parent().before(path);

                    }
                }

            }
        }

    }

}


//More advanced function to connect markers. 
//Not being used because it doesn't work well particularly on US map.


var iwm_processed_connections = [];

function iwm_connect_marker_adv(mapid, marker01, marker02, color, strokew, type) {

    mapid = mapid || false;
    marker01 = marker01 || false;
    marker02 = marker02 || false;
    color = color || '#FFFFFF';
    strokew = strokew || '1';
    type = type || 'normal';


    if (mapid && marker01 && marker02 && iwmMapObj[mapid]) {


        var map = document.getElementById('map_canvas_' + mapid);

        if (map) {


            var mode = null;
            var markers = jQuery('#map_canvas_' + mapid + ' circle');
            var textlabels = jQuery('#map_canvas_' + mapid + ' text');



            var sortedmarkers;
            var markersindex = [];

            if (textlabels.length > 0) {



                //first we sort the markers and the data array to match them
                sortedmarkers = jQuery('#map_canvas_' + mapid + ' g text').sort(function(a, b) {
                //return parseInt(jQuery(a).attr('y')) - parseInt(jQuery(b).attr('y'))
                return Number(jQuery(a).attr('y')) - Number(jQuery(b).attr('y'))
                });

                //we use the cy attr to order the markers
                sortedmarkers.each(function(index){
                    markersindex[index] = jQuery(this).attr('y');
                });

            }

            if (markers.length > 0) {

                //first we sort the markers and the data array to match them
                sortedmarkers = jQuery('#map_canvas_' + mapid + ' g circle').sort(function(a, b) {
                return parseInt(jQuery(a).attr('cy')) - parseInt(jQuery(b).attr('cy'))
                });

                //we use the cy attr to order the markers
                sortedmarkers.each(function(index){
                    markersindex[index] = jQuery(this).attr('cy');
                });

            }
            


            //now we order the data array with the 
            var datacyindex = [];
            var tempcounter = 0;
            jQuery.each(iwmMapObj[mapid].data, function(key, value) {
                cyindex = key.split(' ');
                datacyindex[tempcounter] = cyindex[0];
                tempcounter++;
            });

            datacyindex.sort(iwm_sortNumber).reverse();

              
            relationobj = {}; 

            for (var i = 0; i < datacyindex.length; i++) {
                    relationobj[datacyindex[i]] = markersindex[i];
                };    

            //marker 01 cy value
            mi01i = marker01.split(' ')[0];
            mi01cy = relationobj[mi01i];

            //marker 02 cy value
            mi02i = marker02.split(' ')[0];
            mi02cy = relationobj[mi02i];

            //console.log(markers);
            //console.log(iwmMapObj[mapid]);
            var marker01ck = false;
            var marker02ck = false;

            if (textlabels.length > 0) {
                mode = 'text';
                textlabels.each(function(index){
                    if(jQuery(this).attr('y') == mi01cy) {
                        marker01ck = map.getElementsByTagName(mode)[index];
                    }
                    if(jQuery(this).attr('y') == mi02cy) {
                        marker02ck = map.getElementsByTagName(mode)[index];
                    }
                }); 
            }

            if (markers.length > 0) {
                mode = 'circle';
                markers.each(function(index){
                    if(jQuery(this).attr('cy') == mi01cy) {
                        marker01ck = map.getElementsByTagName(mode)[index];
                    }
                    if(jQuery(this).attr('cy') == mi02cy) {
                        marker02ck = map.getElementsByTagName(mode)[index];
                    }
                }); 
            }


            

            if (marker01ck && marker02ck) {

                if (mode == 'circle') {
                   
                    var m01x = parseInt(marker01ck.getAttribute("cx"), 10);
                    var m01y = parseInt(marker01ck.getAttribute("cy"), 10);
                    var m02x = parseInt(marker02ck.getAttribute("cx"), 10);
                    var m02y = parseInt(marker02ck.getAttribute("cy"), 10);
                    var mr = parseInt(marker02ck.getAttribute("r"), 10);

                }

                if (mode == 'text') {

                    var m01x = parseInt(marker01ck.getAttribute("x"), 10);
                    var m01y = parseInt(marker01ck.getAttribute("y"), 10);
                    var m02x = parseInt(marker02ck.getAttribute("x"), 10);
                    var m02y = parseInt(marker02ck.getAttribute("y"), 10);
                    var mr2 = parseInt(marker02ck.getAttribute("font-size"), 10);


                    var bbox1 = marker01ck.getBBox();
                    var width1 = bbox1.width;
                    var height1 = bbox1.height;




                    var bbox2 = marker02ck.getBBox();
                    var width2 = bbox2.width;
                    var height2 = bbox2.height;

                    mr1 = parseInt(height1) / 2;
                    mr2 = parseInt(height2) / 2;


                    var m02y = parseInt(m02y, 10) - mr2;
                    var m01y = parseInt(m01y, 10) - mr1;

  

                }

                var lineid = new String(marker01 + marker02);
                lineid = lineid.replace(/[^a-zA-Z0-9|-]/g, "");

                //we add arrow head 
                if (type == 'arrow' || type == 'dashed-arrow' || type == 'curved-arrow' || type == 'dashed-curved-arrow') {

                    //we need to calculate new end point for arrow
                    if (mode == 'circle') {
                        mr = parseInt(mr, 10) + parseInt(strokew, 10) * 4; //to add the arrow size to the radius
                        var dx = parseInt(m01x, 10) - parseInt(m02x, 10),
                            dy = parseInt(m01y, 10) - parseInt(m02y, 10),
                            dist = Math.sqrt(dx * dx + dy * dy),
                            m02x = parseInt(m02x, 10) + parseInt(dx, 10) * parseInt(mr, 10) / dist,
                            m02y = parseInt(m02y, 10) + dy * parseInt(mr, 10) / dist;
                    }

                    if (mode == 'text') {
                        mr2 = parseInt(mr2, 10) + parseInt(strokew, 10) * 4; //to add the arrow size to the radius
                        var dx2 = parseInt(m01x, 10) - parseInt(m02x, 10),
                            dy2 = parseInt(m01y, 10) - parseInt(m02y, 10),
                            dist2 = Math.sqrt(dx2 * dx2 + dy2 * dy2),
                            m02x = parseInt(m02x, 10) + parseInt(dx2, 10) * parseInt(mr2, 10) / dist2,
                            m02y = parseInt(m02y, 10) + dy2 * parseInt(mr2, 10) / dist2;

                        mr1 = parseInt(mr1, 10) - 5; //to add the arrow size to the radius
                        var dx1 = parseInt(m02x, 10) - parseInt(m01x, 10),
                            dy1 = parseInt(m02y, 10) - parseInt(m01y, 10),
                            dist1 = Math.sqrt(dx1 * dx1 + dy1 * dy1),
                            m01x = parseInt(m01x, 10) + parseInt(dx1, 10) * parseInt(mr1, 10) / dist1,
                            m01y = parseInt(m01y, 10) + dy1 * parseInt(mr1, 10) / dist1;
                    }


                    //console.log(newX+' '+newY);
                    //console.log(m02x+'|'+m02y);


                    var larrow = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
                    larrow.setAttribute('id', 'arrow' + lineid);
                    larrow.setAttribute('markerWidth', '5');
                    larrow.setAttribute('markerHeight', '5');
                    larrow.setAttribute('refX', '-2');
                    larrow.setAttribute('refY', '0');
                    larrow.setAttribute('orient', 'auto');
                    larrow.setAttribute('markerUnits', 'strokeWidth');
                    larrow.setAttribute('Box', '0 0 10 10');
                    larrow.setAttribute('viewBox', '-5 -5 10 10');

                    var currarrow = jQuery('#map_canvas_' + mapid + ' svg #defs #arrow' + lineid);
                    if (currarrow.length == 0) {
                        jQuery('#map_canvas_' + mapid + ' svg #defs').append(larrow);
                    }

                    //add arrow head to marker
                    var arrowpath = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                    arrowpath.setAttribute('points', '-2,0 -5,5 5,0 -5,-5');
                    arrowpath.setAttribute('fill', color);
                    arrowpath.setAttribute('stroke', color);
                    arrowpath.setAttribute('stroke-width', '0px');
                    arrowpath.setAttribute('id', 'poly' + lineid);

                    var currarrowpath = jQuery('#map_canvas_' + mapid + ' svg #poly' + lineid);
                    if (currarrowpath.length == 0) {
                        jQuery('#map_canvas_' + mapid + ' svg #arrow' + lineid).append(arrowpath);
                    }

                }

                if (type == 'curved' || type == 'curved-arrow' || type == 'dashed-curved' || type == 'dashed-curved-arrow') {

                    var cx = (m01x + m02x) / 2;
                    var cy = (m01y + m02y) / 2;
                    var dx = (m02x - m01x) / 2;
                    var dy = (m02y - m01y) / 2;
                    var dd = Math.sqrt(dx * dx + dy * dy);

                    var ex = cx + dy / dd * 20;
                    var ey = cy - dx / dd * 20;

                    if(!ex) { ex = 0; }
                    if(!ey) { ey = 0; }

                    var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.setAttribute('id', lineid);
                    path.setAttribute('d', 'M' + m01x + ',' + m01y + ' Q' + ex + ',' + ey + ' ' + m02x + ',' + m02y);
                    path.setAttribute('stroke', color);
                    path.setAttribute('stroke-width', strokew);
                    path.setAttribute('fill', 'transparent');

                    if (type == 'dashed-curved-arrow' || type == 'dashed-curved') {

                        path.setAttribute('stroke-dasharray', '5,5');

                    }


                    if (type == 'curved-arrow' || 'dashed-curved-arrow') {

                        path.setAttribute('marker-end', 'url(#arrow' + lineid + ')');

                    }



                }

                if (type == 'dashed' || type == 'dashed-arrow' || type == 'arrow' || type == 'normal') {

                    var path = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    path.setAttribute('id', lineid);
                    path.setAttribute('x1', m01x);
                    path.setAttribute('y1', m01y);
                    path.setAttribute('x2', m02x);
                    path.setAttribute('y2', m02y);
                    path.setAttribute('stroke', color);
                    path.setAttribute('stroke-width', strokew);

                    if (type == 'dashed' || type == 'dashed-arrow') {

                        path.setAttribute('stroke-dasharray', '5,5');

                    }


                    if (type == 'arrow' || type == 'dashed-arrow') {

                        path.setAttribute('marker-end', 'url(#arrow' + lineid + ')');

                    }

                }

                var currpath = jQuery('#map_canvas_' + mapid + ' line#' + lineid);
                if (currpath.length == 0) {
                    if (mode == 'circle') {
                        jQuery('#map_canvas_' + mapid + ' svg circle').first().parent().before(path);

                    }
                    if (mode = 'text') {
                        jQuery('#map_canvas_' + mapid + ' svg text').first().parent().before(path);

                    }
                }

            }
        }

    }

}

function iwm_zoom(id,position,overlay) {

    var overlay = overlay || false;
    var thisMap = jQuery('#map_canvas_'+id);
    var container; 
    //init panzoom script
    if(overlay && overlay != id) {

        jQuery('#iwm_map_overlay .iwm_map_canvas').wrapAll('<div style="overflow:hidden;" id="iwm_pan_wrapper_'+id+'" />').wrapAll('<div id="iwm_pan_container_'+id+'" />');
        container = jQuery('#iwm_pan_container_'+id);

        container.wrap('<div id="iwm_control_'+id+'" />');
        jQuery('#iwm_control_'+id).prepend('<div data-step="0" id="iwm-controls-'+id+'" class="iwm-controls iwm-controls-'+position+'"></div>');
        //container.parent().parent().prepend('<div id="iwm-controls-'+id+'" class="iwm-controls iwm-controls-'+position+'"></div>');
        
        //thisMap = container;

        container.panzoom({
                disableZoom: false,
                contain: 'invert',
                cursor: "default",
        });

        container.mousedown(function() {
            jQuery(this).css('cursor', 'move');
        });
        container.mouseup(function() {
            jQuery(this).css('cursor', 'pointer');
        });


    
    } 

    if(!overlay) {

        thisMap.parent().prepend('<div data-step="0" id="iwm-controls-'+id+'" class="iwm-controls iwm-controls-'+position+'"></div>');

        thisMap.panzoom({
                disableZoom: true,
                contain: 'invert',
                cursor: "default",
            });


        thisMap.mousedown(function() {
            jQuery(this).css('cursor', 'move');
        });
        thisMap.mouseup(function() {
            jQuery(this).css('cursor', 'pointer');
        });

    }

    if(id!=overlay) {

        jQuery('#iwm-controls-'+id).append(function() {

        return jQuery('<div class="iwm-controls-zoom-in">+</div>').click(function() {

                controlparent = jQuery(this).parent();
                cur_click = parseInt(controlparent.attr('data-step'));
                map = thisMap;
                
                if(parseInt(map.width())<=6000) {

                    controlparent.attr('data-step',cur_click+1);
                    
                    var step = (parseInt(map.width())*0.3);

                    var newh = (parseInt(map.height())/parseInt(map.width()))*(parseInt(map.width())+step);
                    var neww = parseInt(map.width())+step;

                    //console.log(neww);
                    
                    //we also calculate margins, so the zoom is central
                    var extrah = parseInt(newh) - parseInt(map.height());
                    var extraw = parseInt(neww) - parseInt(map.width());


                    if(!overlay) {

                        //we get the transform values and not the margins, so it works with the pan script
                        var transform = map.css('transform');
                        var matrix = transform.replace(/[^0-9\-.,]/g, '').split(',');

                        if(matrix[5] == null) {
                            matrix[5] = 0;
                            matrix[4] = 0;
                        }

                        var ctop = parseInt(matrix[5]);
                        var cleft = parseInt(matrix[4]);

                        var newtop = ctop-(extrah/2);
                        var newleft = cleft-(extraw/2);

                        //apply data
                        map.height(newh).width(neww);
                        map.attr('data-iwm-zwidth',neww).attr('data-iwm-zheight',newh);

                        map.css('transform','matrix(1, 0, 0, 1,'+newleft+','+newtop+')');

                        iwmoptions[id].width = neww;
                        iwmoptions[id].height = newh;
                        iwmgeocharts[id].draw(iwmdata[id], iwmoptions[id]);

                        thisMap.panzoom('resetDimensions');

                    }
                    

                    //to apply zoom to overlay map container instead
                    if(overlay) {

                        var transform = container.css('transform');
                        var matrix = transform.replace(/[^0-9\-.,]/g, '').split(',');

                        if(matrix[5] == null) {
                            matrix[5] = 0;
                            matrix[4] = 0;
                        }

                        var ctop = parseInt(matrix[5]);
                        var cleft = parseInt(matrix[4]);

                        var newtop = ctop-(extrah/2);
                        var newleft = cleft-(extraw/2);

                        container.height(newh).width(neww);
                        container.css('transform','matrix(1, 0, 0, 1,'+newleft+','+newtop+')');

                        iwmoptions[id].width = neww;
                        iwmoptions[id].height = newh;
                        iwmgeocharts[id].draw(iwmdata[id], iwmoptions[id]);
                        iwmoptions[overlay].width = neww;
                        iwmoptions[overlay].height = newh;
                        iwmgeocharts[overlay].draw(iwmdata[overlay], iwmoptions[overlay]);
                        container.panzoom('resetDimensions');
                    }

                } else {

                    //what to do if zoom limit was reached

                }
                
                

            });

    }).append(function() {

        return jQuery('<div class="iwm-controls-zoom-out">-</div>').click(function() {

                controlparent = jQuery(this).parent();
                cur_click = parseInt(controlparent.attr('data-step'));

                if(cur_click > 0) {

                    map = thisMap;
                    //var step = (parseInt(map.width())*0.2);

                    var prevw = (10*parseInt(map.width()))/13;
                    var step = parseInt(map.width()) - prevw;
                    var neww = prevw;

                    //console.log(neww);

                    if(!overlay) {

                        var parentw = parseInt(map.parent().width());
                        var parenth = parseInt(map.parent().height());

                    }

                    if(overlay) {

                        var parentw = parseInt(container.parent().width());
                        var parenth = parseInt(container.parent().height());

                    }


                    if(parentw <= neww) {

                        newh = (parseInt(map.height())/parseInt(map.width()))*(parseInt(map.width())-step);
                            
                        //we also set the margins, so the zoom is central
                        var extrah = parseInt(map.height())-parseInt(newh);
                        var extraw = parseInt(map.width())-parseInt(neww);

                        if(overlay) {

                            var transform = container.css('transform');

                        }

                        if(!overlay) {

                            var transform = map.css('transform');

                        }

                        
                        var matrix = transform.replace(/[^0-9\-.,]/g, '').split(',');

                        var ctop = parseInt(matrix[5]);
                        var cleft = parseInt(matrix[4]);

                        var newtop = ctop+(extrah/2);
                        var newleft = cleft+(extraw/2);

                        //not to let map leave parent container
                        if(newleft > 0) {
                            newleft = 0;
                        }
                        if(newtop > 0) {
                            newtop = 0;
                        }

                        var maxh = newh - parenth;
                        var maxh = maxh - (maxh)*2;
                        if(newtop < maxh) {
                            newtop = maxh;
                        }

                        var maxw = neww - parentw;
                        var maxw = maxw - (maxw)*2;
                        if(newleft < maxw) {
                            newleft = maxw;
                        }

                        if(!overlay) {

                            map.height(newh);
                            map.width(neww);
                            map.attr('data-iwm-zwidth',neww).attr('data-iwm-zheight',newh);
                            map.css('transform','matrix(1, 0, 0, 1,'+newleft+','+newtop+')');


                            iwmoptions[id].width = neww;
                            iwmoptions[id].height = newh;
                            iwmgeocharts[id].draw(iwmdata[id], iwmoptions[id]);

                            thisMap.panzoom('resetDimensions');

                            controlparent.attr('data-step',cur_click-1);


                        }
                        

                        //to apply zoom to overlay map
                        if(overlay) {

                            container.height(newh);
                            container.width(neww);
                            container.attr('data-iwm-zwidth',neww).attr('data-iwm-zheight',newh);
                            container.css('transform','matrix(1, 0, 0, 1,'+newleft+','+newtop+')');

                            iwmoptions[id].width = neww;
                            iwmoptions[id].height = newh;
                            iwmgeocharts[id].draw(iwmdata[id], iwmoptions[id]);

                            iwmoptions[overlay].width = neww;
                            iwmoptions[overlay].height = newh;
                            iwmgeocharts[overlay].draw(iwmdata[overlay], iwmoptions[overlay]);
                            container.panzoom('resetDimensions');

                            controlparent.attr('data-step',cur_click-1);


                        }
        
                    }

                }
                
            });

    });

    }

}

//simple sort function. We use it in the connect marker function
function iwm_sortNumber(a,b) {
    return a - b;
}




/*
jQuery(document).ajaxSuccess(function($) {

    iwmDrawVisualization(); 
        
}); */