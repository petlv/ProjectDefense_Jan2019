/**
 * ---------------------------------------
 * This demo was created using amCharts 4.
 *
 * For more information visit:
 * https://www.amcharts.com/
 *
 * Documentation is available at:
 * https://www.amcharts.com/docs/v4/
 * ---------------------------------------
 */

// Create map instance
var chart = am4core.create("chartdiv", am4maps.MapChart);

// Set map definition
chart.geodata = am4geodata_bulgariaHigh;

// Set projection
chart.projection = new am4maps.projections.Miller();

// Create map polygon series
var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

// Make map load polygon (like country names) data from GeoJSON
polygonSeries.useGeodata = true;

// Configure series
var polygonTemplate = polygonSeries.mapPolygons.template;
polygonTemplate.tooltipText = "{name}";
polygonTemplate.fill = am4core.color("#74B266");

// Create hover state and set alternative fill color
var hs = polygonTemplate.states.create("hover");
hs.properties.fill = am4core.color("#367B25");

// Remove Antarctica
polygonSeries.exclude = ["AQ"];

// Add some data
/*polygonSeries.data = [{
	"id": "US",
	"name": "United States",
	"value": 100,
	"fill": am4core.color("#F05C5C")
}, {
	"id": "FR",
	"name": "France",
	"value": 50,
	"fill": am4core.color("#5C5CFF")
}];*/

// Bind "fill" property to "fill" key in data
polygonTemplate.propertyFields.fill = "fill";

// Create image series
var imageSeries = chart.series.push(new am4maps.MapImageSeries());

// Create a circle image in image series template so it gets replicated to all new images
var imageSeriesTemplate = imageSeries.mapImages.template;
var circle = imageSeriesTemplate.createChild(am4core.Circle);
circle.radius = 4;
circle.fill = am4core.color("#B27799");
circle.stroke = am4core.color("#FFFFFF");
circle.strokeWidth = 2;
circle.nonScaling = true;
circle.tooltipText = "{title}";

// Set property fields
imageSeriesTemplate.propertyFields.latitude = "latitude";
imageSeriesTemplate.propertyFields.longitude = "longitude";

// Add data for the three cities
/*imageSeries.data = [
		{
			"latitude": 42.697708,
			"longitude": 23.321867,
			"title": "София",
			"url": "https://www.google.bg/"
		}, {
			"latitude": 42.135406,
			"longitude": 24.745291,
			"title": "Пловдив"
		}, {
			"latitude": 42.497681,
			"longitude": 27.470030,
			"title": "Бургас"
		}];*/

imageSeries.dataSource = "towns-test.json";

imageSeries.mapImages.template.propertyFields.latitude = "latitude";
imageSeries.mapImages.template.propertyFields.longitude = "longitude";
imageSeries.mapImages.template.propertyFields.title = "title";
imageSeries.mapImages.template.propertyFields.url = "url";
