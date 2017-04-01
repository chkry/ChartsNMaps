     //google.load('visualization', '1', {'packages': ['geochart']});
     //google.setOnLoadCallback(drawVisualization);

      function drawVisualization() {

      	return;

      	//currently not being used since version 1.6.

		var bgcolor = document.getElementsByName('i-world-map-settings[default_bg_color]')[0].value;  
		var stroke = document.getElementsByName('i-world-map-settings[default_border_stroke]')[0].value; 
		var bordercolor = document.getElementsByName('i-world-map-settings[default_border_color]')[0].value; 
		var incolor = document.getElementsByName('i-world-map-settings[default_ina_color]')[0].value;
		var actcolor = document.getElementsByName('i-world-map-settings[default_act_color]')[0].value;
		var markersize = document.getElementsByName('i-world-map-settings[default_marker_size]')[0].value;  
		var width = document.getElementsByName('i-world-map-settings[default_width]')[0].value; 
		var height = document.getElementsByName('i-world-map-settings[default_height]')[0].value; 
		var aspratio = document.getElementById('aspratio'); 
		var interact = document.getElementById('interactive');
		var tooltipt = document.getElementById('showtooltip'); 
		var mapprojection = document.getElementsByName('i-world-map-settings[map_projection]')[0].value; 
		var areacombo = document.getElementsByName('i-world-map-settings[default_region]')[0].value;
		var areashow = areacombo.split(",");
		var region = areashow[0]; 
		var resolution = areashow[1];
		var displaymode = document.getElementsByName('i-world-map-settings[default_display_mode]')[0].value;
		
		
		var ratio = false;
		if(aspratio.checked==true) {
			var ratio = true;	
		} 
		
		var interactive = 'true';
		if(interact.checked!=true) {
			var interactive = 'false';	
		}
		
		var toolt = 'focus';
		if(tooltipt.checked!=true) {
			var toolt = 'none';	
		}
		
       var data = new google.visualization.DataTable();
		data.addColumn('string', 'Country'); // Implicit domain label col.
		data.addColumn('number', 'Value'); // Implicit series 1 data col.
		data.addColumn({type:'string', role:'tooltip'}); // 
					
		data.addRows([
			['USA',1,'Tooltip Text'],
			['Canada',1,'Tooltip Text'],
			['France',1,'Tooltip Text'],
			['Germany',1,'Tooltip Text'],
			['Russia',1,'Tooltip Text']				
			]);
			
		var datam = new google.visualization.DataTable();
		datam.addColumn('string', 'Country'); // Implicit domain label col.
		datam.addColumn('number', 'Value'); // Implicit series 1 data col.
		datam.addColumn({type:'string', role:'tooltip'}); // 
					
		datam.addRows([
			['New York, US',1,'Tooltip Text'],
			['Paris, France',1,'Tooltip Text'],
			['Rome, Italy',1,'Tooltip Text'],
			['Berlin, Germany',1,'Tooltip Text'],
			['Lisbon, Portugal',1,'Tooltip Text'],
			['London, United Kingdom',1,'Tooltip Text']				
			]);

        var options = {
        	projection: mapprojection,
			backgroundColor: {fill:bgcolor,stroke:bordercolor ,strokeWidth:stroke },
			colorAxis:  {minValue: 0,  colors: [actcolor, actcolor]},
			legend: 'none',	
			backgroundColor: {fill:bgcolor,stroke:bordercolor ,strokeWidth:stroke },		
			datalessRegionColor: incolor,
			displayMode: displaymode, 
			enableRegionInteractivity: interactive,
			resolution: resolution,
			sizeAxis: {minValue: 1, maxValue:1,minSize:markersize,  maxSize: markersize},
			region:region,
			keepAspectRatio: ratio,
			width:width,
			height:height,
			tooltip: {textStyle: {color: '#555555'}, trigger:toolt},
			domain: 'IN'		
			
			};

        var chart = new google.visualization.GeoChart(document.getElementById('visualization'));
		if(displaymode=='regions') {
        chart.draw(data, options);
		} else {
		chart.draw(datam, options);
		}
    };
