function redrawcrop() {


        console.log('Redraw Map CSS triggered');

        var marginhorizontal = document.getElementsByName('iwm_left')[0].value;
        var marginvertical = document.getElementsByName('iwm_top')[0].value;
        var percentagesize = document.getElementsByName('iwm_size')[0].value;
        var hsize = document.getElementsByName('iwm_hsize')[0].value;

        var hovercolor = document.getElementsByName('hovercolor')[0].value.toLowerCase();
        var inactivecolor = document.getElementsByName('ina_color')[0].value.toLowerCase();

        var showcursor = '';
        if (document.getElementsByName('showcursor')[0].checked) {
            showcursor = 1;
        }

        var bordercolor = document.getElementsByName('bcolor')[0].value.toLowerCase();
        var borderwidth = document.getElementsByName('bwidth')[0].value;
        var borderinawidth = document.getElementsByName('biwidth')[0].value;

        var bgimage = document.getElementsByName('bgimage')[0].value;

        var bgrepeat = '';
        if (document.getElementsByName('bgrepeat')[0].checked) {
            bgrepeat = 1;
        }

        var tooltipfontfamily = document.getElementsByName('tooltipfontfamily')[0].value;
        var tooltipfontsize = document.getElementsByName('tooltipfontsize')[0].value;
        var tooltipbg = document.getElementsByName('tooltipbg')[0].value;
        var tooltipminwidth = document.getElementsByName('tooltipminwidth')[0].value;

        var tooltipbordercolor = document.getElementsByName('tooltipbordercolor')[0].value;
        var tooltipborderwidth = document.getElementsByName('tooltipborderwidth')[0].value;

        var tooltiphidetitle = '';
        if (document.getElementsByName('tooltiphidetitle')[0].checked) {
            tooltiphidetitle = 1;
        }

        var fontawesomeapply = '';
        if (document.getElementsByName('fontawesomeapply')[0].checked) {
            fontawesomeapply = 1;
        }
        var fontawesomeinclude = '';
        if (document.getElementsByName('fontawesomeinclude')[0].checked) {
            fontawesomeinclude = 1;
        }

        //to create styles for preview
        var mapstyle = document.getElementById("visualization").style;

        //zoom effect
        if (marginhorizontal != '') {
            mapstyle.marginLeft = marginhorizontal + "%";
        } else {
            mapstyle.marginLeft = '0%'
        }
        if (marginvertical != '') {
            mapstyle.marginTop = marginvertical + "%";
        } else {
            mapstyle.marginTop = '0%'
        }
        if (percentagesize != '') {
            mapstyle.width = percentagesize + "%";
            mapstyle.height = percentagesize + "%";
        } else {
            mapstyle.width = '100%';
            mapstyle.height = '100%'
        }

         //we remove the temp style
         var tempcss = document.getElementById("tempcss");
         if(tempcss) {
            tempcss.parentNode.removeChild(tempcss);
         }

        //styles with advanced selectors, we need to add them to the css file
        //var tempcss = document.styleSheets[0];
        tempcss = (function() {
            // Create the <style> tag
            var style = document.createElement("style");
            style.setAttribute("id", "tempcss");

            // Add a media (and/or media query) here if you'd like!
            // style.setAttribute("media", "screen")
            // style.setAttribute("media", "only screen and (max-width : 1024px)")

            // WebKit hack :(
            style.appendChild(document.createTextNode(""));

            // Add the <style> element to the page
            document.head.appendChild(style);

            return style.sheet;
        })();

       

        //height 1 (1)
        if (hsize != '' && hsize != '61,7') {
            tempcss.insertRule('#visualization-wrap-responsive:after { padding-top: ' + hsize + '% !important}', 0);
        } else {
            tempcss.insertRule('#visualization-wrap-responsive:after {}', 0);
        }

        //hover effect 6 (7)
        if (hovercolor != '') {
            //tempcss.insertRule('#visualization path:not([fill^="'+inactivecolor+'"]):hover { fill:'+hovercolor+'; }', 0);
            //tempcss.insertRule('#visualization circle:hover { fill:'+hovercolor+'; }', 0);

            tempcss.insertRule('#visualization circle:hover { fill:' + hovercolor + '; }', 0);
            tempcss.insertRule('#visualization text:hover { fill:' + hovercolor + '; }', 0);

            tempcss.insertRule('#visualization path[stroke-width^="3"] + path { display:none; }', 0);
            tempcss.insertRule('#visualization path[stroke-width^="3"] + path + path:not([fill^="' + inactivecolor + '"]) { display:none; }', 0);
            tempcss.insertRule('#visualization path[stroke-width^="3"] { fill:' + hovercolor + ';  }', 0);

            tempcss.insertRule('#visualization path[fill^="' + inactivecolor + '"] { pointer-events: none; }',0);
            tempcss.insertRule('#visualization path[fill^="none"] { pointer-events: none; }',0);

            var bw = 1;
            if (borderwidth != '') {
                bw = borderwidth;
            }

            tempcss.insertRule('#visualization path:not([fill^="' + inactivecolor + '"]) + path[stroke-width^="3"] { stroke-width:' + bw + '; stroke-opacity:0; stroke:' + hovercolor + '  }', 0);


        } 


        //hand cursor 3 (10)
        if (showcursor == 1) {
            tempcss.insertRule('#visualization path:not([fill^="' + inactivecolor + '"]):hover { cursor:pointer; }', 0);
            tempcss.insertRule('#visualization circle:hover { cursor:pointer; }', 0);
            tempcss.insertRule('#visualization text:hover { cursor:pointer; }', 0);
        } else {
            tempcss.insertRule('#visualization path:not([fill^="#f5f5f5"]):hover {}', 0);
            tempcss.insertRule('#visualization circle:hover { }', 0);
            tempcss.insertRule('#visualization text:hover { }', 0);
        }


        //borders colour 1 (11)
        if (bordercolor != '') {
            tempcss.insertRule('#visualization path { stroke:' + bordercolor + ' }', 0);
        } else {
            tempcss.insertRule('#visualization path { }', 0);
        }

        //borderwidth 1 (12)
        if (borderwidth != '') {
            tempcss.insertRule('#visualization path { stroke-width:' + borderwidth + ' }', 0);
        } else {
            tempcss.insertRule('#visualization path { }', 0);
        }

        //inactive border width 3 (15)
        if (borderinawidth != '') {
            tempcss.insertRule('#visualization path[fill^="' + inactivecolor + '"] { stroke-width:' + borderinawidth + ' }', 0);
            tempcss.insertRule('#visualization path[fill^="' + inactivecolor + '"]:hover { stroke-width:' + borderinawidth + ' }', 0);
            tempcss.insertRule('#visualization path[fill^="none"] { stroke-opacity:0; }', 0);

        } else {
            tempcss.insertRule('#visualization path { }', 0);
            tempcss.insertRule('#visualization path { }', 0);
            tempcss.insertRule('#visualization path { }', 0);
        }


        //background image
        if (bgimage != '') {
            document.getElementsByName('bg_color')[0].value = 'transparent';
            mapstyle.backgroundImage = "url('" + bgimage + "')";
        }
        if (bgimage == '') {
            mapstyle.backgroundImage = "none";
        }

        //background repeat
        if (bgimage != '' && bgrepeat == 1) {
            mapstyle.backgroundRepeat = "repeat";
            mapstyle.backgroundSize = "auto";
        }

        if (bgimage != '' && bgrepeat == '') {
            mapstyle.backgroundSize = "100%";
            mapstyle.backgroundRepeat = "no-repeat";
        }

        //Tooltip CSS

        //font family 1 (16)
        if (tooltipfontfamily != '') {
            tempcss.insertRule('#visualization .google-visualization-tooltip { font-family:"' + tooltipfontfamily + '"  !important; }', 0);
        } else {
            tempcss.insertRule('#visualization .google-visualization-tooltip {}', 0);
        }

        //font size 1 (17)
        if (tooltipfontsize != '') {
            tempcss.insertRule('#visualization .google-visualization-tooltip { font-size:' + tooltipfontsize + '}', 0);
        } else {
            tempcss.insertRule('#visualization .google-visualization-tooltip {}', 0);
        }
        //background color 1 (18)
        if (tooltipbg != '') {
            tempcss.insertRule('#visualization .google-visualization-tooltip { background-color:' + tooltipbg + '}', 0);
        } else {
            tempcss.insertRule('#visualization .google-visualization-tooltip {}', 0);
        }
        //min width 1 (19)
        if (tooltipminwidth != '') {
            tempcss.insertRule('#visualization .google-visualization-tooltip { width:' + tooltipminwidth + '}', 0);
        } else {
            tempcss.insertRule('#visualization .google-visualization-tooltip {}', 0);
        }
        //border colout 1 (20)
        if (tooltipbordercolor != '') {
            tempcss.insertRule('#visualization .google-visualization-tooltip { border-color:' + tooltipbordercolor + '}', 0);
        } else {
            tempcss.insertRule('#visualization .google-visualization-tooltip {}', 0);
        }
        //border width 1 (21)
        if (tooltipborderwidth != '') {
            tempcss.insertRule('#visualization .google-visualization-tooltip { border-width:' + tooltipborderwidth + '}', 0);
        } else {
            tempcss.insertRule('#visualization .google-visualization-tooltip {}', 0);
        }
        //hide title 1 (22)
        if (tooltiphidetitle != '') {
            tempcss.insertRule('#visualization .google-visualization-tooltip-item:first-child { display:none;}', 0);
        } else {
            tempcss.insertRule('#visualization .google-visualization-tooltip {}', 0);
        }

        //2 (24)        
        if (fontawesomeapply != '') {
            tempcss.insertRule('#visualization text { font-family:fontAwesome;}', 0);
            tempcss.insertRule('#add-table { font-family: fontAwesome;}', 0);
        } else {
            tempcss.insertRule('#visualization text {}', 0);
            tempcss.insertRule('#add-table { font-family:inherit;}', 0);
        }

        //set the hidden field content
        //document.getElementsByName('customcss')[0].value = marginhorizontal+','+marginvertical+','+percentagesize+','+hsize+','+hovercolor+','+showcursor+','+bordercolor+','+borderwidth+','+borderinawidth+','+bgimage+','+bgrepeat;
        document.getElementsByName('customcss')[0].value = '{"iwm_size":"' + percentagesize + '","iwm_hsize":"' + hsize + '","iwm_left":"' + marginhorizontal + '","iwm_top":"' + marginvertical + '","hovercolor":"' + hovercolor + '","showcursor":"' + showcursor + '","bcolor":"' + bordercolor + '","bwidth":"' + borderwidth + '","biwidth":"' + borderinawidth + '","bgimage":"' + bgimage + '","bgrepeat":"' + bgrepeat + '","tooltipfontfamily":"' + tooltipfontfamily + '","tooltipfontsize":"' + tooltipfontsize + '","tooltipbg":"' + tooltipbg + '","tooltipminwidth":"' + tooltipminwidth + '","tooltiphidetitle":"' + tooltiphidetitle + '","tooltipbordercolor":"' + tooltipbordercolor + '","tooltipborderwidth":"' + tooltipborderwidth + '","fontawesomeinclude":"' + fontawesomeinclude + '","fontawesomeapply":"' + fontawesomeapply + '"}';
        drawVisualization();
    
}



