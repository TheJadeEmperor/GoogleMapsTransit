<?
$long = $_GET['long']; 
$lat = $_GET['lat']; 

//default location = times square
if($lat == '') $lat = 40.758895;
if($long == '') $long = -73.985131;
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//maps.googleapis.com/maps/api/js?libraries=places"></script>
<script src="jquery.csv-0.71.js"></script>
<script type="text/javascript">
    var map;
    var infowindow;
    var service;
    function initialize(lat,lng) {
        var origin = new google.maps.LatLng(lat,lng);
       
        map = new google.maps.Map(document.getElementById('map'), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: origin,
            zoom: 15
        });
        
        var marker = new google.maps.Marker({
            map: map,
            position: origin
        });
     
        //createMarker(marker); 
     
        var request = {
            location: origin, 
            radius: 500,
            types: ['train_station','subway_station','transit_station']
        };
        infowindow = new google.maps.InfoWindow();
        service = new google.maps.places.PlacesService(map);
        service.search(request, callback);
    }

    function callback(results, status) {
        if (status == google.maps.places.PlacesServiceStatus.OK) {
            var trainsList = []; 
            var list = '';
                
            $.ajax({
                type: "GET",
                url: "StationEntrances.csv",
                dataType: "text",
                success: function(data) {

                    var csvData = $.csv.toArrays(data); 

                    for (var l = 0; l < csvData.length; l++) {
                        var subwayLat = csvData[l][3];
                        var subwayLong = csvData[l][4];
                        
                        for (var p = 0; p < results.length; p++) {
                            //console.log( results.length );
                            var place = results[p];
                            var placeLoc = place.geometry.location;
                            
                            var placeLat = parseFloat(placeLoc.lat().toFixed(6));
                            var placeLong = parseFloat(placeLoc.lng().toFixed(6));
                            //console.log( placeLat );
                            //console.log( subwayLat +' '+placeLat );
                            if((subwayLat === placeLat) && (subwayLong === placeLong)) {
                                //console.log(placeLat+' '+placeLong); 
                                
                                results[p]['placeLat'] = placeLat; 
                                results[p]['placeLong'] = placeLong;
                                results[p]['info'] = 'found';
                                results[p]['info'] = csvData[l][5] + ' ' + csvData[l][6] + ' ' + csvData[l][7] + ' ' 
                                    + csvData[l][8] + ' ' + csvData[l][9] + ' ' + csvData[l][10] + ' ' + csvData[l][11] + ' '
                                    + csvData[l][12] + ' ' + csvData[l][13] + ' ' + csvData[l][14] + ' '  + csvData[l][15];     
                                
                                
                                for(t = 5; t <= 15; t++){
                                    var found = jQuery.inArray(csvData[l][t], trainsList);
                                    if (found >= 0) {
                                        // Element was found, remove it.
                                        //trainsList.splice(found, 1);
                                        console.log(csvData[l][t]);
                                    } else {
                                        // Element was not found, add it.
                                        trainsList.push(csvData[l][t]);
                                        console.log(csvData[l][t]);
                                    }
                                } //for
                                 
                            } //if
                            
                        } //for
                    } //for
                    
                   
                    //create red markers on map
                    for (var p = 0; p < results.length; p++) {
                        createMarker(results[p]); 
                    }
                    
                    $.each( trainsList, function( key, value ) {
                        console.log( key + ": " + value );
                        if(value != 'undefined' && value != '')
                        list += value + ', ';
                    });

                    list += ' various buses';
                    $('#trains').html(list);
                } //function
            }); //ajax
        }
    }

    function createMarker(place) {
      
        var placeLoc = place.geometry.location;
        var marker = new google.maps.Marker({
          map: map,
          position: place.geometry.location
        });
        
        //place.placeLat; place.placeLong
         
        var content='<strong style="font-size:1.2em">'+place.name+'</strong>'+
                    '<br/><strong>Latitude:</strong> '+placeLoc.lat()+
                    '<br/><strong>Longitude:</strong> '+placeLoc.lng()+
                    '<br/><strong>Type:</strong> '+place.types[0]+
                    '<br/><strong>Train:</strong> '+(place.types[0]);
        var more_content='';
        console.log(place);
        
        //make a request for further details
        service.getDetails({reference:place.reference}, function (place, status) {
            if (status == google.maps.places.PlacesServiceStatus.OK) {
              more_content='<hr/><strong><a href="'+place.url+'" target="details">Details</a>';

                if(place.website) {
                    more_content+='<br/><br/><strong><a href="'+place.website+'" target="details">'+place.website+'</a>';      
                }
            }
        });


    google.maps.event.addListener(marker, 'click', function() {
          
            infowindow.setContent(content+more_content);
            infowindow.open(map, this);
        });
    }

    //long, lat
    google.maps.event.addDomListener(window, 'load', function(){
    //    initialize(40.7411095,-73.9888796); //OMG office
        initialize(<?=$lat?>,<?=$long?>);
    });
    
</script>

<div id="map" style="height:400px; width: 650px"></div>

<p>&nbsp;</p><p>&nbsp;</p>

<div>Trains: <div id="trains"></div> </div>