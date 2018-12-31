<?php

namespace ZingChart\PHPWrapper;

class ZC {
    private $mysqli;

    protected $chartId = "";
    protected $chartType;
    protected $theme;
    protected $width;
    protected $height;
    protected $fullscreen = false;
    protected $config;
    protected $data;
    protected $fieldNames = array();
    protected $xAxisTitle = "";

    public function __construct($id, $cType = 'area', $theme = 'light', $width = '100%', $height = '400') {
        $this->chartId =   $id;
        $this->chartType = $cType;
        $this->theme =     $theme;
        $this->width =     $width;
        $this->height =    $height;

        // Setting the chart type, this is not a top level function like width, height, theme, and id
        $this->config['type'] = $this->chartType;

        // Defaulting to dynamic margins
        $this->config['plotarea']['margin'] = 'dynamic';

        // Defaulting to crosshairs enabled
        $this->enableCrosshairX();

        // Defaulting to tooltips disabled
        $this->disableTooltip();
    }

    // ###################################### LEVEL 1 FUNCTIONS ######################################
    public function render() {
        echo $this->getRenderScript();
    }

    public function getRenderScript() {
        $id = $this->chartId;
        $width = $this->width;
        $height = $this->height;
        $theme = $this->theme;
        $fullscreen = $this->fullscreen ? 'true' : 'false';
        $jsonConfig = json_encode($this->config);
        $gold = <<< EOT
<script>
  zingchart.render(
      {
          id: "$id",
          theme: "$theme",
          width: "$width",
          height: "$height",
          fullscreen: $fullscreen,
          data: $jsonConfig
      }
  );
</script>
EOT;
        return $gold;
    }

    public function connect($host, $port, $username, $password, $dbName) {
        $this->mysqli = new mysqli($host, $username, $password, $dbName, $port);
        if ($this->mysqli->connect_error) {
            die('Connect Error (' . $this->mysqli->connect_errno . ')' . $this->mysqli->connect_error);
        }
    }

    public function closeConnection() {
        $this->mysqli->close();
    }

    /**
     **   This function expects the sql query to return x number of fields where the first field specifies
     **   for the xAxisScale labels. All subsequent fields will be treated as new series of values.
     **   If you wish to disable the xAxisScale labeling because you do not have a field corresponding to it
     **   then pass in the $scaleXFlag as false.
     **/
    public function query($query, $scaleXFlag) {
        if ($result = $this->mysqli->query($query)) {
            $seriesData = array();
            $xData = array();

            $columns = count($result->fetch_array(MYSQLI_NUM));
            $info = mysqli_fetch_fields($result);

            if ($scaleXFlag) {
                $count = 0;
                foreach ($info as $f) {
                    if ($count == 0) {
                        $this->xAxisTitle = $f->name;
                    }
                    else {
                        $this->fieldNames[] = $f->name;
                    }
                    $count++;
                }
            }
            else {
                foreach ($info as $f) {
                    $this->fieldNames[] = $f->name;
                }
            }

            $result->close();
            $result = $this->mysqli->query($query);

            if ($scaleXFlag) {
                for ($i = 1; $i < $columns; $i++) {
                    $seriesData[] = array();
                }
            }
            else {
                for ($i = 0; $i < $columns; $i++) {
                    $seriesData[] = array();
                }
            }

            while($row = $result->fetch_array(MYSQLI_NUM)) {
                for ($j = 0; $j < $columns; $j++) {
                    if ($scaleXFlag) {
                        if ($j == 0) {
                            $xData[] = $row[0];
                        }
                        else {
                            $seriesData[$j-1][] = $row[$j]*1;
                        }
                    }
                    else {
                        $seriesData[$j][] = $row[$j]*1;
                    }
                }
            }

            $result->close();
            
            $this->data = $seriesData;//$response;

            // Defaulting to set X and Y axis titles according to data retrieved from MySQL database
            $this->autoAxisTitles($scaleXFlag, $xData);

            return $seriesData;
        }
        return "<h1>Invalid Query</h1>";
    }

