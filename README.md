# PHP Wrapper for ZingChart

The purpose of this wrapper is to enable PHP users to quickly and easily create interactive charts using nothing but PHP code. **No JavaScript required.**

## Initial Setup

### Manual

1. Download this repo by clicking on the green "Clone or download" button near the top of this page.
1. Click on "Download Zip" option to begin the download.
1. Copy file "ZC.php" into your project's source directory.
1. Include the ZingChart library in your project `<script src="//cdn.zingchart.com/zingchart.min.js"></script>`.
1. Include this wrapper in your project using the 'include' syntax. ie) `include ZC.php`.
1. After the include use the proper namespace in your code: `use ZingChart\PHPWrapper\ZC;`.

```php
include 'ZC.php';
use ZingChart\PHPWrapper\ZC;

$datay = array();
$a = 6.619;
$b = 0.113;
$index = 0;
for ($x = 50; $x < 600; $x += 50, $index++) {
    array_push($datay, $a + $b*$x);
}

$zc = new ZC("myChart");
$zc->setChartType("line");
$zc->setTitle("PHP 5.6 render");
$zc->setSeriesData(0, $datay);
$zc->setChartHeight("400px");
$zc->setChartWidth("100%");
$zc->render();
```

### Composer
We are on Packagist [here](https://packagist.org/packages/zingchart/php_wrapper).

1. Download this package with Composer: `composer require zingchart/php_wrapper`.
1. AutoLoad your package dependencies in your project: `require __DIR__ . '/vendor/autoload.php;`.
1. Reference this library in your code: `use ZingChart\PHPWrapper\ZC;`.


## Quick Start

### Pulling plot data from MySQL:

```php
$zc = new ZC("myChart");
$zc->connect($host,$port,$uName,$pswd,$db);
$data = $zc->query($mySqlQuery, true);
$zc->setSeriesData($data[1]);
$zc->render();
$zc->closeConnection();
```

**Code Breakdown:**

1. `$zc = new ZC("myChart");`  
Instantiate a new instance of the ZC object with the id of the html element you wish to embed your chart in. For example, `<div id="myChart"></div>`.
1. `$zc->connect($host,$port,$uname,$pswd,$db);`  
Establish a connection to your db.
1. `$data = $zc->query($mySqlQuery, $scaleXLabelsFlag);`  
Query the db with a valid SQL query string and set returned value to a variable.  
***Note:*** *The second argument to this function accepts a boolean that tells this function whether the first field returned from the SQL query should be treated as the scaleXLabels.*
1. `$zc->setSeriesData($data[1]);`  
Set the plot data with the data stored from your sql query, ie. $data[1] is the plot data and  $data[0] in this case holds the x-axis scale labels. This is because we set the $scaleXLabelsFlag to 'true' so our first dataset in our array will hold these labels in an array at index 0. And the plot data is stored in another array at index 1.
1. `$zc->render();`  
Render your data as the default 'Area' chart type. If you wish to use a different chart type then simply set it either using another function call `$zc->setChartType("bar");` or via overloading the constructor method `$zc = new ZC("myChart", "bar");`.
1. `$zc->closeConnection();`  
Be sure to close your database connection once you are done using it.

### Plotting data without a MySQL database:

```php
$zc = new ZC("myChart");
$zc->setSeriesData([1,4,2,6,3]);
$zc->render();
```

**Code Breakdown:**

1. `$zc = new ZC("myChart");`  
Instantiate a new instance of the ZC object with the id of the html elemet you wish to embed your chart in. For example, `<div id="myChart"></div>`.
1. `$zc->setSeriesData(0, [1,4,2,6,3]);`  
Set the plot data with an array of values. You can even use an array of arrays to plot multiple series like this: `$zc->setSeriesData([[12,35,24],[3,9,7]);`
1. `$zc->render();`  
Render your data as the default 'Area' chart type. If you wish to use a different chart type then simply set it either using another function call `$zc->setChartType("bar");` or via overloading the constructor `$zc = new ZC("myChart", "bar");`.

### Live Demos
* [PHP Wrapper](https://examples.zingchart.com/dashboards/php-wrapper)
    * [or just the code](https://gist.github.com/jbogartPint/884d092e2e4037c76255ac0610afb74a)
* [Dashboard](https://examples.zingchart.com/dashboards/php)


## Usage

Constructor:  
**[ZC](#ZC)** - The constructor for ZingChart. Overloading is possible.

There are three levels of usability for this wrapper: 

* **Level 1** - Built-in functions that set and get chart data. 
***Limited*** *library functionality.*

	**[render](#render)** - Renders the chart. This method must be called last, after you have optionally configured your chart.  
	
	**[connect](#connect)** - Establishes a connection to your MySQL database.  
	**[closeConnection](#closeConnection)** - Closes the connection to your MySQL database.  
	**[query](#query)** - Queries your database and returns your data in the form of an array.  
	**[getFieldNames](#getFieldNames)** - Get the MySQL field names returned from the function query above.  
	
	**[setTitle](#setTitle)**           - Sets the chart title. Expected arg: String.  
	**[setSubtitle](#setSubtitle)**        - Sets the chart subtitle. Expected arg: String.  
	**[setLegendTitle](#setLegendTitle)**     - Sets the chart legend's title. Expected arg: String.  
	**[setScaleXTitle](#setScaleXTitle)**     - Sets the chart x-axis title.  
	**[setScaleYTitle](#setScaleYTitle)**     - Sets the chart y-axis title.  
	**[setScaleXLabels](#setScaleXLabels)**    - Sets the chart x-axis' scale values.  
	**[setScaleYLabels](#setScaleYLabels)**    - Sets the chart y-axis' scale values.  
	**[setSeriesData](#setSeriesData)**      - Sets the chart plot data. ie) The data to be plotted.  
	**[setSeriesText](#setSeriesText)**      - Sets the chart data labels. ie) Used for tooltips, valueBox, etc..  
	
	**[setChartType](#setChartType)**       - Sets the chart type. ie) Area, Bar, Line, Pie, etc..  
	**[setChartWidth](#setChartWidth)**      - Sets the chart width. ***Note:*** *Defaults to 100%.*  
	**[setChartHeight](#setChartHeight)**     - Sets the chart height. ***Note:*** *Defaults to 400px.*  
	**[setChartTheme](#setChartTheme)**      - Sets the chart color theme. ie) light, dark, classic.  
	**[setFullscreen](#setFullscreen)**	    - Sets the chart's width and height to fit the window.  
	
	**[enableScaleXZooming](#enableScaleXZooming)**  - Turn on chart zooming on x-axis.  
	**[enableScaleYZooming](#enableScaleYZooming)**  - Turn on chart zooming on y-axis.  
	**[enableCrosshairX](#enableCrosshairX)**     - Turn on chart crosshair guide on x-axis. ***NOTE:*** *On by default.*  
	**[enableCrosshairY](#enableCrosshairY)**     - Turn on chart crosshair guide on y-axis.  
	**[enableTooltip](#enableTooltip)**        - Turn on chart tooltip.  
	**[enableValueBox](#enableValueBox)**       - Turn on chart valueBox.  
	**[enablePreview](#enablePreview)**        - Turn on chart preview area.  

	**[disableScaleXZooming](#disableScaleXZooming)** - Turn off chart zooming on x-axis.  
	**[disableScaleYZooming](#disableScaleYZooming)** - Turn off chart zooming on y-axis.  
	**[disableCrosshairX](#disableCrosshairX)**    - Turn off chart crosshair on x-axis.  
	**[disableCrosshairY](#disableCrosshairY)**    - Turn off chart crosshair on y-axis.  
	**[disableTooltip](#disableTooltip)**       - Turn off chart tooltip.  
	**[disableValueBox](#disableValueBox)**      - Turn off chart valueBox.  
	**[disablePreview](#disablePreview)**       - Turn off chart preview area.  
	
	**[getTitle](#getTitle)**             - Get the chart title.  
	**[getSubtitle](#getSubtitle)**          - Get the chart subtitle.  
	**[getLegendTitle](#getLegendTitle)**       - Get the chart legend title.  
	**[getConfig](#getConfig)**            - Get the chart JSON configuration.  
	**[getScaleXTitle](#getScaleXTitle)**       - Get the chart x-axis title.  
	**[getScaleYTitle](#getScaleYTitle)**       - Get the chart y-axis title.  
	**[getScaleXLabels](#getScaleXLabels)**      - Get the chart x-axis scale values.  
	**[getScaleYLabels](#getScaleYLabels)**      - Get the cahrt y-axis scale values.  
	**[getSeriesData](#getSeriesData)**        - Get the chart plot data/values.  
	**[getSeriesText](#getSeriesText)**        - Get the chart plot data labels/texts.  

* **Level 2** - Using dot syntax to set more in-depth chart data. ***No limitation*** *on original library functionality.*

	**[setConfig](#setConfig)** - This function allows you to set a value for a single chart property using dot-notation.

* **Level 3** - Full-blown JSON syntax to have full control. ***No limitation*** *on original library functionality.*

	**[trapdoor](#trapdoor)** - This function allows you to set the entire chart's configuration with a single function call.

## Documentation

<a id="ZC"></a>
### ZC ( elemId [,chartType="area" [,theme="light" [,width="100%" [,height="400"]]]] ) `Contructor`

**Default Behavior:**
This method is overloaded to accept: 1, 2, 3, 4, or 5 arguments. Argument order matters. See examples below.

**Examples:**

```php
$zc = new ZC("chartDiv");
$zc = new ZC("chartDiv", "line");
$zc = new ZC("chartDiv", "line", "dark");
$zc = new ZC("chartDiv", "line", "dark", "600");
$zc = new ZC("chartDiv", "line", "dark", "600", "400");
$zc = new ZC("chartDiv", null, "dark");
$zc = new ZC("chartDiv", "bar", null, 600, 400);
```

*NOTE: The first argument is required to render your chart properly. This first argument corresponds to the id of the html element you wish to put your chart into.*

---
<a id="render"></a>
### render ( ) `Level 1`
Renders the chart to the html element specified from the constructor.

**Example:**

```php
$zc->render();
```

---
<a id="getRenderScript"></a>
### getRenderScript ( ) `Level 1`
This function returns the text that would be printed by the render function.

**Example:**

```php
$chart1Script = $zc->getRenderScript();// This stores the script, to be printed later
echo $chart1Script;// This will render the chart
```

---
<a id="connect"></a>
### connect ( host, port, username, pswd, db ) `Level 1`

Establishes a connection to your MySQL database.

**Example:**

```php
$zc->connect("127.0.0.1","8889","root","root","mydb");
```

---
<a id="closeConnection"></a>
### closeConnection ( ) `Level 1`
Closes the connection to your MySQL database.

**Example:**

```php
$zc->closeConnection();
```

---
<a id="query"></a>
### query ( sqlQuery, scaleXLabelsFlag ) `Level 1`
Queries your MySQL database with your supplied query string. ***Note:*** *Accepts a second argument that expects a boolean representing whether or not to treat the first field returned from your SQL query as the x-axis scale labels.*

**Example:**

```php
$queryStr = "SELECT timestamp, unitsSold, expected, anotherMetric FROM feed_data";
$zc->query($queryStr, true);
```

In the code snippet above, we set the scaleXLabelsFlag to true because our SQL query returns 'timestamps' data that we wish to set as our x-axis scale labels. If we did not want to explicitly set the x-axis labels then the code could look like this:

```php
$queryStr = "SELECT unitsSold, expected, anotherMetric FROM feed_data";
$zc->query($queryStr, false);
```

---
<a id="getFieldNames"></a>
### getFieldNames ( ) `Level 1`
Get the MySQL field names returned from the function query above.

**Example:**

```php
$fieldNames = $zc->getFieldNames();
```

---
<a id="setTitle"></a>
### setTitle ( theChartTitle ) `Level 1`
Sets the chart title. Expected arg: String.

**Example:**

```php
$zc->setTitle("Sandwiches Consumed");
```

---
<a id="setSubtitle"></a>
### setSubtitle ( theSubtitle ) `Level 1`
Sets the chart subtitle. Expected arg: String.

**Example:**

```php
$zc->setSubtitle("March 1 thru March 31, 2016");
```

---
<a id="setLegendTitle"></a>
### setLegendTitle ( theLegendTitle ) `Level 1`
Sets the chart legend's title. Expected arg: String.

**Example:**

```php
$zc->setLegendTitle("Sandwich Types");
```

---
<a id="setScaleXTitle"></a>
### setScaleXTitle ( xAxisTitle ) `Level 1`
Sets the chart x-axis title.

**Example:**

```php
$zc->setScaleXTitle("Quantity");
```

---
<a id="setScaleYTitle"></a>
### setScaleYTitle ( yAxisTitle ) `Level 1`
Sets the chart y-axis title.

**Example:**

```php
$zc->setScaleYTitle("Date");
```

---
<a id="setScaleXLabels"></a>
### setScaleXLabels ( xAxisLabels ) `Level 1`
Sets the chart x-axis' scale values.

**Example:**

```php
$zc->setScaleXLabels(array("Mar 1", "Mar 2", "Mar 3));
```

---
<a id="setScaleYLabels"></a>
### setScaleYLabels ( yAxisValueRange ) `Level 1`
Sets the chart y-axis' scale value range and increment. 

**Example:**

```php
$zc->setScaleYLabels(array("0:10:100");
```

---
<a id="setSeriesData"></a>
### setSeriesData ( [,seriesIndex], plotDataArray ) ```Level 1```
Sets the chart plot data. ie) The data to be plotted.
seriesIndex is an optional parameter. If ommited, the function will assume plotDataArray contain 
all the data for all the series.

**Examples:**

```php
$zc->setSeriesData(0, [5,7,11]);
$zc->setSeriesData([[3,7,1], [20,32,37], [1,25,48]]);
```

---
<a id="setSeriesText"></a>
### setSeriesText ( [,seriesIndex], seriesText ) `Level 1`
Sets the chart data labels. ie) Used for tooltips, valueBox, etc..
There are two ways to overload this function.

1.	One argument - ([]) : an array of values. Will apply each element of this array to 
	the corresponding series text.
2.	Two arguments - (i, "value") : i is the index of the series to apply "value" towards.

**Examples:**

```php
$zc->setSeriesText(["BLT","Tuna","Club"]); // applies "BLT" to series[0], "Tuna" to series[1],..
$zc->setSeriesText(0, "BLT");              // applies "BLT" to series[0]
```

---
<a id="setChartType"></a>
### setChartType ( theType ) `Level 1`
Sets the chart type. ie) Area, Bar, Line, Pie, etc..

**Example:**

```php
$zc->setChartType("line");
```

---
<a id="setChartWidth"></a>
### setChartWidth ( chartWidth ) `Level 1`
Sets the chart width. ***Note:*** *Defaults to 100%.*

**Examples:**

```php
$zc->setChartWidth("600"); // in pixels
$zc->setChartWidth("100%");
```

---
<a id="setChartHeight"></a>
### setChartHeight ( chartHeight ) `Level 1`
Sets the chart height. ***Note:*** *Defaults to 400px.*

**Examples:**

```php
$zc->setChartHeight("400"); // in pixels
$zc->setChartHeight("50%");
```

---
<a id="setChartTheme"></a>
### setChartTheme ( chartTheme ) `Level 1`
Sets the chart color theme. ie) light, dark, classic

**Limited Options:**
`"light" | "dark" | "classic"`

**Example:**

```php
$zc->setChartTheme("dark");
```

---
<a id="setFullscreen"></a>
### setFullscreen ( ) `Level 1`
Toggles the chart to fit the window. Calling this method twice will disable fullscreen.

**Example:**

```php
$zc->setFullscreen();
```

---

<a id="enableScaleXZooming"></a>
### enableScaleXZooming ( ) `Level 1`
Turn on chart zooming on x-axis.

**Example:**

```php
$zc->enableScaleXZooming();
```

---
<a id="enableScaleYZooming"></a>
### enableScaleYZooming ( ) `Level 1`
Turn on chart zooming on y-axis.

**Example:**

```php
$zc->enableScaleYZooming();
```

---
<a id="enableCrosshairX"></a>
### enableCrosshairX ( ) `Level 1`
Turn on chart crosshair guide on x-axis. ***NOTE:*** *On by default.*

**Example:**

```php
$zc->enableCrosshairX();
```

---
<a id="enableCrosshairY"></a>
### enableCrosshairY ( ) `Level 1`
Turn on chart crosshair guide on y-axis.

**Example:**

```php
$zc->enableCrosshairY();
```

---
<a id="enableTooltip"></a>
### enableTooltip ( ) `Level 1`
Turn on chart tooltip.

**Example:**

```php
$zc->enableTooltip();
```

---
<a id="enableValueBox"></a>
### enableValueBox ( ) `Level 1`
Turn on chart valueBox.

**Example:**

```php
$zc->enableValueBox();
```

---
<a id="enablePreview"></a>
### enablePreview ( ) `Level 1`
Turn on chart preview area.

**Example:**

```php
$zc->enablePreview();
```

---
<a id="disableScaleXZooming"></a>
### disableScaleXZooming ( ) `Level 1`
Turn off chart zooming on x-axis.

**Example:**

```php
$zc->disableScaleXZooming();
```

---
<a id="disableScaleYZooming"></a>
### disableScaleYZooming ( ) `Level 1`
Turn off chart zooming on y-axis.

**Example:**

```php
$zc->disableScaleYZooming();
```

---
<a id="disableCrosshairX"></a>
### disableCrosshairX ( ) `Level 1`
Turn off chart crosshair on x-axis.

**Example:**

```php
$zc->disableCrosshairX();
```
---
<a id="disableCrosshairY"></a>
### disableCrosshairY ( ) `Level 1`
Turn off chart crosshair on y-axis.

**Example:**

```php
$zc->disableCrosshairY();
```

---
<a id="disableTooltip"></a>
### disableTooltip ( ) `Level 1`
Turn off chart tooltip.

**Example:**

```php
$zc->disableTooltip();
```

---
<a id="disableValueBox"></a>
### disableValueBox ( ) `Level 1`
Turn off chart valueBox.

**Example:**

```php
$zc->disableValueBox();
```

---
<a id="disablePreview"></a>
### disablePreview ( ) `Level 1`
Turn off chart preview area.

**Example:**

```php
$zc->disablePreview();
```

---
<a id="getTitle"></a>
### getTitle ( ) `Level 1`
Get the chart title.

**Example:**

```php
$chartTitle = $zc->getTitle();
```

---
<a id="getSubtitle"></a>
### getSubtitle ( ) `Level 1`
Get the chart subtitle.

**Example:**

```php
$chartSubtitle = $zc->getSubtitle();
```

---
<a id="getLegendTitle"></a>
### getLegendTitle ( ) `Level 1`
Get the chart legend title.

**Example:**

```php
$legendTitle = $zc->getLegendTitle();
```

---
<a id="getConfig"></a>
### getConfig ( ) `Level 1`
Get the chart JSON configuration.

**Example:**

```php
$config = $zc->getConfig();
```

---
<a id="getScaleXTitle"></a>
### getScaleXTitle ( ) `Level 1`
Get the chart x-axis title.

**Example:**

```php
$xAxisTitle = $zc->getScaleXTitle();
```

---
<a id="getScaleYTitle"></a>
### getScaleYTitle ( ) `Level 1`
Get the chart y-axis title.

**Example:**

```php
$yAxisTitle = $zc->getScaleYTitle();
```

---
<a id="getScaleXLabels"></a>
### getScaleXLabels ( ) `Level 1`
Get the chart x-axis scale values.

**Example:**

```php
$xAxisLabels = $zc->getScaleXLabels();
```

---
<a id="getScaleYLabels"></a>
### getScaleYLabels ( ) `Level 1`
Get the cahrt y-axis scale values.

**Example:**

```php
$yAxisLabels = $zc->getScaleYLabels();
```

---
<a id="getSeriesData"></a>
### getSeriesData ( ) `Level 1`
Get the chart plot data/values.

**Example:**

```php
$plotValues = $zc->getSeriesData();
```

---
<a id="getSeriesText"></a>
### getSeriesText ( ) `Level 1`
Get the chart plot data labels/texts.

**Example:**

```php
$plotSeriesText = $zc->getSeriesText();
```

---
<a id="setConfig"></a>
### setConfig ( ) `Level 2`
This is a single function that accepts a string in the form of dot-syntax. This function allows you to set a value for a single chart property.

**Examples:**

```php
$zc->setConfig("legend.header.background-color", "red");
$zc->setConfig("series[1].values", array(5,9,13,10,22,39));
```

You may also pass in an associative array to set multiple attributes from the given root property like this:

```php
$legendConfig = array(
    "header" => array(
        "background-color" => "red"
    ),
    "marker" => array(
        "border-color" => "orange",
        "border-width" => "3px",
        "shadow-angle" => "115"
    )
);

$zc->setConfig("legend", $legendConfig);
```

This syntax is a close-derivative of ZingChart's JSON syntax except that it uses dots to represent sub-object navigation.

---
<a id="trapdoor"></a>
### trapdoor ( ) `Level 3`
This is a single function that accepts a full-blown JSON string. This function allows you to set the entire chart's configuration with a single function call. This JSON string can be generated using standard PHP associative array syntax as well.

### Note: Using the trapdoor will overwrite any previously set chart configurations for that object.
For example, if you set `$zc->setLegendTitle("Cool Title")` and later use `$zc->trapdoor("series":[{"values": [1,2,3]}, {"values":[22,23,27]})` then the legend title will be destroyed.

**Examples:**

```php
$myConfig = array(
    "legend" => array(
        "header" => array(
            "background-color" => "red"
        ),
        "marker" => array(
            "border-color" => "orange",
            "border-width" => "3px",
            "shadow-angle" => "115"
        )
    ),
    "series" => array(
        array(
            "values" => array(33,45,27,32,15),
            "text"   => "Apples"
        ),
        array(
            "values" => array(1,5,9,3,7),
            "text"   => "Oranges"
        )
    )
);
$zc->trapdoor(json_encode($myConfig));
```
Or you could pass in the JSON string like this:

```php
$jsonString = <<< EOT
{
    "legend":{
        "header":{
            "background-color":"red"
        },
        "marker":{
            "border-color":"orange",
            "border-width":"3px",
            "shadow-angle":"115"
        }
    },
    "series":[
    {
        "values":[33,45,27,32,15],
        "text":"Apples"
    },
    {
        "values":[1,5,9,3,7],
        "text":"Oranges"
    }]
}
EOT;

$zc->trapdoor($jsonString);
```
One thing to note here is that if you are using the array method, then you must prepend your array with `json_encode(...)` in order to render the chart properly when you call the render method.

Finished product:  
The following three lines of code will produce an area chart that is 600x400 pixels with the light color theme rendered in the html element's id of 'myChart'.

```php
$zc = new ZC("myChart", "area", "light", 600, 400);
$zc->trapdoor(json_encode($myConfig));
$zc->render();
```

## More Documentation

You may visit our [JSON Attributes](https://www.zingchart.com/docs/json-attributes-syntax/) page for more in-depth tutorials on how to use JSON syntax with ZingChart should you need it.

## Live Demo

[Dashboard](https://examples.zingchart.com/dashboards/php)

## Issues, Contributions, or Requests

Feature(s) missing? Something broken?   
Post your questions, comments, and issues right here in this GitHub repository. We welcome any input you may have and will happily respond promptly. And feel free to fork this repo to append any additional features you like.

Thank you for reading this tutorial.  
Happy PHP Charting with ZingChart