function iwmcsscontrol(control) {
    if (control == "widthplus") {
        document.getElementsByName('iwm_size')[0].value = (parseInt(document.getElementsByName('iwm_size')[0].value) || 100) + 5;
    }
    if (control == "widthminus") {
        document.getElementsByName('iwm_size')[0].value = (parseInt(document.getElementsByName('iwm_size')[0].value) || 100) - 5;
    }
    if (control == "up") {
        document.getElementsByName('iwm_top')[0].value = (parseInt(document.getElementsByName('iwm_top')[0].value) || 0) - 5;
    }
    if (control == "down") {
        document.getElementsByName('iwm_top')[0].value = (parseInt(document.getElementsByName('iwm_top')[0].value) || 0) + 5;
    }
    if (control == "left") {
        document.getElementsByName('iwm_left')[0].value = (parseInt(document.getElementsByName('iwm_left')[0].value) || 0) - 5;
    }
    if (control == "right") {
        document.getElementsByName('iwm_left')[0].value = (parseInt(document.getElementsByName('iwm_left')[0].value) || 0) + 5;
    }
    if (control == "verticalplus") {
        document.getElementsByName('iwm_hsize')[0].value = (parseInt(document.getElementsByName('iwm_hsize')[0].value) || 62) + 5;
    }
    if (control == "verticalminus") {
        document.getElementsByName('iwm_hsize')[0].value = (parseInt(document.getElementsByName('iwm_hsize')[0].value) || 62) - 5;
    }

    redrawcrop();
}