    public function getFieldNames() {
        return $this->fieldNames;
    }

    public function setTitle($title) {
        $this->setConfig('title.text', $title);

        if (!array_key_exists('adjust-layout', $this->config['title'])) {
            $this->setConfig('title.adjust-layout', true);
        }

        // defaulting to dynamic margin-top 0% if not previously specified. It just looks better this way.
        if (!array_key_exists('margin-top', $this->config['plotarea'])) {
            $this->setConfig('plotarea.margin-top', '0%');
        }
    }
    public function setSubtitle($subtitle) {
        $this->setConfig('subtitle.text', $subtitle);
        if (!array_key_exists('adjust-layout', $this->config['subtitle'])) {
            $this->setConfig('subtitle.adjust-layout', true);
        }
    }
    public function setLegendTitle($title) {
        $this->setConfig('legend.header.text', $title);
    }
    public function setScaleXTitle($title) {
        $this->setConfig('scale-x.label.text', $title);
    }
    public function setScaleYTitle($title) {
        $this->setConfig('scale-y.label.text', $title);
    }
    public function setScaleXLabels($labelsArray) {
        $this->setConfig('scale-x.labels', $labelsArray);
    }
		public function setScaleYLabels($yAxisRange) { // "0:100:5"
        $this->setConfig('scale-y.values', $yAxisRange);
  	}
  	public function setSeriesData() {
        $numArgs = func_num_args();
        if ($numArgs == 1 && is_array(func_get_arg(0))) {
            for ($j = 0; $j < count(func_get_arg(0)); $j++) {
                $this->setConfig('series['.$j.'].values', func_get_arg(0)[$j]);
            }
        }
        else if ($numArgs == 2) {
            $this->setConfig('series['.func_get_arg(0).'].values', func_get_arg(1));
        }
        else {
            echo "<br><h1>Invalid number of arguments: $numArgs </h1>";
        }
    }
    public function setSeriesText() {
        $numArgs = func_num_args();
        if ($numArgs == 1 && is_array(func_get_arg(0))) {
            for($i = 0; $i < count(func_get_arg(0)); $i++) {
                $this->setConfig('series['.$i.'].text', func_get_arg(0)[$i]);
                //$this->config['series'][$i]['text'] = func_get_arg(0)[$i];
            }
        }
        else if ($numArgs == 2) {
            $this->setConfig('series['.func_get_arg(0).'].text', func_get_arg(1));
            //$this->config['series'][func_get_arg(0)]['text'] = func_get_arg(1);
        }
        else {
            echo "<br><h1>Invalid number of arguments: $numArgs </h1>";
        }
    }

    public function setChartType($type) {
        $this->chartType = $type;
        $this->setConfig('type', $type);
    }
    public function setChartWidth($width) {
        $this->width = $width;
    }
    public function setChartHeight($height) {
        $this->height = $height;
    }
    public function setChartTheme($theme) {
        $this->theme = $theme;
    }
    public function setFullscreen() {
        $this->fullscreen = !$this->fullscreen;
    }

    public function enableScaleXZooming() {
        $this->setConfig('scale-x.zooming', true);
  	}
  	public function enableScaleYZooming() {
        $this->setConfig('scale-y.zooming', true);
    }
    public function enableCrosshairX() {
        $this->setConfig('crosshair-x.visible', true);
    }
    public function enableCrosshairY() {
        $this->setConfig('crosshair-y.visible', true);
    }
    public function enableTooltip() {
        $this->setConfig('plot.tooltip.text', '%t, %v');
        $this->setConfig('plot.tooltip.visible', true);
    }
    public function enableValueBox() {
        $this->setConfig('plot.value-box.text', '%t, %v');
    }
    public function enablePreview() {
        $this->setConfig('preview.adjust-layout', true);
    }


