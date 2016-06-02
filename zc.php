<?php
class ZC {
    private $mysqli;

    private $chartId = "";
    private $chartType;
    private $theme;
    private $width;
    private $height;
    private $config;

    private $data;
    private $fieldNames = array();

    public function ZC($id="myChart", $cType="area", $theme="light", $width="100%", $height=400) {
	    	$this->chartId = $id;
	    	$this->chartType = $cType;
	    	$this->width = $width;
	    	$this->height = $height;
	    	$this->theme = $theme;

	    	// Setting the chart type, this is not a top level function like, width,height,theme, and id
	    	$this->config['type'] = $cType;

    		// Defaulting to dynamic margins
        $this->config['plotarea']['margin'] = 'dynamic';

        // Defaulting to crosshairs enabled
        $this->enableCrosshairX();

        // Defaulting to tooltips disabled
        $this->disableTooltip();
    }

    // ###################################### LEVEL 1 FUNCTIONS ######################################
    public function render() {
        $id = $this->chartId;
        $width = $this->width;
        $height = $this->height;
        $theme = $this->theme;

        $jsonConfig = json_encode($this->config);
        $gold = <<< EOT
<script>
  zingchart.render(
      {
          id: "$id",
          theme: "$theme",
          width: "$width",
          height: "$height",
          data: $jsonConfig
      }
  );
</script>
EOT;
        echo $gold;
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
            				if ($count == 0) {}
            				else {
            						array_push($this->fieldNames, $f->name);
            				}
            				$count++;
            		}
            }
            else {
            		foreach ($info as $f) {
                		array_push($this->fieldNames, $f->name);
            		}
            }

            $result->close();
            $result = $this->mysqli->query($query);

            if ($scaleXFlag) {
                for ($i = 1; $i < $columns; $i++) {
                    array_push($seriesData, array());
                }
            }
            else {
                for ($i = 0; $i < $columns; $i++) {
                    array_push($seriesData, array());
                }
            }

            while($row = $result->fetch_array(MYSQLI_NUM)) {
                for ($j = 0; $j < $columns; $j++) {
                    if ($scaleXFlag) {
                        if ($j == 0) {
                            array_push($xData, $row[0]);
                        }
                        else {
                            array_push($seriesData[$j-1], $row[$j]*1);
                        }
                    }
                    else {
                        array_push($seriesData[$j], $row[$j]*1);
                    }
                }
            }

            $result->close();

            //$response = array($xData,$seriesData);
            $this->data = $seriesData;//$response;

            // Defaulting to set X and Y axis titles according to data retreived from MySQL database
            $this->autoAxisTitles($scaleXFlag, $xData);

            return $seriesData;
        }
        return "<h1>Invalid Query</h1>";
    }

    public function getFieldNames() {
    		return $this->fieldNames;
    }

    public function setTitle($title) {
        $this->config['title']['text'] = $title;
        $this->config['title']['adjust-layout'] = true;

        // defaulting to dynamic margin-top 0% if not previously specified. It just looks better this way.
        $this->config['plotarea']['margin-top'] = array_key_exists('margin-top', $this->config['plotarea']) ? $this->config['plotarea']['margin-top'] : '0%';
    }
    public function setSubtitle($subtitle) {
        $this->config['subtitle']['text'] = $subtitle;
        $this->config['subtitle']['adjust-layout'] = true;
    }
    public function setLegendTitle($title) {
        $this->config['legend']['header']['text'] = $title;
    }
    public function setScaleXTitle($title) {
        $this->config['scale-x']['label']['text'] = $title;
    }
    public function setScaleYTitle($title) {
        $this->config['scale-y']['label']['text'] = $title;
    }
    public function setScaleXLabels($labelsArray) {
        $this->config['scale-x']['labels'] = $labelsArray;
    }
		public function setScaleYLabels($yAxisRange) { // "0:100:5"
        $this->config['scale-y']['values'] = "$yAxisRange";
    }
		public function setSeriesData() {//$index, $theData) {
        $args = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            array_push($args, func_get_arg($i));
        }
        if (func_num_args() == 1) {
            //$this->data['series'][$index]['values'] = $args[1];
            for ($j = 0; $j < count($args[0]); $j++) {
                $this->config['series'][$j]['values'] = $args[0][$j];
            }
        }
        else if (func_num_args() == 2) {
            $this->config['series'][$args[0]]['values'] = $args[1];
        }
        else {
            echo "<br><h1>Invalid number of arguments: " . func_num_args() . "</h1>";
        }
    }
    public function setSeriesText() {
        $args = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            array_push($args, func_get_arg($i));
        }
        if (count($args) == 1) {
            for($i = 0; $i < count($args[0]); $i++) {
                $this->config['series'][$i]['text'] = $args[0][$i];
            }
        }
        else if (count($args) == 2) {
            $this->config['series'][$args[0]]['text'] = $args[1];
        }
        else {
            echo "<br><h1>Invalid number of arguments: " . count($args) . "</h1>";
        }
    }

    public function setChartType($type) {
        $this->chartType = $type;
        $this->config['type'] = $type;
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

    public function enableScaleXZooming() {
    		$this->config['scale-x']['zooming'] = 'true';
  	}
  	public function enableScaleYZooming() {
    		$this->config['scale-y']['zooming'] = 'true';
    }
    public function enableCrosshairX() {
				$this->config['crosshair-x'] = array("visible" => "true");
		}
		public function enableCrosshairY() {
    		$this->config['crosshair-y'] = array();
    }
    public function enableTooltip() {
        $this->config['plot']['tooltip']['text'] = "%t, %v";
        $this->config['plot']['tooltip']['visible'] = true;
    }
    public function enableValueBox() {
    		$this->config['plot']['value-box']['text'] = "%t, %v";
    }
    public function enablePreview() {
        $this->config['preview'] = array();
        $this->config['preview']['adjust-layout'] = true;
    }


    public function disableScaleXZooming() {
    		$this->config['scale-x']['zooming'] = 'false';
    }
    public function disableScaleYZooming() {
    		$this->config['scale-y']['zooming'] = 'false';
    }
    public function disableCrosshairX() {
    		$this->config['crosshair-x']['visible'] = false;
    }
    public function disableCrosshairY() {
    		//$this->config['crosshair-y']['visible'] = 'false';
        $newConfig = array();
        foreach($this->config as $x => $x_value) {
            if ($x == 'crosshair-y') {}// skip over this. Do not add to newConfig
            else $newConfig[$x] = $x_value;
        }
        $this->config = $newConfig;
    }
    public function disableTooltip() {
    		$this->config['plot']['tooltip']['visible'] = 'false';
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
        $newConfig = array();

        foreach($this->config as $x => $x_value) {
            if ($x == 'preview') {}// skip over this. Do not add to newConfig
            else $newConfig[$x] = $x_value;
        }
        $this->config = $newConfig;
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
            if ($key == 'values') {
                array_push($seriesValues, $key_val);
            }
        }
        return $seriesValues;
    }
    public function getSeriesText() {
        $seriesText = array();
        foreach($this->config['series'] as $key => $key_val) {
            if ($key == 'text') {
                array_push($seriesText, $key_val);
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
            $index = (substr($chain[0], $indexStart+1, ($indexEnd-$indexStart)+1))*1;
            $parentKey = substr($chain[0], 0, $indexStart);
            if (count($chain[1])) {
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
    private function autoAxisTitles($scaleXFlag=false, $xLabels=array()) {
    		if ($scaleXFlag) {
    				$this->config['scale-x']['label']['text'] = $this->fieldNames[0];
    				$this->config['scale-y']['label']['text'] = $this->fieldNames[1];
    				$this->config['scale-x']['labels'] = $xLabels;
    		}
        else {
        		$this->config['scale-y']['label']['text'] = $this->fieldNames[0];
        }
    }

    /**
     * Process the array with tail recursion.
     */
    private function buildArray($propertyChain, $value) {
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