function expandcustomcss() {

    document.getElementById("iwm-custom-css").style.display = 'block';
    document.getElementById("iwmexpandcss").innerHTML = '<a onclick="closecustomcss()"><i class="fa fa-chevron-circle-down fa-lg"></i></i> Close Custom CSS Options Box</a>';

}

function closecustomcss() {

    document.getElementById("iwm-custom-css").style.display = 'none';
    document.getElementById("iwmexpandcss").innerHTML = '<a onclick="expandcustomcss()"><i class="fa fa-chevron-circle-right fa-lg"></i></i> Expand Custom CSS Options Box</a>';

}


function clearCssValues() {

    document.getElementsByName('iwm_size')[0].value = '';
    document.getElementsByName('iwm_top')[0].value = '';
    document.getElementsByName('iwm_left')[0].value = '';
    document.getElementsByName('iwm_hsize')[0].value = '';
    document.getElementsByName('hovercolor')[0].value = '';
    document.getElementsByName('showcursor')[0].checked = false;
    document.getElementsByName('bcolor')[0].value = '';
    document.getElementsByName('bwidth')[0].value = '';
    document.getElementsByName('biwidth')[0].value = '';
    document.getElementsByName('bgimage')[0].value = '';
    document.getElementsByName('bgrepeat')[0].checked = false;
    document.getElementsByName('customcss')[0].value = '';
    redrawcrop();

}


function clearCropValues() {

    document.getElementsByName('iwm_size')[0].value = '';
    document.getElementsByName('iwm_top')[0].value = '';
    document.getElementsByName('iwm_left')[0].value = '';
    document.getElementsByName('iwm_hsize')[0].value = '';

    redrawcrop();

}

function getAddress() {

    var latlonspan = document.getElementById("latlonvalues");

    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById('mapsearch').value;

    geocoder.geocode({
        'address': address
    }, function(results, status) {

        if (status == google.maps.GeocoderStatus.OK) {
            var glatitude = results[0].geometry.location.lat();
            var glongitude = results[0].geometry.location.lng();

            latlonspan.innerHTML = address + ": " + glatitude + " " + glongitude + " [<a href='javascript:void(0);' title='Copy this values to the Region Code field' onclick='usethis(" + glatitude + "," + glongitude + ")'>use this</a>]";

        } else {
            latlonspan.innerHTML = 'Impossible to locate that Address';
        }
    });
}


function usethis(lat, lon) {
    var inp = document.addimap.cd;
    inp.value = lat + " " + lon;
}

function addPlaceToTable() {

    var code = document.addimap.cd.value.replace(/;/g, "");
    var title = document.addimap.c.value.replace(/;/g, "&#59");
    var tooltip = document.addimap.t.value.replace(/;/g, "&#59");
    var action = document.addimap.u.value.replace(/;/g, "&#59"); // special char &#59 = ;
    var color = document.addimap.cl.value.replace(/;/g, "");

    var code = code.replace(/,/g, " ");
    var title = title.replace(/,/g, "&#44");
    var tooltip = tooltip.replace(/,/g, "&#44");
    var action = action.replace(/,/g, "&#44"); // special char &#44 = ,
    var color = color.replace(/,/g, "");

    var newtext = code + ',' + title + ',' + tooltip + ',' + action + ',' + color + ';\n';
    document.addimap.places.value += newtext;
    document.addimap.cd.value = "";
    document.addimap.c.value = "";
    document.addimap.t.value = "";
    document.addimap.u.value = "";

    iwmdataToTable();
}