    public function disableScaleXZooming() {
        $this->setConfig('scale-x.zooming', false);
    }
    public function disableScaleYZooming() {
        $this->setConfig('scale-y.zooming', false);
    }
    public function disableCrosshairX() {
        $this->setConfig('crosshair-x.visible', false);
    }
    public function disableCrosshairY() {
        $this->setConfig('crosshair-y.visible', false);
    }
    public function disableTooltip() {
        $this->setConfig('plot.tooltip.visible', false);
    }
    public function disableValueBox() {
        $newConfig = array();
        foreach($this->config as $x => $x_value) {
            if ($x == 'plot') {
                foreach($this->config['plot'] as $plot => $plot_value) {
                    if ($plot == 'value-box') {}
                    else $newConfig['plot'][$plot] = $plot_value;
                }
            }
            else $newConfig[$x] = $x_value;
        }
        $this->config = $newConfig;
    }
    public function disablePreview() {
        $this->setConfig('preview.visible', false);
    }
    

    public function getTitle() {
        return $this->config['title']['text'];
    }
    public function getSubTitle() {
        return $this->config['subtitle']['text'];
    }
    public function getLegendTitle() {
        return $this->config['legend']['header']['text'];
    }
    public function getConfig() {
        return $this->config;
    }
    public function getScaleXTitle() {
        return $this->config['scale-x']['label']['text'];
    }
    public function getScaleYTitle() {
        return $this->config['scale-y']['label']['text'];
    }
    public function getScaleXLabels() {
        return $this->config['scale-x']['labels'];
    }
    public function getScaleYLabels() {
        return $this->config['scale-y']['labels'];
    }
    public function getSeriesData() {
        $seriesValues = array();
        foreach($this->config['series'] as $key => $key_val) {
            if (array_key_exists('values', $key_val)) {
                array_push($seriesValues, $key_val['values']);
            }
        }
        return $seriesValues;
    }
    public function getSeriesText() {
        $seriesText = array();
        foreach($this->config['series'] as $key => $key_val) {
            if (isset($key_val['text'])) {
                array_push($seriesText, $key_val['text']);
            }
        }
        return $seriesText;
    }

    // ###################################### LEVEL 2 FUNCTION ######################################
    public function setConfig($keyChain, $val) {
        $chain = explode(".", $keyChain);
        $indexStart = strpos($chain[0], "[");
        $indexEnd = strpos($chain[0], "]");

        if ($indexStart > -1) {
            $index = (int) substr($chain[0], $indexStart+1, ($indexEnd-$indexStart)-1);
            $parentKey = substr($chain[0], 0, $indexStart);
            if (strlen($chain[1])) {
                $this->config[$parentKey][$index][$chain[1]] = $val;
            }
        }
        else {
            $this->config = array_replace_recursive($this->config, $this->buildArray($chain, $val));
        }
    }

    // ###################################### LEVEL 3 FUNCTION ######################################
    public function trapdoor($json) {
        $this->config = is_array($this->config) ? $this->config : array();
        $this->config = array_replace($this->config, json_decode($json, true));
    }


    // ###################################### HELPER FUNCTIONS ######################################
    protected function autoAxisTitles($scaleXFlag=false, $xLabels=array()) {
        if ($scaleXFlag) {
            $this->setConfig('scale-x.label.text', $this->xAxisTitle);
            $this->setConfig('scale-y.label.text', $this->fieldNames[0]);
            $this->setConfig('scale-x.labels', $xLabels);
            for ($i = 0; $i < count($this->fieldNames); $i++) {
            	$this->setConfig('series['.$i.'].text', $this->fieldNames[$i]);
            }
        }
        else {
            $this->setConfig('scale-y.label.text', $this->fieldNames[0]);
            for ($j = 0; $j < count($this->fieldNames); $j++) {
            	$this->setConfig('series['.$j.'].text', $this->fieldNames[$j]);
            }
        }
    }

    /**
     * Process the array with tail recursion.
     */
    protected function buildArray($propertyChain, $value) {
        $key = array_shift($propertyChain);

        // Base case, build the bottom level array
        if (empty($propertyChain)) {
            return array($key => $value);
        }

        // Wrap the next level in this level
        return array($key => $this->buildArray($propertyChain, $value));
    }
}
?>