function iwmdataToTable() {


    var oldText = document.getElementById("places").value;
    var oldTextArr = oldText.split(";");
    oldTextArr.pop();
    var newText = "";
    for (i = 0; i < oldTextArr.length; i++) {
        oldTextArr[i] = oldTextArr[i].replace(/\r/, "");
        oldTextArr[i] = oldTextArr[i].replace(/^\'/, "");
        oldTextArr[i] = oldTextArr[i].replace(/^\"/, "");
        oldTextArr[i] = oldTextArr[i].replace(/"$/, "");
        oldTextArr[i] = oldTextArr[i].replace(/'$/, "");



        var entry = oldTextArr[i].split(",");
        var colori = entry[4];

        //to disable html
        var rendertags = document.getElementById('rendertags');
        var renderhtml = false;
        if (rendertags.checked == true) {
            renderhtml = true;
        }
        if (!renderhtml) {
            oldTextArr[i] = jQuery("<div/>").text(oldTextArr[i]).html();
        }

        oldTextArr[i] = oldTextArr[i] + "<div class='colorsample' style='background-color:" + colori + "'></div>";

        oldTextArr[i] = "<tr><td>" + oldTextArr[i] + "</td><td style='white-space:nowrap;'><i alt='edit' title='Edit Entry' class='fa fa-pencil-square' aria-hidden='true' onclick='editPlace(" + i + ");'></i><i class='fa fa-plus-circle' alt='Delete' title='Delete' aria-hidden='true' onclick='deletePlace(" + i + ");'></i></td></tr>";
    }

    var linesep = ",";


    for (i = 0; i < oldTextArr.length; i++) {


        oldTextArr[i] = oldTextArr[i].replace(new RegExp(linesep, "gi"), "</td><td>");
        newText = newText + oldTextArr[i] + "\n";
        newText = newText.replace(new RegExp("&#59", "gi"), ";");
        newText = newText.replace(new RegExp("&#44", "gi"), ",");


    }

    var header = "<tr><th style='width:10%;'>Region Code</th><th style='width:20%;'>Title</th><th style='width:20%;'>Tooltip Text</th><th style='width:23%;'>Action Value</th><th style='width:10%;'>Color</th><th style='width:7%;'>&nbsp;</th></tr>";
    newText = "<table class='data-content-table'>\n" + header + "\n" + newText + "</table>\n";

    var span = document.getElementById("htmlplacetable");
    span.innerHTML = newText; // clear existing

    drawVisualization();
}

function updatePlace(placeid) {

    //Get old text
    var oldText = document.getElementById("places").value;
    //Split into lines
    var oldTextArr = oldText.split(";");
    oldTextArr.pop();
    var newText = "";
    for (i = 0; i < oldTextArr.length; i++) {

        if (i == placeid) {
            var updatecode = document.getElementById("input-" + placeid + "-0").value.replace(/,/g, " ");
            var updatetitle = document.getElementById("input-" + placeid + "-1").value.replace(/,/g, "&#44");
            var updatetooltip = document.getElementById("input-" + placeid + "-2").value.replace(/,/g, "&#44");
            var updateaction = document.getElementById("input-" + placeid + "-3").value.replace(/,/g, "&#44");
            var updatecolor = document.getElementById("input-" + placeid + "-4").value.replace(/,/g, "");



            var updatecode = updatecode.replace(/;/g, " ");
            var updatetitle = updatetitle.replace(/;/g, "&#59");
            var updatetooltip = updatetooltip.replace(/;/g, "&#59");
            var updateaction = updateaction.replace(/;/g, "&#59");
            var updatecolor = updatecolor.replace(/;/g, "");

            //if tinymce is enabled
            var editor = document.getElementById('editor').value;
            if (editor == '1') {
                var mapaction = document.getElementsByName('map_action')[0].value;
                if (mapaction != 'i_map_action_open_url' && mapaction != 'i_map_action_open_url_new' && mapaction != 'i_map_action_alert' && mapaction != 'i_map_action_colorbox_iframe' && mapaction != 'i_map_action_colorbox_inline' && mapaction != 'i_map_action_colorbox_image' && mapaction != 'none') {
                    mapaction = true;
                } else {
                    mapaction = false;
                }
                var tooltip = parseInt(iwmparam[0]['usehtml']);
                if (mapaction) {
                    var action = tinyMCE.get("input-" + placeid + "-3").getContent();
                    action = action.replace(/,/g, "&#44");
                    updateaction = action.replace(/;/g, "&#59");
                }
                if (tooltip == 1) {
                    var tooltip = tinyMCE.get("input-" + placeid + "-2").getContent();
                    tooltip = tooltip.replace(/,/g, "&#44");
                    updatetooltip = tooltip.replace(/;/g, "&#59");
                }
            }

            newText = newText + "\n" + updatecode + "," + updatetitle + "," + updatetooltip + "," + updateaction + ",#" + updatecolor + ";";
        } else {
            newText = newText + oldTextArr[i] + ";";
        }
    }
    document.getElementById("places").value = newText;
    iwmdataToTable();
}

function deletePlace(placeid) {

    var conf = confirm('Are you sure you want to delete this Region entry?');
    if (conf == true) {
        var oldText = document.getElementById("places").value;
        var oldTextArr = oldText.split(";");
        oldTextArr.pop();
        oldTextArr.splice(placeid, 1);

        var newText = "";
        for (i = 0; i < oldTextArr.length; i++) {
            newText = newText + oldTextArr[i] + ";";
        }
        document.getElementById("places").value = newText;
        iwmdataToTable();
    }
}




function editPlace(placeid) {

    var oldText = document.getElementById("places").value;

    var oldTextArr = oldText.split(";");
    oldTextArr.pop();
    var newText = "";
    for (i = 0; i < oldTextArr.length; i++) {
        oldTextArr[i] = oldTextArr[i].replace(/\r/, "");
        oldTextArr[i] = oldTextArr[i].replace(/^\'/, "");
        oldTextArr[i] = oldTextArr[i].replace(/^\"/, "");
        oldTextArr[i] = oldTextArr[i].replace(/"$/, "");
        oldTextArr[i] = oldTextArr[i].replace(/'$/, "");

        if (placeid == i) {


            var editArr = oldTextArr[i].split(",");
            oldTextArr[i] = "<tr class='editing-map-entry'>";
            for (ix = 0; ix < editArr.length; ix++) {
                if (ix != 4 && ix != 3 && ix != 2) {
                    var ixvalue = editArr[ix].replace(/'/g, "&#39;");

                    //ixvalue = ixvalue.replace(/&#44/g,"&#44;");	
                    //ixvalue = ixvalue.replace(/&#59/g,"&#59;");	

                    oldTextArr[i] = oldTextArr[i] + "<td><input type='text' id='input-" + placeid + "-" + ix + "' value='" + ixvalue + "'></td>\n";
                }
                if (ix == 3 || ix == 2) {
                    var ixvalue = editArr[ix].replace(/'/g, "&#39;");

                    //ixvalue = ixvalue.replace(/&#44/g,"&#44;");	
                    //ixvalue = ixvalue.replace(/&#59/g,"&#59;");

                    oldTextArr[i] = oldTextArr[i] + "<td><textarea class='tinymce-enabled' id='input-" + placeid + "-" + ix + "'>" + ixvalue + "</textarea></td>\n";
                }

                if (ix == 4) {
                    var colorinput = document.createElement("INPUT");
                    var inputname = 'input-' + placeid + '-' + ix;
                    var inputvalue = '#' + editArr[ix];
                    colorinput.type = 'text';
                    colorinput.id = String(inputname);
                    colorinput.value = String(inputvalue);
                    var col = new jscolor.color(colorinput);
                    oldTextArr[i] = oldTextArr[i] + "<td><span id='colori'></span></td>\n";

                }
            }


            oldTextArr[i] = oldTextArr[i] + "</td><td><i onclick='updatePlace(" + placeid + ");' class='fa fa-check-square' title='Submit Changes' aria-hidden='true'></i><i class='fa fa-undo' aria-hidden='true' title='Cancel' onclick='iwmdataToTable();'></i></td></tr>";

        } else {

            var entry = oldTextArr[i].split(",");
            var colori = entry[4];

            //to disable html
            var rendertags = document.getElementById('rendertags');
            var renderhtml = false;
            if (rendertags.checked == true) {
                renderhtml = true;
            }
            if (!renderhtml) {
                oldTextArr[i] = jQuery("<div/>").text(oldTextArr[i]).html();
            }

            oldTextArr[i] = oldTextArr[i] + "<div class='colorsample' style='background-color:" + colori + "'></div>";
            oldTextArr[i] = "<tr><td>" + oldTextArr[i] + "</td><td style='white-space:nowrap;'><i alt='edit' title='Edit Entry' class='fa fa-pencil-square' aria-hidden='true' onclick='editPlace(" + i + ");'></i><i class='fa fa-plus-circle' alt='Delete' title='Delete' aria-hidden='true' onclick='deletePlace(" + i + ");'></i></td></tr>";
        }

    }

    var linesep = ",";


    for (i = 0; i < oldTextArr.length; i++) {
        oldTextArr[i] = oldTextArr[i].replace(new RegExp(linesep, "gi"), "</td><td>");
        newText = newText + oldTextArr[i] + "\n";
        newText = newText.replace(new RegExp("&#59", "gi"), ";");
        newText = newText.replace(new RegExp("&#44", "gi"), ",");


    }

    var header = "<tr><th style='width:10%;'>Region Code</th><th style='width:20%;'>Title</th><th style='width:20%;'>Tooltip Text</th><th style='width:23%;'>Action Value</th><th style='width:10%;'>Color</th><th style='width:7%;'>&nbsp;</th></tr>";
    newText = "<table class='data-content-table'>\n" + header + "\n" + newText + "</table>\n";

    var span = document.getElementById("htmlplacetable");
    var actionvaluetip = document.getElementById("actionvaluetip");
    span.innerHTML = newText; // clear existing

    document.getElementById("colori").appendChild(colorinput);


    iwm_tinymce(placeid);

}

var apiversion = 1;

//google.load('visualization', apiversion , {'packages': ['geochart']});
//google.setOnLoadCallback(drawVisualization);

/*
var options = {
    packages: ['geochart'],
    callback: drawVisualization
};
google.load('visualization', apiversion, options);
*/



google.charts.load('42', {
    packages: ['geochart']
});

google.charts.setOnLoadCallback(initmap);



function drawVisualization() {

     

    var usehtml = parseInt(iwmparam[0]['usehtml']);
    var bgcolor = document.getElementsByName('bg_color')[0].value;
    var stroke = document.getElementsByName('border_stroke')[0].value;
    var bordercolor = document.getElementsByName('border_color')[0].value;
    var incolor = document.getElementsByName('ina_color')[0].value;
    var actcolor = document.getElementsByName('act_color')[0].value;
    var width = document.getElementsByName('width')[0].value;
    var height = document.getElementsByName('height')[0].value;
    var aspratio = document.getElementById('aspratio');
    var responsivemode = document.getElementsByName('responsivemode')[0].value;
    var interact = document.getElementById('interactive');
    var tooltipt = document.getElementsByName('tooltipt')[0].value;
    var areacombo = document.getElementsByName('region')[0].value;
    var areashow = areacombo.split(",");
    var region = areashow[0];
    var resolution = areashow[1];
    var markersize = document.getElementsByName('marker_size')[0].value;
    var displaym = document.getElementsByName('display_mode')[0].value;
    var placestxt = document.getElementsByName('places')[0].value.replace(/(\r\n|\n|\r)/gm, "");
    var places = placestxt.split(";");
    var projection = document.getElementsByName('mapprojection')[0].value;
	

    var pl = places;
	var newplaces = [];

	for (var i=0; i<pl.length;i++) {
 		
 		var ple = pl[i].split(",");
		
		if(ple[0].indexOf('group:') > -1) {
			
			
			var tempindex = ple[0].replace('group:','');

			temparray = tempindex.split('|');
			

			for(y=0;y<temparray.length; y++) {
				var newentry = temparray[y]+','+ple[1]+','+ple[2]+','+ple[3]+','+ple[4];
				newplaces.unshift(newentry);
			}
			
			
			
		} else {
			var sameentry = ple[0]+','+ple[1]+','+ple[2]+','+ple[3]+','+ple[4];
			newplaces.push(sameentry);
		}
 		
		
	}


		
	places = newplaces;


    //console.log(places);

    if (responsivemode == "on") {
        width = null;
        height = null;
    }

    var displaymode = "regions";

    if (displaym == "markers" || displaym == "markers02") {
        displaymode = "markers";
    }

    if (displaym == "text" || displaym == "text02") {
        displaymode = "text";
    }



    var ratio = false;
    if (aspratio.checked == true) {
        var ratio = true;
    }

    var interactive = 'true';
    if (interact.checked != true) {
        var interactive = 'false';
    }



    var toolt = 'focus';
    if (tooltipt == '0') {
        toolt = 'none';
    }
    if (tooltipt == '2') {
        var toolt = 'selection';
    }

    var data = new google.visualization.DataTable();

    if (displaym == "markers02" || displaym == "text02") {
        data.addColumn('number', 'Lat');
        data.addColumn('number', 'Long');
    }


    data.addColumn('string', 'Country'); // Implicit domain label col.
    data.addColumn('number', 'Value'); // Implicit series 1 data col.
    data.addColumn({
        type: 'string',
        role: 'tooltip',
        p: {
            html: true
        }
    }); // 
    var ivalue = new Array();
    var colorsmap = [];
    var colorsmapecho = "";

    //places.length-1 to eliminate empty value at the end
    for (var i = 0; i < places.length - 1; i++) {

        var entry = places[i].split(",");

        var ttitle = entry[1].replace(/&#59/g, ";");
        ttitle = ttitle.replace(/&#44/g, ",");
        var ttooltip = entry[2].replace(/&#59/g, ";");
        ttooltip = ttooltip.replace(/&#44/g, ",");


        //If data != markers02
        if (displaym != "markers02" && displaym != "text02") {



            data.addRows([
                [{
                    v: entry[0],
                    f: ttitle
                }, i, ttooltip]
            ]);

            var index = entry[0];
        } else {
            var trim = entry[0].replace(/^\s+|\s+$/g, "");
            var latlon = trim.split(/ /);
            var lat = parseFloat(latlon[0]);
            var lon = parseFloat(latlon[1]);



            data.addRows([
                [lat, lon, ttitle, i, ttooltip]
            ]);

            var index = lat;

        }
        var colori = entry[4];

        ivalue[index] = entry[3].replace(/&#59/g, ";");
        ivalue[index] = ivalue[index].replace(/&#44/g, ",");

        colorsmapecho = colorsmapecho + "'" + colori + "',";
        colorsmap.push(colori);
        ivalue.push(ivalue);
    }

    //colorsmap.pop();
    //ivalue.pop();

    defmaxvalue = 0;
    if ((places.length - 2) > 0) {
        defmaxvalue = places.length - 2;
    }

    var htmltooltip = false;
    if (usehtml == 1) {
        htmltooltip = true;
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
        tooltip: {
            trigger: toolt,
            isHtml: htmltooltip
        },
        domain: 'IN'
    };

    var chart = new google.visualization.GeoChart(document.getElementById('visualization'));

    google.visualization.events.addListener(chart, 'select', function() {
        var selection = chart.getSelection();

        if (selection.length == 1) {
            var selectedRow = selection[0].row;
            var selectedRegion = data.getValue(selectedRow, 0);
            if (ivalue[selectedRegion] != "") {
                alert(ivalue[selectedRegion]);
            }
        }
    });

    var iwm_img_div = document.getElementById('iwm_image_map');
    var iwm_img_field = document.getElementById('mapimage');

    google.visualization.events.addListener(chart, 'ready', function() {
        // iwm_img_div.innerHTML = '<a href="' + chart.getImageURI() + '" target="_blank">Download this map as an image</a>';
        iwm_img_field.value = chart.getImageURI();

    });

    chart.draw(data, options);




}

function showsimple() {
    document.getElementById('simple-table').style.display = 'block';
    document.getElementById('advanced-table').style.display = 'none';
    document.getElementById("shsimple").setAttribute("class", "activeb");
    document.getElementById("shadvanced").setAttribute("class", "inactiveb");
}

function showadvanced() {
    document.getElementById('simple-table').style.display = 'none';
    document.getElementById('advanced-table').style.display = 'block';
    document.getElementById("shsimple").setAttribute("class", "inactiveb");
    document.getElementById("shadvanced").setAttribute("class", "activeb");
}


function hidecustomsettings() {
    var e = document.getElementById('default-settings-table-add');
    e.style.display = 'none';
}

function showcustomsettings() {

    if (document.getElementsByName('use_defaults')[1].checked) {

        var e = document.getElementById('default-settings-table-add');
        e.style.display = 'block';
    }

}

function customoptionshow() {
    var e = document.getElementById('custom-action');
    e.style.display = 'block';
}

function customoptionhide() {
    var e = document.getElementById('custom-action');
    e.style.display = 'none';
}

function latlonshow() {
    var e = document.getElementById('latlondiv');
    e.style.display = 'block';
}

function latlonhide() {
    var e = document.getElementById('latlondiv');
    e.style.display = 'none';
}




function isolink() {

    var display = document.getElementsByName('display_mode')[0].value;
    var areacombo = document.getElementsByName('region')[0].value;
    var mapaction = document.getElementsByName('map_action')[0].value;
    var areashow = areacombo.split(",");
    var region = areashow[0];
    var resolution = areashow[1];
    var span = document.getElementById("iso-code-msg");

    if (resolution == 'countries' && display == "regions") {

        span.innerHTML = '<b><i class="fa fa-question-circle"></i> ' + document.getElementById('region')[document.getElementById('region').selectedIndex].innerHTML + '</b> - To create your interactive regions, when using the "Regions" display mode, use a country name as a string, or an uppercase <a href="http://en.wikipedia.org/wiki/ISO_3166-1">ISO-3166-1</a> code or its English text equivalent (for example, <i>GB</i> or <i>United Kingdom</i>). Check Google\'s <a href="https://developers.google.com/chart/interactive/docs/gallery/geochart#Continent_Hierarchy" target="_blank">Continents and Countries</a> list for aditional resources.';
        latlonhide();
    }

    if (resolution == 'provinces' && display == "regions") {
        var ct = areashow[0].length;
        var linkiso;
        if (ct > 2) {
            linkiso = "<a href='http://en.wikipedia.org/wiki/ISO_3166-2:US'>ISO-3166-2:US</a>";
        } else {
            linkiso = "<a target='_blank' href='http://en.wikipedia.org/wiki/ISO_3166-2:" + areashow[0] + "'>ISO-3166-2:" + areashow[0] + "</a>";
        }
        span.innerHTML = '<b><i class="fa fa-question-circle"></i> ' + document.getElementById('region')[document.getElementById('region').selectedIndex].innerHTML + '</b> - To create your interactive regions, use the ' + linkiso + ' codes.';
        latlonhide();
    }

    if (resolution == 'metros' && display == "regions") {

        span.innerHTML = '<b><i class="fa fa-question-circle"></i> ' + document.getElementById('region')[document.getElementById('region').selectedIndex].innerHTML + '</b> - To create your interactive regions, use these three-digit <a href="http://developers.google.com/adwords/api/docs/appendix/metrocodes" target="_blank">metropolitan area codes</a> as the region codes.'; // 
        latlonhide();
    }

    if (resolution == 'continents' && display == "regions") {

        span.innerHTML = '<b><i class="fa fa-question-circle"></i> ' + document.getElementById('region')[document.getElementById('region').selectedIndex].innerHTML + '</b> - Region Codes for Continents (When using Regions Display Mode): <br /> Africa - 002 | Europe - 150 | Americas - 019 | Asia - 142 | Oceania - 009'; // 
        latlonhide();

    }

    if (resolution == 'subcontinents' && display == "regions") {

        span.innerHTML = '<b><i class="fa fa-question-circle"></i> ' + document.getElementById('region')[document.getElementById('region').selectedIndex].innerHTML + '</b> - Region codes for Subcontinents (When using Regions Display Mode): <br />Africa - Northern Africa: 015, Western Africa: 011, Middle Africa: 017, Eastern Africa: 014, Southern Africa: 018;<br />Europe - Northern Europe: 154, Western Europe: 155, Eastern Europe: 151, Southern Europe: 039;<br />Americas - Northern America: 021, Caribbean: 029, Central America: 013, South America: 005;<br />Asia - Central Asia: 143, Eastern Asia: 030, Southern Asia: 034, South-Eastern Asia: 035, Western Asia: 145;<br />Oceania - Australia and New Zealand: 053, Melanesia: 054, Micronesia: 057, Polynesia: 061;'; // 

        latlonhide();

    }


    if (display == 'text') {

        span.innerHTML = '<b><i class="fa fa-question-circle"></i> Text Markers</b> - When using the Text Markers display mode, a colored text will be added to the specified region. When this mode is selected you can also use a a specific string address (for example, "1600 Pennsylvania Ave") or Berlin Germany as a Region Code. DO NOT use commas (,) or quotes("").<br /><strong style="color:red"> <i class="fa fa-exclamation-triangle"></i> If you experience slow loading of the Markers, consider using Text Labels - Coordinates Mode and use coordinate values in the Regions Code.</strong>'; // 

        latlonhide();

    }


    if (display == 'markers') {

        span.innerHTML = '<b><i class="fa fa-question-circle"></i> Round Markers (Text Code)</b> - When using the Markers display mode, a colored bubble will be added to the specified region. When this mode is selected you can also use a a specific string address (for example, "1600 Pennsylvania Ave") or Berlin Germany as a Region Code. DO NOT use commas (,) or quotes("").<br /><strong style="color:red"><i class="fa fa-exclamation-triangle"></i> If you experience slow loading of the Maps, consider using Markers (Coordinates) Display Mode.</strong>'; // 

        latlonhide();

    }

    if (display == 'markers02') {

        span.innerHTML = '<b><i class="fa fa-question-circle"></i> Round Markers (Coordinates Code)</b> - When using the Markers display mode, a colored bubble will be added to the specified region. When the Coordinates mode is chosen, you should insert the coordinates values in the Region Code, in this format: latitude longitude. <strong>Do not use commas, use a space to separate de values</strong>. Example:34.3071438 -53.7890625'; // 
        latlonshow();

    }

    if (display == 'text02') {

        span.innerHTML = '<b><i class="fa fa-question-circle"></i> Text Labels (Coordinates Code)</b> - When using the Text Labels display mode, a colored text will be added to the specified region. When the Coordinates mode is chosen, you should insert the coordinates values in the Region Code, in this format: latitude longitude. <strong>Do not use commas, use a space to separate de values</strong>. Example:34.3071438 -53.7890625'; // 
        latlonshow();

    }



    if (mapaction == 'i_map_action_open_url') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Action - Open URL</b> - The URL you specify in the "Action Value" field will open in the same window, after the user clicked on that region.'; // 
        actionvaluetip.innerHTML = 'URL to open.';

        customoptionhide();

    }
    if (mapaction == 'i_map_action_open_url_new') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Action - Open URL (new window)</b> - The URL you specify in the "Action Value" field will open in a new window, after the user clicked on that region.'; // 
        actionvaluetip.innerHTML = 'URL to open';

        customoptionhide();

    }

    if (mapaction == 'i_map_action_alert') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Action - Alert</b> - An alert message will display with the text you specify in the "Action Value" field.'; // 
        actionvaluetip.innerHTML = 'Message to display on alert';

        customoptionhide();

    }

    if (mapaction == 'i_map_action_content_below') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Display Content Below Map</b> - The content of the "Action Value" field will display inside a div under the map. The div will have the id="imapMAPIDmessage" (for example <i>imap1message</i>) and can be customized with CSS.'; // 
        actionvaluetip.innerHTML = 'Content to display below map. HTML can be used';

        customoptionhide();

    }

    if (mapaction == 'i_map_action_content_below_scroll') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Display Content Below Map & Scroll</b> - The content of the "Action Value" field will display inside a div under the map and the page will scroll to that div to make it visible on the screen. It\'s usefull when the maps are big and take all the screen. The div will have the id="imapMAPIDmessage" (for example <i>imap1message</i>) and can be customized with CSS.'; // 
        actionvaluetip.innerHTML = 'Content to display below map. HTML can be used';

        customoptionhide();

    }


    if (mapaction == 'i_map_action_content_above') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Display Content Above Map</b> - The content of the "Action Value" field will display inside a div above the map. The div will have the id="imapMAPIDmessage" (for example <i>imap1message</i>) and can be customized with CSS.'; // 
        actionvaluetip.innerHTML = 'Content to display above map. HTML can be used';
        customoptionhide();

    }

    if (mapaction == 'i_map_action_colorbox_content') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Display Content in Lightbox</b> - A lighbox window will open with the content of the "Action Value" field. '; // 
        actionvaluetip.innerHTML = 'Content to display inside lightbox. HTML can be used';
        customoptionhide();

    }

    if (mapaction == 'i_map_action_colorbox_iframe') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Display URL in a Lightbox (iframe)</b> - A lighbox window will open with the URL you place in the action value. '; // 
        actionvaluetip.innerHTML = 'Full URL to the page you want to open in the iframe.';
        customoptionhide();

    }

    if (mapaction == 'i_map_action_colorbox_inline') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Display inline content</b> - A lighbox window will open with the content of the inline element you specify in the action value. Use a class selector like ".name" or an id, like "#name".  '; // 
        actionvaluetip.innerHTML = '#ID or .class selector of the inline element to display.'
        customoptionhide();

    }

    if (mapaction == 'i_map_action_colorbox_image') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Display Image in a Lightbox </b> - A lighbox window will open with the image you specify in the action value. Use the complete URL to the image. '; // 
        actionvaluetip.innerHTML = 'Full URL to the image to open with the lightbox.'
        customoptionhide();

    }

    if (mapaction == 'i_map_action_custom') {

        span.innerHTML = span.innerHTML + '<br /><br /><b><i class="fa fa-question-circle"></i> Action - Custom</b> - Create your custom action.'; // 
        actionvaluetip.innerHTML = 'Content to use with your custom action.'
        customoptionshow();
    }

    if (mapaction == 'none') {

        customoptionhide();
    }


}

function isolinkcheck() {
    drawVisualization();
    isolink();

}

function initmap() {
    isolink();
    iwmdataToTable();
    showcustomsettings();
    redrawcrop();
}

function addslashes(str) {
    return (str + '').replace(/([\\"'])/g, "\\$1").replace(/\0/g, "\\0");
}

function iwm_tinymce(placeid) {
    var editor = document.getElementById('editor').value;
    if (editor == '1') {


        var mapaction = document.getElementsByName('map_action')[0].value;
        if (mapaction != 'i_map_action_open_url' && mapaction != 'i_map_action_open_url_new' && mapaction != 'i_map_action_alert' && mapaction != 'i_map_action_colorbox_iframe' && mapaction != 'i_map_action_colorbox_inline' && mapaction != 'i_map_action_colorbox_image' && mapaction != 'none') {
            mapaction = true;
        } else {
            mapaction = false;
        }

        var tooltip = parseInt(iwmparam[0]['usehtml']);

        for (var i = tinymce.editors.length - 1; i > -1; i--) {
            var ed_id = tinymce.editors[i].id;
            tinyMCE.execCommand("mceRemoveEditor", true, ed_id);
        }

        tinymce.init({

            selector: "input-" + placeid + "-2", // change this value according to your HTML
            plugins: "image fullscreen lists media wordpress wpautoresize wpeditimage wplink wpdialogs wpview wpembed code",
            menubar: false,
            forced_root_block: "",
            relative_urls : false,
            remove_script_host : false,
            convert_urls : true,
            toolbar: "bold italic underline alignleft aligncenter alignright alignjustify bullist numlist link unlink image fullscreen code "
        });

        if (tooltip == 1) {
            tinymce.execCommand('mceAddEditor', false, "input-" + placeid + "-2");
        }
        if (mapaction) {
            tinymce.execCommand('mceAddEditor', false, "input-" + placeid + "-3");
        }

    }


}


//set local storage for rendertags checkbox

jQuery(function() {
    var data = localStorage.getItem("rendertags");

    if (data !== null) {
        jQuery("input[name='rendertags']").attr("checked", "checked");
    }
});


//store rendertags checkbox state onclick
jQuery("input[name='rendertags']").click(function() {

    if (jQuery(this).is(":checked")) {
        localStorage.setItem("rendertags", jQuery(this).val());
    } else {
        localStorage.removeItem("rendertags");
    }

});



//window.onload = initmap;