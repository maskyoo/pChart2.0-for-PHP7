<?php

/*
pCharts - class with charts

Version     : 0.1
Made by     : Forked by Momchil Bozhinov from the original pDraw class from Jean-Damien POGOLOTTI
Last Update : 02/12/2017

Contains functions:
	drawPolygonChart
	drawStackedAreaChart
	drawStackedBarChart
	drawBarChart
	drawAreaChart
	drawFilledStepChart
	drawStepChart
	drawZoneChart
	drawLineChart
	drawFilledSplineChart
	drawSplineChart
	drawPlotChart

This file can be distributed under the license you can find at :

http://www.pchart.net/license

You can find the whole class documentation on the pChart web site.
*/

namespace pChart;

class pCharts {
	
	var $myPicture;
	
	/* Class creator */
	function __construct($pChartObject)
	{
		if (!($pChartObject instanceof pDraw)){
			die("pBubble needs a pDraw object. Please check the examples.");
		}
		
		$this->myPicture = $pChartObject;
	}

	/* Draw a plot chart */
	function drawPlotChart(array $Format = [])
	{
		$PlotSize = NULL;
		$PlotBorder = FALSE;
		$BorderR = 50;
		$BorderG = 50;
		$BorderB = 50;
		$BorderAlpha = 30;
		$BorderSize = 2;
		$Surrounding = NULL;
		$DisplayValues = FALSE;
		$DisplayOffset = 4;
		$DisplayColor = DISPLAY_MANUAL;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$RecordImageMap = FALSE;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$SerieWeight = (isset($Serie["Weight"])) ? $Serie["Weight"] + 2 : 2;
				($PlotSize != NULL) AND 	$SerieWeight = $PlotSize;
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				if ($Surrounding != NULL) {
					$BorderR = $R + $Surrounding;
					$BorderG = $G + $Surrounding;
					$BorderB = $B + $Surrounding;
				}

				if (isset($Serie["Picture"])) {
					$Picture = $Serie["Picture"];
					$PicInfo = $this->myPicture->getPicInfo($Picture);
					list($PicWidth, $PicHeight, $PicType) = $PicInfo;
				} else {
					$Picture = NULL;
					$PicOffset = 0;
				}

				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = $R;
					$DisplayG = $G;
					$DisplayB = $B;
				}

				$AxisID = $Serie["Axis"];
				$Shape = $Serie["Shape"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$SerieDescription = (isset($Serie["Description"])) ? $Serie["Description"] : $SerieName;
				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]]);
				$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					if ($Picture != NULL) {
						$PicOffset = $PicHeight / 2;
						$SerieWeight = 0;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					foreach($PosArray as $Key => $Y) {
						if ($DisplayValues) {
							$this->myPicture->drawText(
								$X,
								$Y - $DisplayOffset - $SerieWeight - $BorderSize - $PicOffset,
								$this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit),
								array("R" => $DisplayR,	"G" => $DisplayG,"B" => $DisplayB,"Align" => TEXT_ALIGN_BOTTOMMIDDLE)
							);
						}
						if ($Y != VOID) {
							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("CIRCLE", floor($X) . "," . floor($Y) . "," . $SerieWeight, $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}

							if ($Picture != NULL) {
								$this->myPicture->drawFromPicture($PicInfo, $Picture, $X - $PicWidth / 2, $Y - $PicHeight / 2);
							} else {
								$this->myPicture->drawShape($X, $Y, $Shape, $SerieWeight, $PlotBorder, $BorderSize, $R, $G, $B, $Alpha, $BorderR, $BorderG, $BorderB, $BorderAlpha);
							}
						}

						$X = $X + $XStep;
					}
					
				} else {
					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					if ($Picture != NULL) {
						$PicOffset = $PicWidth / 2;
						$SerieWeight = 0;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					foreach($PosArray as $Key => $X) {
						if ($DisplayValues) $this->myPicture->drawText($X + $DisplayOffset + $SerieWeight + $BorderSize + $PicOffset, $Y, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), array(
							"Angle" => 270,
							"R" => $DisplayR,
							"G" => $DisplayG,
							"B" => $DisplayB,
							"Align" => TEXT_ALIGN_BOTTOMMIDDLE
						));
						if ($X != VOID) {
							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("CIRCLE", floor($X) . "," . floor($Y) . "," . $SerieWeight, $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}

							if ($Picture != NULL) {
								$this->myPicture->drawFromPicture($PicInfo, $Picture, $X - $PicWidth / 2, $Y - $PicHeight / 2);
							} else {
								$this->myPicture->drawShape($X, $Y, $Shape, $SerieWeight, $PlotBorder, $BorderSize, $R, $G, $B, $Alpha, $BorderR, $BorderG, $BorderB, $BorderAlpha);
							}
						}

						$Y = $Y + $YStep;
					}
				}
			}
		}
	}

	/* Draw a spline chart */
	function drawSplineChart($Format = [])
	{
		# Momchil: The sandbox system requires it
		$Format = $this->myPicture->convertToArray($Format);
		
		$BreakVoid = TRUE;
		$VoidTicks = 4;
		$BreakR = NULL; // 234
		$BreakG = NULL; // 55
		$BreakB = NULL; // 26
		$DisplayValues = FALSE;
		$DisplayOffset = 2;
		$DisplayColor = DISPLAY_MANUAL;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$RecordImageMap = FALSE;
		$ImageMapPlotSize = 5;
		
		/* Override defaults */
		extract($Format);
		
		$this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				$Weight = $Serie["Weight"];
				if ($BreakR == NULL) {
					$BreakSettings = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $VoidTicks];
				} else {
					$BreakSettings = ["R" => $BreakR,"G" => $BreakG,"B" => $BreakB,"Alpha" => $Alpha,"Ticks" => $VoidTicks,"Weight" => $Weight];
				}

				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = $R;
					$DisplayG = $G;
					$DisplayB = $B;
				}

				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$SerieDescription = (isset($Serie["Description"])) ? $Serie["Description"] : $SerieName;
				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]]);
				$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$WayPoints = [];
					$Force = $XStep / 5;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$LastGoodY = NULL;
					$LastGoodX = NULL;
					$LastX = 1;
					$LastY = 1;
					foreach($PosArray as $Key => $Y) {
						if ($DisplayValues) {
							$this->myPicture->drawText(
								$X,
								$Y - $DisplayOffset, 
								$this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), 
								array("R" => $DisplayR,	"G" => $DisplayG, "B" => $DisplayB,	"Align" => TEXT_ALIGN_BOTTOMMIDDLE)
							);
						}
						
						if ($RecordImageMap && $Y != VOID) {
							$this->myPicture->addToImageMap("CIRCLE", floor($X) . "," . floor($Y) . "," . $ImageMapPlotSize, $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
						}

						if ($Y == VOID && $LastY != NULL) {
							$this->myPicture->drawSpline($WayPoints, ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks,"Weight" => $Weight]);
							$WayPoints = [];
						}

						if ($Y != VOID && $LastY == NULL && $LastGoodY != NULL && !$BreakVoid) {
							$this->drawLine($LastGoodX, $LastGoodY, $X, $Y, $BreakSettings);
						}

						if ($Y != VOID) {
							$WayPoints[] = [$X,$Y];
						}
						
						if ($Y != VOID) {
							$LastGoodY = $Y;
							$LastGoodX = $X;
						}

						if ($Y == VOID) {
							$Y = NULL;
						}

						$LastX = $X;
						$LastY = $Y;
						$X = $X + $XStep;
					}

					$this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks,"Weight" => $Weight]);
					
				} else {
					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$WayPoints = [];
					$Force = $YStep / 5;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$LastGoodY = NULL;
					$LastGoodX = NULL;
					$LastX = 1;
					$LastY = 1;
					foreach($PosArray as $Key => $X) {
						if ($DisplayValues) {
							$this->myPicture->drawText($X + $DisplayOffset, $Y, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["Angle" => 270,"R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => TEXT_ALIGN_BOTTOMMIDDLE]);
						}

						if ($RecordImageMap && $X != VOID) {
							$this->myPicture->addToImageMap("CIRCLE", floor($X) . "," . floor($Y) . "," . $ImageMapPlotSize, $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
						}

						if ($X == VOID && $LastX != NULL) {
							$this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks,"Weight" => $Weight]);
							$WayPoints = [];
						}

						if ($X != VOID && $LastX == NULL && $LastGoodX != NULL && !$BreakVoid) {
							$this->myPicture->drawLine($LastGoodX, $LastGoodY, $X, $Y, $BreakSettings);
						}

						if ($X != VOID) {
							$WayPoints[] = [$X,	$Y];
						#} # Momchil 
						#if ($X != VOID) {
							$LastGoodX = $X;
							$LastGoodY = $Y;
						} else {
						#if ($X == VOID) {
							$X = NULL;
						}

						$LastX = $X;
						$LastY = $Y;
						$Y = $Y + $YStep;
					}

					$this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks,"Weight" => $Weight]);
				}
			}
		}
	}

	/* Draw a filled spline chart */
	function drawFilledSplineChart(array $Format = [])
	{
		$DisplayValues = FALSE;
		$DisplayOffset = 2;
		$DisplayColor = DISPLAY_MANUAL;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$AroundZero = TRUE;
		$Threshold = NULL;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = $R;
					$DisplayG = $G;
					$DisplayB = $B;
				}

				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]]);
				if ($AroundZero) {
					$YZero = $this->myPicture->scaleComputeY(0, ["AxisID" => $Serie["Axis"]]);
				}

				if ($Threshold != NULL) {
					foreach($Threshold as $Key => $Params) {
						$Threshold[$Key]["MinX"] = $this->myPicture->scaleComputeY($Params["Min"], ["AxisID" => $Serie["Axis"]]);
						$Threshold[$Key]["MaxX"] = $this->myPicture->scaleComputeY($Params["Max"], ["AxisID" => $Serie["Axis"]]);
					}
				}

				$this->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$WayPoints = [];
					$Force = $XStep / 5;
					if (!$AroundZero) {
						$YZero = $this->myPicture->GraphAreaY2 - 1;
					}

					if ($YZero > $this->myPicture->GraphAreaY2 - 1) {
						$YZero = $this->myPicture->GraphAreaY2 - 1;
					}

					if ($YZero < $this->myPicture->GraphAreaY1 + 1) {
						$YZero = $this->myPicture->GraphAreaY1 + 1;
					}

					// $LastX = ""; $LastY = ""; # UNUSED
					$PosArray = $this->myPicture->convertToArray($PosArray);
				
					foreach($PosArray as $Key => $Y) {
						if ($DisplayValues) {
							$this->myPicture->drawText($X, $Y - $DisplayOffset, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => TEXT_ALIGN_BOTTOMMIDDLE]);
						}

						if ($Y == VOID) {
							$Area = $this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"PathOnly" => TRUE]);
							if (count($Area) > 0) //if ( $Area != "" )
							{
								foreach($Area as $key => $Points) {
									$Corners = [$Area[$key][0]["X"], $YZero];
									foreach($Points as $subKey => $Point) {
										$Corners[] = ($subKey == count($Points) - 1) ? $Point["X"] - 1 : $Point["X"];
										$Corners[] = $Point["Y"] + 1;
									}

									$Corners[] = $Points[$subKey]["X"] - 1;
									$Corners[] = $YZero;
									$this->drawPolygonChart($Corners, ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha / 2,"NoBorder" => TRUE,"Threshold" => $Threshold]);
								}

								$this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks]);
							}

							$WayPoints = [];
						} else {
							$WayPoints[] = [$X,$Y - .5]; /* -.5 for AA visual fix */
						}

						$X = $X + $XStep;
					}

					$Area = $this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"PathOnly" => TRUE]);
					if (count($Area) > 0) //if ( $Area != "" )
					{
						foreach($Area as $key => $Points) {
							$Corners = [$Area[$key][0]["X"], $YZero];
							foreach($Points as $subKey => $Point) {
								$Corners[] = ($subKey == count($Points) - 1) ? $Point["X"] - 1 : $Point["X"];
								$Corners[] = $Point["Y"] + 1;
							}

							$Corners[] = $Points[$subKey]["X"] - 1;
							$Corners[] = $YZero;
							$this->drawPolygonChart($Corners, ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha / 2,"NoBorder" => TRUE,"Threshold" => $Threshold]);
						}

						$this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks]);
					}
				} else {
					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$WayPoints = [];
					$Force = $YStep / 5;
					if (!$AroundZero) {
						$YZero = $this->myPicture->GraphAreaX1 + 1;
					}

					if ($YZero > $this->myPicture->GraphAreaX2 - 1) {
						$YZero = $this->myPicture->GraphAreaX2 - 1;
					}

					if ($YZero < $this->myPicture->GraphAreaX1 + 1) {
						$YZero = $this->myPicture->GraphAreaX1 + 1;
					}

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					foreach($PosArray as $Key => $X) {
						if ($DisplayValues) {
							$this->myPicture->drawText(
								$X + $DisplayOffset,
								$Y,
								$this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit),
								array("Angle" => 270,"R" => $DisplayR,"G" => $DisplayG,	"B" => $DisplayB, "Align" => TEXT_ALIGN_BOTTOMMIDDLE)
							);
						}

						if ($X == VOID) {
							$Area = $this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"PathOnly" => TRUE]);
							if (count($Area) > 0) // if ( $Area != "" )
							{
								foreach($Area as $key => $Points) {
									$Corners = [$YZero,$Area[$key][0]["Y"]];
									foreach($Points as $subKey => $Point) {
										$Corners[] = ($subKey == count($Points) - 1) ? $Point["X"] - 1 : $Point["X"];
										$Corners[] = $Point["Y"];
									}

									$Corners[] = $YZero;
									$Corners[] = $Points[$subKey]["Y"] - 1;
									$this->drawPolygonChart($Corners, ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha / 2,"NoBorder" => TRUE,"Threshold" => $Threshold]);
								}

								$this->myPicture->drawSpline($WayPoints, ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha / 2,"NoBorder" => TRUE,"Threshold" => $Threshold]);
							}

							$WayPoints = [];
						} else {
							$WayPoints[] = [$X,$Y];
						}

						$Y = $Y + $YStep;
					}

					$Area = $this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"PathOnly" => TRUE]);
					if (count($Area) > 0) // if ( $Area != "" )
					{
						foreach($Area as $key => $Points) {
							$Corners = [];
							$Corners[] = $YZero;
							$Corners[] = $Area[$key][0]["Y"];
							foreach($Points as $subKey => $Point) {
								$Corners[] = ($subKey == count($Points) - 1) ? $Point["X"] - 1 : $Point["X"];
								$Corners[] = $Point["Y"];
							}

							$Corners[] = $YZero;
							$Corners[] = $Points[$subKey]["Y"] - 1;
							$this->drawPolygonChart($Corners, ["Force" => $Force,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks]);
						}

						$this->myPicture->drawSpline($WayPoints, ["Force" => $Force,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks]);
					}
				}
			}
		}
	}

	/* Draw a line chart */
	function drawLineChart(array $Format = [])
	{
		$BreakVoid = TRUE;
		$VoidTicks = 4;
		$BreakR = NULL;
		$BreakG = NULL;
		$BreakB = NULL;
		$DisplayValues = FALSE;
		$DisplayOffset = 2;
		$DisplayColor = DISPLAY_MANUAL;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$RecordImageMap = FALSE;
		$ImageMapPlotSize = 5;
		$ForceColor = FALSE;
		$ForceR = 0;
		$ForceG = 0;
		$ForceB = 0;
		$ForceAlpha = 100;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				$Weight = $Serie["Weight"];
				if ($ForceColor) {
					$R = $ForceR;
					$G = $ForceG;
					$B = $ForceB;
					$Alpha = $ForceAlpha;
				}

				if ($BreakR == NULL) {
					$BreakSettings = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $VoidTicks,"Weight" => $Weight];
				} else {
					$BreakSettings = ["R" => $BreakR,"G" => $BreakG,"B" => $BreakB,"Alpha" => $Alpha,"Ticks" => $VoidTicks,"Weight" => $Weight];
				}

				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = $R;
					$DisplayG = $G;
					$DisplayB = $B;
				}

				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$SerieDescription = (isset($Serie["Description"])) ? $Serie["Description"] : $SerieName;
				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]]);
				$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$LastX = NULL;
					$LastY = NULL;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$LastGoodY = NULL;
					$LastGoodX = NULL;
					foreach($PosArray as $Key => $Y) {
						if ($DisplayValues && $Serie["Data"][$Key] != VOID) {
							if ($Serie["Data"][$Key] > 0) {
								$Align = TEXT_ALIGN_BOTTOMMIDDLE;
								$Offset = $DisplayOffset;
							} else {
								$Align = TEXT_ALIGN_TOPMIDDLE;
								$Offset = - $DisplayOffset;
							}

							$this->myPicture->drawText($X, $Y - $Offset - $Weight, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => $Align]);
						}

						if ($RecordImageMap && $Y != VOID) {
							$this->myPicture->addToImageMap("CIRCLE", floor($X) . "," . floor($Y) . "," . $ImageMapPlotSize, $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
						}

						if ($Y != VOID && $LastX != NULL && $LastY != NULL){
							$this->myPicture->drawLine($LastX, $LastY, $X, $Y, ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks,	"Weight" => $Weight]);
						}
						
						if ($Y != VOID && $LastY == NULL && $LastGoodY != NULL && !$BreakVoid) {
							$this->myPicture->drawLine($LastGoodX, $LastGoodY, $X, $Y, $BreakSettings);
							$LastGoodY = NULL;
						}

						if ($Y != VOID) {
							$LastGoodY = $Y;
							$LastGoodX = $X;
						}

						if ($Y == VOID) {
							$Y = NULL;
						}

						$LastX = $X;
						$LastY = $Y;
						$X = $X + $XStep;
					}
					
				} else {
					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$LastX = NULL;
					$LastY = NULL;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$LastGoodY = NULL;
					$LastGoodX = NULL;
					foreach($PosArray as $Key => $X) {
						if ($DisplayValues && $Serie["Data"][$Key] != VOID) {
							$this->myPicture->drawText($X + $DisplayOffset + $Weight, $Y, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["Angle" => 270,"R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => TEXT_ALIGN_BOTTOMMIDDLE]);
						}

						if ($RecordImageMap && $X != VOID) {
							$this->myPicture->addToImageMap("CIRCLE", floor($X) . "," . floor($Y) . "," . $ImageMapPlotSize, $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
						}

						if ($X != VOID && $LastX != NULL && $LastY != NULL) $this->myPicture->drawLine($LastX, $LastY, $X, $Y, ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks,"Weight" => $Weight]);
						if ($X != VOID && $LastX == NULL && $LastGoodY != NULL && !$BreakVoid) {
							$this->myPicture->drawLine($LastGoodX, $LastGoodY, $X, $Y, $BreakSettings);
							$LastGoodY = NULL;
						}

						if ($X != VOID) {
							$LastGoodY = $Y;
							$LastGoodX = $X;
						}

						if ($X == VOID) {
							$X = NULL;
						}

						$LastX = $X;
						$LastY = $Y;
						$Y = $Y + $YStep;
					}
				}
			}
		}
	}

	/* Draw a line chart */
	function drawZoneChart($SerieA, $SerieB, array $Format = [])
	{
		$AxisID = 0;
		$LineR = 150;
		$LineG = 150;
		$LineB = 150;
		$LineAlpha = 50;
		$LineTicks = 1;
		$AreaR = 150;
		$AreaG = 150;
		$AreaB = 150;
		$AreaAlpha = 5;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		if (!isset($Data["Series"][$SerieA]["Data"]) || !isset($Data["Series"][$SerieB]["Data"])) {
			return 0;
		}

		$SerieAData = $Data["Series"][$SerieA]["Data"];
		$SerieBData = $Data["Series"][$SerieB]["Data"];
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		$Mode = $Data["Axis"][$AxisID]["Display"];
		$Format = $Data["Axis"][$AxisID]["Format"];
		$Unit = $Data["Axis"][$AxisID]["Unit"];
		$PosArrayA = $this->myPicture->scaleComputeY($SerieAData, ["AxisID" => $AxisID]);
		$PosArrayB = $this->myPicture->scaleComputeY($SerieBData, ["AxisID" => $AxisID]);
		if (count($PosArrayA) != count($PosArrayB)) {
			return 0;
		}

		if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
			if ($XDivs == 0) {
				$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
			} else {
				$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
			}

			$X = $this->myPicture->GraphAreaX1 + $XMargin;
			$LastX = NULL;
			$LastY = NULL;
			$LastX = NULL;
			$LastY1 = NULL;
			$LastY2 = NULL;
			$BoundsA = [];
			$BoundsB = [];
			foreach($PosArrayA as $Key => $Y1) {
				$Y2 = $PosArrayB[$Key];
				$BoundsA[] = $X;
				$BoundsA[] = $Y1;
				$BoundsB[] = $X;
				$BoundsB[] = $Y2;
				$LastX = $X;
				$LastY1 = $Y1;
				$LastY2 = $Y2;
				$X = $X + $XStep;
			}

			$Bounds = array_merge($BoundsA, $this->myPicture->reversePlots($BoundsB));
			$this->drawPolygonChart($Bounds, ["R" => $AreaR,"G" => $AreaG,"B" => $AreaB,"Alpha" => $AreaAlpha]);
			for ($i = 0; $i <= count($BoundsA) - 4; $i = $i + 2) {
				$this->myPicture->drawLine($BoundsA[$i], $BoundsA[$i + 1], $BoundsA[$i + 2], $BoundsA[$i + 3], ["R" => $LineR,"G" => $LineG,"B" => $LineB,"Alpha" => $LineAlpha,"Ticks" => $LineTicks]);
				$this->myPicture->drawLine($BoundsB[$i], $BoundsB[$i + 1], $BoundsB[$i + 2], $BoundsB[$i + 3], ["R" => $LineR,"G" => $LineG,"B" => $LineB,"Alpha" => $LineAlpha,"Ticks" => $LineTicks]);
			}
		} else {
			if ($XDivs == 0) {
				$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
			} else {
				$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
			}

			$Y = $this->myPicture->GraphAreaY1 + $XMargin;
			$LastX = NULL;
			$LastY = NULL;
			$LastY = NULL;
			$LastX1 = NULL;
			$LastX2 = NULL;
			$BoundsA = [];
			$BoundsB = [];
			foreach($PosArrayA as $Key => $X1) {
				$X2 = $PosArrayB[$Key];
				$BoundsA[] = $X1;
				$BoundsA[] = $Y;
				$BoundsB[] = $X2;
				$BoundsB[] = $Y;
				$LastY = $Y;
				$LastX1 = $X1;
				$LastX2 = $X2;
				$Y = $Y + $YStep;
			}

			$Bounds = array_merge($BoundsA, $this->myPicture->reversePlots($BoundsB));
			$this->drawPolygonChart($Bounds, ["R" => $AreaR,"G" => $AreaG,"B" => $AreaB,"Alpha" => $AreaAlpha]);
			for ($i = 0; $i <= count($BoundsA) - 4; $i = $i + 2) {
				$this->myPicture->drawLine($BoundsA[$i], $BoundsA[$i + 1], $BoundsA[$i + 2], $BoundsA[$i + 3], ["R" => $LineR,"G" => $LineG,"B" => $LineB,"Alpha" => $LineAlpha,"Ticks" => $LineTicks]);
				$this->myPicture->drawLine($BoundsB[$i], $BoundsB[$i + 1], $BoundsB[$i + 2], $BoundsB[$i + 3], ["R" => $LineR,"G" => $LineG,"B" => $LineB,"Alpha" => $LineAlpha,"Ticks" => $LineTicks]);
			}
		}
	}

	/* Draw a step chart */
	function drawStepChart(array $Format = [])
	{
		$BreakVoid = FALSE;
		$ReCenter = TRUE;
		$VoidTicks = 4;
		$BreakR = NULL;
		$BreakG = NULL;
		$BreakB = NULL;
		$DisplayValues = FALSE;
		$DisplayOffset = 2;
		$DisplayColor = DISPLAY_MANUAL;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$RecordImageMap = FALSE;
		$ImageMapPlotSize = 5;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				$Weight = $Serie["Weight"];
				$SerieDescription = (isset($Serie["Description"])) ? $Serie["Description"] : $SerieName;

				if ($BreakR == NULL) {
					$BreakSettings = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $VoidTicks,"Weight" => $Weight];
				} else {
					$BreakSettings = ["R" => $BreakR,"G" => $BreakG,"B" => $BreakB,"Alpha" => $Alpha,"Ticks" => $VoidTicks,"Weight" => $Weight];
				}

				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = $R;
					$DisplayG = $G;
					$DisplayB = $B;
				}

				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$Color = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks,"Weight" => $Weight];
				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]]);
				$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$LastX = NULL;
					$LastY = NULL;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$LastGoodY = NULL;
					$LastGoodX = NULL;
					$Init = FALSE;
					foreach($PosArray as $Key => $Y) {
						if ($DisplayValues && $Serie["Data"][$Key] != VOID) {
							if ($Y <= $LastY) {
								$Align = TEXT_ALIGN_BOTTOMMIDDLE;
								$Offset = $DisplayOffset;
							} else {
								$Align = TEXT_ALIGN_TOPMIDDLE;
								$Offset = - $DisplayOffset;
							}

							$this->myPicture->drawText($X, $Y - $Offset - $Weight, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => $Align]);
						}

						if ($Y != VOID && $LastX != NULL && $LastY != NULL) {
							$this->myPicture->drawLine($LastX, $LastY, $X, $LastY, $Color);
							$this->myPicture->drawLine($X, $LastY, $X, $Y, $Color);
							if ($ReCenter && $X + $XStep < $this->myPicture->GraphAreaX2 - $XMargin) {
								$this->myPicture->drawLine($X, $Y, $X + $XStep, $Y, $Color);
								if ($RecordImageMap) {
									$this->myPicture->addToImageMap("RECT", floor($X - $ImageMapPlotSize) . "," . floor($Y - $ImageMapPlotSize) . "," . floor($X + $XStep + $ImageMapPlotSize) . "," . floor($Y + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
								}
							} else {
								if ($RecordImageMap) {
									$this->myPicture->addToImageMap("RECT", floor($LastX - $ImageMapPlotSize) . "," . floor($LastY - $ImageMapPlotSize) . "," . floor($X + $ImageMapPlotSize) . "," . floor($LastY + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
								}
							}
						}

						if ($Y != VOID && $LastY == NULL && $LastGoodY != NULL && !$BreakVoid) {
							if ($ReCenter) {
								$this->myPicture->drawLine($LastGoodX + $XStep, $LastGoodY, $X, $LastGoodY, $BreakSettings);
								if ($RecordImageMap) {
									$this->myPicture->addToImageMap("RECT", floor($LastGoodX + $XStep - $ImageMapPlotSize) . "," . floor($LastGoodY - $ImageMapPlotSize) . "," . floor($X + $ImageMapPlotSize) . "," . floor($LastGoodY + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
								}
							} else {
								$this->myPicture->drawLine($LastGoodX, $LastGoodY, $X, $LastGoodY, $BreakSettings);
								if ($RecordImageMap) {
									$this->myPicture->addToImageMap("RECT", floor($LastGoodX - $ImageMapPlotSize) . "," . floor($LastGoodY - $ImageMapPlotSize) . "," . floor($X + $ImageMapPlotSize) . "," . floor($LastGoodY + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
								}
							}

							$this->myPicture->drawLine($X, $LastGoodY, $X, $Y, $BreakSettings);
							$LastGoodY = NULL;
							
						} elseif (!$BreakVoid && $LastGoodY == NULL && $Y != VOID) {
							$this->myPicture->drawLine($this->myPicture->GraphAreaX1 + $XMargin, $Y, $X, $Y, $BreakSettings);
							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("RECT", floor($this->myPicture->GraphAreaX1 + $XMargin - $ImageMapPlotSize) . "," . floor($Y - $ImageMapPlotSize) . "," . floor($X + $ImageMapPlotSize) . "," . floor($Y + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}
						}

						if ($Y != VOID) {
							$LastGoodY = $Y;
							$LastGoodX = $X;
						}

						if ($Y == VOID) {
							$Y = NULL;
						}

						if (!$Init && $ReCenter) {
							$X = $X - $XStep / 2;
							$Init = TRUE;
						}

						$LastX = $X;
						$LastY = $Y;
						if ($LastX < $this->myPicture->GraphAreaX1 + $XMargin) {
							$LastX = $this->myPicture->GraphAreaX1 + $XMargin;
						}

						$X = $X + $XStep;
					}

					if ($ReCenter) {
						$this->myPicture->drawLine($LastX, $LastY, $this->myPicture->GraphAreaX2 - $XMargin, $LastY, $Color);
						if ($RecordImageMap) {
							$this->myPicture->addToImageMap("RECT", floor($LastX - $ImageMapPlotSize) . "," . floor($LastY - $ImageMapPlotSize) . "," . floor($this->myPicture->GraphAreaX2 - $XMargin + $ImageMapPlotSize) . "," . floor($LastY + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
						}
					}
					
				} else {
					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$LastX = NULL;
					$LastY = NULL;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$LastGoodY = NULL;
					$LastGoodX = NULL;
					$Init = FALSE;
					foreach($PosArray as $Key => $X) {
						if ($DisplayValues && $Serie["Data"][$Key] != VOID) {
							if ($X >= $LastX) {
								$Align = TEXT_ALIGN_MIDDLELEFT;
								$Offset = $DisplayOffset;
							} else {
								$Align = TEXT_ALIGN_MIDDLERIGHT;
								$Offset = - $DisplayOffset;
							}

							$this->myPicture->drawText($X + $Offset + $Weight, $Y, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => $Align]);
						}

						if ($X != VOID && $LastX != NULL && $LastY != NULL) {
							$this->myPicture->drawLine($LastX, $LastY, $LastX, $Y, $Color);
							$this->myPicture->drawLine($LastX, $Y, $X, $Y, $Color);
							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("RECT", floor($LastX - $ImageMapPlotSize) . "," . floor($LastY - $ImageMapPlotSize) . "," . floor($LastX + $XStep + $ImageMapPlotSize) . "," . floor($Y + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}
						}

						if ($X != VOID && $LastX == NULL && $LastGoodY != NULL && !$BreakVoid) {
							$this->myPicture->drawLine($LastGoodX, $LastGoodY, $LastGoodX, $LastGoodY + $YStep, $Color);
							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("RECT", floor($LastGoodX - $ImageMapPlotSize) . "," . floor($LastGoodY - $ImageMapPlotSize) . "," . floor($LastGoodX + $ImageMapPlotSize) . "," . floor($LastGoodY + $YStep + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}

							$this->myPicture->drawLine($LastGoodX, $LastGoodY + $YStep, $LastGoodX, $Y, $BreakSettings);
							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("RECT", floor($LastGoodX - $ImageMapPlotSize) . "," . floor($LastGoodY + $YStep - $ImageMapPlotSize) . "," . floor($LastGoodX + $ImageMapPlotSize) . "," . floor($YStep + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}

							$this->myPicture->drawLine($LastGoodX, $Y, $X, $Y, $BreakSettings);
							$LastGoodY = NULL;
						} elseif ($X != VOID && $LastGoodY == NULL && !$BreakVoid) {
							$this->myPicture->drawLine($X, $this->myPicture->GraphAreaY1 + $XMargin, $X, $Y, $BreakSettings);
							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("RECT", floor($X - $ImageMapPlotSize) . "," . floor($this->myPicture->GraphAreaY1 + $XMargin - $ImageMapPlotSize) . "," . floor($X + $ImageMapPlotSize) . "," . floor($Y + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}
						}

						if ($X != VOID) {
							$LastGoodY = $Y;
							$LastGoodX = $X;
						}

						if ($X == VOID) {
							$X = NULL;
						}

						if (!$Init && $ReCenter) {
							$Y = $Y - $YStep / 2;
							$Init = TRUE;
						}

						$LastX = $X;
						$LastY = $Y;
						if ($LastY < $this->myPicture->GraphAreaY1 + $XMargin) {
							$LastY = $this->myPicture>GraphAreaY1 + $XMargin;
						}

						$Y = $Y + $YStep;
					}

					if ($ReCenter) {
						$this->myPicture->drawLine($LastX, $LastY, $LastX, $this->myPicture->GraphAreaY2 - $XMargin, $Color);
						if ($RecordImageMap) {
							$this->myPicture->addToImageMap("RECT", floor($LastX - $ImageMapPlotSize) . "," . floor($LastY - $ImageMapPlotSize) . "," . floor($LastX + $ImageMapPlotSize) . "," . floor($this->myPicture->GraphAreaY2 - $XMargin + $ImageMapPlotSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
						}
					}
				}
			}
		}
	}

	/* Draw a step chart */
	function drawFilledStepChart(array $Format = [])
	{
		$ReCenter = TRUE;
		$DisplayValues = FALSE;
		$DisplayOffset = 2;
		$DisplayColor = DISPLAY_MANUAL;
		$ForceTransparency = NULL;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$AroundZero = TRUE;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				$Weight = $Serie["Weight"];
				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = $R;
					$DisplayG = $G;
					$DisplayB = $B;
				}

				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$Color = ["R" => $R,"G" => $G,"B" => $B];
				$Color["Alpha"] = ($ForceTransparency != NULL) ? $ForceTransparency : $Alpha;

				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"],["AxisID" => $Serie["Axis"]]);
				$YZero = $this->myPicture->scaleComputeY(0, ["AxisID" => $Serie["Axis"]]);
				$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					if ($YZero > $this->myPicture->GraphAreaY2 - 1) {
						$YZero = $this->myPicture->GraphAreaY2 - 1;
					}

					if ($YZero < $this->myPicture->GraphAreaY1 + 1) {
						$YZero = $this->myPicture->GraphAreaY1 + 1;
					}

					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$LastX = NULL;
					$LastY = NULL;
					if (!$AroundZero) {
						$YZero = $this->myPicture->GraphAreaY2 - 1;
					}

					$PosArray = $this->myPicture->convertToArray($PosArray);

					$LastGoodY = NULL;
					$LastGoodX = NULL;
					$Points = [];
					$Init = FALSE;
					
					foreach($PosArray as $Key => $Y) {

						if ($Y == VOID && $LastX != NULL && $LastY != NULL && (count($Points) > 0)) {
							$Points[] = $LastX;
							$Points[] = $LastY;
							$Points[] = $X;
							$Points[] = $LastY;
							$Points[] = $X;
							$Points[] = $YZero;
							$this->myPicture->drawPolygon($Points, $Color);
							$Points = [];
						}

						if ($Y != VOID && $LastX != NULL && $LastY != NULL) {

							if (count($Points) == 0) {
								$Points[] = $LastX;
								$Points[] = $YZero;
							}

							$Points[] = $LastX;
							$Points[] = $LastY;
							$Points[] = $X;
							$Points[] = $LastY;
							$Points[] = $X;
							$Points[] = $Y;
						}

						if ($Y != VOID) {
							$LastGoodY = $Y;
							$LastGoodX = $X;
						} else {
							$Y = NULL;
						}

						if (!$Init && $ReCenter) {
							$X = $X - $XStep / 2;
							$Init = TRUE;
						}

						$LastX = $X;
						$LastY = $Y;
						if ($LastX < $this->myPicture->GraphAreaX1 + $XMargin) {
							$LastX = $this->myPicture->GraphAreaX1 + $XMargin;
						}

						$X = $X + $XStep;
					}

					if ($ReCenter) {
						$Points[] = $LastX+$XStep/2; $Points[] = $LastY;
						$Points[] = $LastX+$XStep/2; $Points[] = $YZero;
					} else {
						$Points[] = $LastX;
						$Points[] = $YZero;
					}

					$this->myPicture->drawPolygon($Points, $Color);
					
				} else {
					if ($YZero < $this->myPicture->GraphAreaX1 + 1) {
						$YZero = $this->myPicture->GraphAreaX1 + 1;
					}

					if ($YZero > $this->myPicture->GraphAreaX2 - 1) {
						$YZero = $this->myPicture->GraphAreaX2 - 1;
					}

					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$LastX = NULL;
					$LastY = NULL;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$LastGoodY = NULL;
					$LastGoodX = NULL;
					$Points = [];
					foreach($PosArray as $Key => $X) {

						if ($X == VOID && $LastX != NULL && $LastY != NULL && (count($Points) > 0)) {
							$Points[] = $LastX;
							$Points[] = $LastY;
							$Points[] = $LastX;
							$Points[] = $Y;
							$Points[] = $YZero;
							$Points[] = $Y;
							$this->myPicture->drawPolygon($Points, $Color);
							$Points = [];
						}

						if ($X != VOID && $LastX != NULL && $LastY != NULL) {
							(count($Points) == 0) AND $Points = [$YZero, $LastY];
							$Points[] = $LastX; 
							$Points[] = $LastY;
							$Points[] = $LastX;
							$Points[] = $Y;
							$Points[] = $X;
							$Points[] = $Y;
						}

						if ($X != VOID) {
							$LastGoodY = $Y;
							$LastGoodX = $X;
						} else {
							$X = NULL;
						}

						if ($LastX == NULL && $ReCenter) {
							$Y = $Y - $YStep / 2;
						}

						$LastX = $X;
						$LastY = $Y;
						if ($LastY < $this->myPicture->GraphAreaY1 + $XMargin) {
							$LastY = $this->myPicture->GraphAreaY1 + $XMargin;
						}

						$Y = $Y + $YStep;
					}

					if ($ReCenter) {
						$Points[] = $LastX;
						$Points[] = $LastY+$YStep/2;
						$Points[] = $YZero;
						$Points[] = $LastY+$YStep/2;
					} else {
						$Points[] = $YZero;
						$Points[] = $LastY;
					}

					$this->myPicture->drawPolygon($Points, $Color);
				}
			}
		}
	}

	/* Draw an area chart */
	function drawAreaChart(array $Format = [])
	{
		$DisplayValues = FALSE;
		$DisplayOffset = 2;
		$DisplayColor = DISPLAY_MANUAL;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$ForceTransparency = 25;
		$AroundZero = TRUE;
		$Threshold = NULL;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = $R;
					$DisplayG = $G;
					$DisplayB = $B;
				}

				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]]);
				$YZero = $this->myPicture->scaleComputeY(0, ["AxisID" => $Serie["Axis"]]);
				if ($Threshold != NULL) {
					foreach($Threshold as $Key => $Params) {
						$Threshold[$Key]["MinX"] = $this->myPicture->scaleComputeY($Params["Min"], ["AxisID" => $Serie["Axis"]]);
						$Threshold[$Key]["MaxX"] = $this->myPicture->scaleComputeY($Params["Max"], ["AxisID" => $Serie["Axis"]]);
					}
				}

				$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					if ($YZero > $this->myPicture->GraphAreaY2 - 1) {
						$YZero = $this->myPicture->GraphAreaY2 - 1;
					}

					$AreaID = 0;
					$Areas = [];
					$Areas[$AreaID][] = $this->myPicture->GraphAreaX1 + $XMargin;
					$Areas[$AreaID][] = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaY2 - 1;
	

					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$LastX = NULL;
					$LastY = NULL;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					foreach($PosArray as $Key => $Y) {
						if ($DisplayValues && $Serie["Data"][$Key] != VOID) {
							if ($Serie["Data"][$Key] > 0) {
								$Align = TEXT_ALIGN_BOTTOMMIDDLE;
								$Offset = $DisplayOffset;
							} else {
								$Align = TEXT_ALIGN_TOPMIDDLE;
								$Offset = - $DisplayOffset;
							}

							$this->myPicture->drawText($X, $Y - $Offset, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => $Align]);
						}

						if ($Y == VOID && isset($Areas[$AreaID])) {
							$Areas[$AreaID][] = ($LastX == NULL) ? $X : $LastX;
							$Areas[$AreaID][] = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaY2 - 1;
							$AreaID++;
						} elseif ($Y != VOID) {
							if (!isset($Areas[$AreaID])) {
								$Areas[$AreaID][] = $X;
								$Areas[$AreaID][] = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaY2 - 1;
							}

							$Areas[$AreaID][] = $X;
							$Areas[$AreaID][] = $Y;
						}

						$LastX = $X;
						$X = $X + $XStep;
					}

					$Areas[$AreaID][] = $LastX;
					$Areas[$AreaID][] = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaY2 - 1;

					/* Handle shadows in the areas */
					if ($this->myPicture->Shadow) {
						$ShadowArea = [];
						foreach($Areas as $Key => $Points) {
							$ShadowArea[$Key] = [];
							foreach($Points as $Key2 => $Value) {
								$ShadowArea[$Key][] = ($Key2 % 2 == 0) ? $Value + $this->myPicture->ShadowX : $Value + $this->myPicture->ShadowY;
							}
						}

						foreach($ShadowArea as $Key => $Points) {
							$this->drawPolygonChart($Points, ["R" => $this->myPicture->ShadowR,"G" => $this->myPicture->ShadowG,"B" => $this->myPicture->ShadowB,"Alpha" => $this->myPicture->Shadowa]);
						}
					}

					$Alpha = $ForceTransparency != NULL ? $ForceTransparency : $Alpha;
					$Color = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Threshold" => $Threshold];
					
					foreach($Areas as $Key => $Points){
						$this->drawPolygonChart($Points, $Color);
					}
					
				} else {
					if ($YZero < $this->myPicture->GraphAreaX1 + 1) {
						$YZero = $this->myPicture->GraphAreaX1 + 1;
					}

					if ($YZero > $this->myPicture->GraphAreaX2 - 1) {
						$YZero = $this->myPicture->GraphAreaX2 - 1;
					}

					$AreaID = 0;
					$Areas = [];
					$Areas[$AreaID][] = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaX1 + 1;
					$Areas[$AreaID][] = $this->myPicture->GraphAreaY1 + $XMargin;
					
					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$LastX = NULL;
					$LastY = NULL;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					foreach($PosArray as $Key => $X) {
						if ($DisplayValues && $Serie["Data"][$Key] != VOID) {
							if ($Serie["Data"][$Key] > 0) {
								$Align = TEXT_ALIGN_BOTTOMMIDDLE;
								$Offset = $DisplayOffset;
							} else {
								$Align = TEXT_ALIGN_TOPMIDDLE;
								$Offset = - $DisplayOffset;
							}

							$this->myPicture->drawText($X + $Offset, $Y, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit),["Angle" => 270,"R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => $Align]);
						}

						if ($X == VOID && isset($Areas[$AreaID])) {
							$Areas[$AreaID][] = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaX1 + 1;
							$Areas[$AreaID][] = ($LastY == NULL) ? $Y : $LastY;
							$AreaID++;
						} elseif ($X != VOID) {
							if (!isset($Areas[$AreaID])) {
								$Areas[$AreaID][] = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaX1 + 1;
								$Areas[$AreaID][] = $Y;
							}

							$Areas[$AreaID][] = $X;
							$Areas[$AreaID][] = $Y;
						}

						$LastX = $X;
						$LastY = $Y;
						$Y = $Y + $YStep;
					}

					$Areas[$AreaID][] = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaX1 + 1;
					$Areas[$AreaID][] = $LastY;
					
					/* Handle shadows in the areas */
					if ($this->myPicture->Shadow) {
						$ShadowArea = [];
						foreach($Areas as $Key => $Points) {
							$ShadowArea[$Key] = [];
							foreach($Points as $Key2 => $Value) {
								$ShadowArea[$Key][] = ($Key2 % 2 == 0) ? ($Value + $this->myPicture->ShadowX) : ($Value + $this->myPicture->ShadowY);
							}
						}

						foreach($ShadowArea as $Key => $Points) {
							$this->drawPolygonChart($Points, ["R" => $this->myPicture->ShadowR,"G" => $this->myPicture->ShadowG,"B" => $this->myPicture->ShadowB,"Alpha" => $this->myPicture->Shadowa]);
						}
					}

					$Alpha = $ForceTransparency != NULL ? $ForceTransparency : $Alpha;
					$Color = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Threshold" => $Threshold];
					foreach($Areas as $Key => $Points) {
						$this->drawPolygonChart($Points, $Color);
					}
				}
			}
		}
	}
	
	/* Draw a bar chart */
	function drawBarChart(array $Format = [])
	{
		$Floating0Serie = NULL;
		$Floating0Value = NULL;
		$Draw0Line = FALSE;
		$DisplayValues = FALSE;
		$DisplayOrientation = ORIENTATION_HORIZONTAL;
		$DisplayOffset = 2;
		$DisplayColor = DISPLAY_MANUAL;
		$DisplayFont = isset($Format["DisplaySize"]) ? $Format["DisplaySize"] : $this->myPicture->FontName; # Momchil: Probably a bug
		$DisplaySize = $this->myPicture->FontSize;
		$DisplayPos = LABEL_POS_OUTSIDE;
		$DisplayShadow = TRUE;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$AroundZero = TRUE;
		$Interleave = .5;
		$Rounded = FALSE;
		$RoundRadius = 4;
		$Surrounding = NULL;
		$BorderR = -1;
		$BorderG = -1;
		$BorderB = -1;
		$Gradient = FALSE;
		$GradientMode = GRADIENT_SIMPLE;
		$GradientAlpha = 20;
		$GradientStartR = 255;
		$GradientStartG = 255;
		$GradientStartB = 255;
		$GradientEndR = 0;
		$GradientEndG = 0;
		$GradientEndB = 0;
		$TxtMargin = 6;
		$OverrideColors = NULL;
		$OverrideSurrounding = 30;
		$InnerSurrounding = NULL;
		$InnerBorderR = -1;
		$InnerBorderG = -1;
		$InnerBorderB = -1;
		$RecordImageMap = FALSE;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		if ($OverrideColors != NULL) {
			$OverrideColors = $this->myPicture->validatePalette($OverrideColors, $OverrideSurrounding);
			$this->myPicture->myData->saveExtendedData("Palette", $OverrideColors);
		}

		$RestoreShadow = $this->myPicture->Shadow;
		$SeriesCount = $this->myPicture->countDrawableSeries();
		$CurrentSerie = 0;
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = $R;
					$DisplayG = $G;
					$DisplayB = $B;
				}

				if ($Surrounding != NULL) {
					$BorderR = $R + $Surrounding;
					$BorderG = $G + $Surrounding;
					$BorderB = $B + $Surrounding;
				}

				if ($InnerSurrounding != NULL) {
					$InnerBorderR = $R + $InnerSurrounding;
					$InnerBorderG = $G + $InnerSurrounding;
					$InnerBorderB = $B + $InnerSurrounding;
				}

				$InnerColor = ($InnerBorderR == - 1) ? NULL : ["R" => $InnerBorderR,"G" => $InnerBorderG,"B" => $InnerBorderB];
				$Color = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"BorderR" => $BorderR,"BorderG" => $BorderG,"BorderB" => $BorderB];
				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$SerieDescription =  (isset($Serie["Description"])) ? $Serie["Description"] : $SerieName;

				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]]);
				if ($Floating0Value != NULL) {
					$YZero = $this->myPicture->scaleComputeY($Floating0Value, ["AxisID" => $Serie["Axis"]]);
				} else {
					$YZero = $this->myPicture->scaleComputeY([], ["AxisID" => $Serie["Axis"]]);
				}

				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					($YZero > $this->myPicture->GraphAreaY2 - 1) AND $YZero = $this->myPicture->GraphAreaY2 - 1;
					($YZero < $this->myPicture->GraphAreaY1 + 1) AND $YZero = $this->myPicture->GraphAreaY1 + 1;
					$XStep = ($XDivs == 0) ? 0 : ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$Y1 = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaY2 - 1;

					if ($XDivs == 0) {
						$XSize = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / ($SeriesCount + $Interleave);
					} else {
						$XSize = ($XStep / ($SeriesCount + $Interleave));
					}

					$XOffset = - ($XSize * $SeriesCount) / 2 + $CurrentSerie * $XSize;
					if ($X + $XOffset <= $this->myPicture->GraphAreaX1) {
						$XOffset = $this->myPicture->GraphAreaX1 - $X + 1;
					}

					$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = $XOffset + $XSize / 2;
					$XSpace = ($Rounded || $BorderR != - 1) ? 1 : 0;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$ID = 0;
					foreach($PosArray as $Key => $Y2) {
						if ($Floating0Serie != NULL) {
							$Value = (isset($Data["Series"][$Floating0Serie]["Data"][$Key])) ? $Data["Series"][$Floating0Serie]["Data"][$Key] : 0;
							$YZero = $this->myPicture->scaleComputeY($Value, ["AxisID" => $Serie["Axis"]]);
							($YZero > $this->myPicture->GraphAreaY2 - 1) AND $YZero = $this->myPicture->GraphAreaY2 - 1;
							($YZero < $this->myPicture->GraphAreaY1 + 1) AND $YZero = $this->myPicture->GraphAreaY1 + 1;
							$Y1 = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaY2 - 1;
						}

						if ($OverrideColors != NULL) {
							if (isset($OverrideColors[$ID])) {
								$Color = ["R" => $OverrideColors[$ID]["R"],"G" => $OverrideColors[$ID]["G"],"B" => $OverrideColors[$ID]["B"],"Alpha" => $OverrideColors[$ID]["Alpha"],"BorderR" => $OverrideColors[$ID]["BorderR"],"BorderG" => $OverrideColors[$ID]["BorderG"],"BorderB" => $OverrideColors[$ID]["BorderB"]];
							} else {
								$Color = $this->myPicture->getRandomColor();
							}
						}

						if ($Y2 != VOID) {
							$BarHeight = $Y1 - $Y2;
							if ($Serie["Data"][$Key] == 0) {
								$this->myPicture->drawLine($X + $XOffset + $XSpace, $Y1, $X + $XOffset + $XSize - $XSpace, $Y1, $Color);
								if ($RecordImageMap) {
									$this->myPicture->addToImageMap("RECT", floor($X + $XOffset + $XSpace) . "," . floor($Y1 - 1) . "," . floor($X + $XOffset + $XSize - $XSpace) . "," . floor($Y1 + 1), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
								}
							} else {
								if ($RecordImageMap) {
									$this->myPicture->addToImageMap("RECT", floor($X + $XOffset + $XSpace) . "," . floor($Y1) . "," . floor($X + $XOffset + $XSize - $XSpace) . "," . floor($Y2), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
								}

								if ($Rounded){
									$this->myPicture->drawRoundedFilledRectangle($X + $XOffset + $XSpace, $Y1, $X + $XOffset + $XSize - $XSpace, $Y2, $RoundRadius, $Color);
								} else {
									$this->myPicture->drawFilledRectangle($X + $XOffset + $XSpace, $Y1, $X + $XOffset + $XSize - $XSpace, $Y2, $Color);
									if ($InnerColor != NULL) {
										$this->myPicture->drawRectangle($X + $XOffset + $XSpace + 1, min($Y1, $Y2) + 1, $X + $XOffset + $XSize - $XSpace - 1, max($Y1, $Y2) - 1, $InnerColor);
									}

									if ($Gradient) {
										$this->myPicture->Shadow = FALSE;
										if ($GradientMode == GRADIENT_SIMPLE) {
											if ($Serie["Data"][$Key] >= 0) {
												$GradienColor = ["StartR" => $GradientStartR,"StartG" => $GradientStartG,"StartB" => $GradientStartB,"EndR" => $GradientEndR,"EndG" => $GradientEndG,"EndB" => $GradientEndB,"Alpha" => $GradientAlpha];
											} else {
												$GradienColor = ["StartR" => $GradientEndR,"StartG" => $GradientEndG,"StartB" => $GradientEndB,"EndR" => $GradientStartR,"EndG" => $GradientStartG,"EndB" => $GradientStartB,"Alpha" => $GradientAlpha];
											}
											$this->myPicture->drawGradientArea($X + $XOffset + $XSpace, $Y1, $X + $XOffset + $XSize - $XSpace, $Y2, DIRECTION_VERTICAL, $GradienColor);
										} elseif ($GradientMode == GRADIENT_EFFECT_CAN) {
											$GradienColor1 = ["StartR" => $GradientEndR,"StartG" => $GradientEndG,"StartB" => $GradientEndB,"EndR" => $GradientStartR,"EndG" => $GradientStartG,"EndB" => $GradientStartB,"Alpha" => $GradientAlpha];
											$GradienColor2 = ["StartR" => $GradientStartR,"StartG" => $GradientStartG,"StartB" => $GradientStartB,"EndR" => $GradientEndR,"EndG" => $GradientEndG,"EndB" => $GradientEndB,"Alpha" => $GradientAlpha];
											$XSpan = floor($XSize / 3);
											$this->myPicture->drawGradientArea($X + $XOffset + $XSpace, $Y1, $X + $XOffset + $XSpan - $XSpace, $Y2, DIRECTION_HORIZONTAL, $GradienColor1);
											$this->myPicture->drawGradientArea($X + $XOffset + $XSpan + $XSpace, $Y1, $X + $XOffset + $XSize - $XSpace, $Y2, DIRECTION_HORIZONTAL, $GradienColor2);
										}

										$this->myPicture->Shadow = $RestoreShadow;
									}
								}

								if ($Draw0Line) {
									$Line0Color = ["R" => 0,"G" => 0,"B" => 0,"Alpha" => 20];
									$Line0Width = (abs($Y1 - $Y2) > 3) ? 3 : 1;
									($Y1 - $Y2 < 0) AND $Line0Width = - $Line0Width;
									$this->myPicture->drawFilledRectangle($X + $XOffset + $XSpace, floor($Y1), $X + $XOffset + $XSize - $XSpace, floor($Y1) - $Line0Width, $Line0Color);
									$this->myPicture->drawLine($X + $XOffset + $XSpace, floor($Y1), $X + $XOffset + $XSize - $XSpace, floor($Y1), $Line0Color);
								}
							}

							if ($DisplayValues && $Serie["Data"][$Key] != VOID) {
								($DisplayShadow) AND $this->myPicture->Shadow = TRUE;
								$Caption = $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit);
								$TxtPos = $this->myPicture->getTextBox(0, 0, $DisplayFont, $DisplaySize, 90, $Caption);
								$TxtHeight = $TxtPos[0]["Y"] - $TxtPos[1]["Y"] + $TxtMargin;
								if ($DisplayPos == LABEL_POS_INSIDE && abs($TxtHeight) < abs($BarHeight)) {
									$CenterX = (($X + $XOffset + $XSize - $XSpace) - ($X + $XOffset + $XSpace)) / 2 + $X + $XOffset + $XSpace;
									$CenterY = ($Y2 - $Y1) / 2 + $Y1;
									$this->myPicture->drawText($CenterX, $CenterY, $Caption, ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => TEXT_ALIGN_MIDDLEMIDDLE,"FontSize" => $DisplaySize,"Angle" => 90]);
								} else {
									if ($Serie["Data"][$Key] >= 0) {
										$Align = TEXT_ALIGN_BOTTOMMIDDLE;
										$Offset = $DisplayOffset;
									} else {
										$Align = TEXT_ALIGN_TOPMIDDLE;
										$Offset = - $DisplayOffset;
									}

									$this->myPicture->drawText($X + $XOffset + $XSize / 2, $Y2 - $Offset, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => $Align,"FontSize" => $DisplaySize]);
								}

								$this->myPicture->Shadow = $RestoreShadow;
							}
						}

						$X = $X + $XStep;
						$ID++;
					}
					
				} else {
					
					($YZero < $this->myPicture->GraphAreaX1 + 1) AND $YZero = $this->myPicture->GraphAreaX1 + 1;
					($YZero > $this->myPicture->GraphAreaX2 - 1) AND $YZero = $this->myPicture->GraphAreaX2 - 1;
					$YStep = ($XDivs == 0) ? 0 : ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$X1 = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaX1 + 1;

					if ($XDivs == 0) {
						$YSize = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / ($SeriesCount + $Interleave);
					} else {
						$YSize = ($YStep / ($SeriesCount + $Interleave));
					}

					$YOffset = - ($YSize * $SeriesCount) / 2 + $CurrentSerie * $YSize;
					if ($Y + $YOffset <= $this->myPicture->GraphAreaY1) {
						$YOffset = $this->myPicture->GraphAreaY1 - $Y + 1;
					}

					$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = $YOffset + $YSize / 2;
					$YSpace = ($Rounded || $BorderR != - 1) ? 1 : 0;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$ID = 0;
					foreach($PosArray as $Key => $X2) {
						if ($Floating0Serie != NULL) {
							$Value = (isset($Data["Series"][$Floating0Serie]["Data"][$Key])) ? $Data["Series"][$Floating0Serie]["Data"][$Key] : 0;
							$YZero = $this->myPicture->scaleComputeY($Value, ["AxisID" => $Serie["Axis"]]);
							($YZero < $this->myPicture->GraphAreaX1 + 1) AND $YZero = $this->myPicture->GraphAreaX1 + 1;
							($YZero > $this->myPicture->GraphAreaX2 - 1) AND $YZero = $this->myPicture->GraphAreaX2 - 1;
							$X1 = ($AroundZero) ? $YZero : $this->myPicture->GraphAreaX1 + 1;
						}

						if ($OverrideColors != NULL) {
							if (isset($OverrideColors[$ID])) {
								$Color = ["R" => $OverrideColors[$ID]["R"],"G" => $OverrideColors[$ID]["G"],"B" => $OverrideColors[$ID]["B"],"Alpha" => $OverrideColors[$ID]["Alpha"],"BorderR" => $OverrideColors[$ID]["BorderR"],"BorderG" => $OverrideColors[$ID]["BorderG"],"BorderB" => $OverrideColors[$ID]["BorderB"]];
							} else {
								$Color = $this->myPicture->getRandomColor();
							}
						}

						if ($X2 != VOID) {
							$BarWidth = $X2 - $X1;
							if ($Serie["Data"][$Key] == 0) {
								$this->myPicture->drawLine($X1, $Y + $YOffset + $YSpace, $X1, $Y + $YOffset + $YSize - $YSpace, $Color);
								if ($RecordImageMap) {
									$this->myPicture->addToImageMap("RECT", floor($X1 - 1) . "," . floor($Y + $YOffset + $YSpace) . "," . floor($X1 + 1) . "," . floor($Y + $YOffset + $YSize - $YSpace), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
								}
							} else {
								if ($RecordImageMap) {
									$this->myPicture->addToImageMap("RECT", floor($X1) . "," . floor($Y + $YOffset + $YSpace) . "," . floor($X2) . "," . floor($Y + $YOffset + $YSize - $YSpace), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
								}

								if ($Rounded) {
									$this->myPicture->drawRoundedFilledRectangle($X1 + 1, $Y + $YOffset + $YSpace, $X2, $Y + $YOffset + $YSize - $YSpace, $RoundRadius, $Color);
								} else {
									$this->myPicture->drawFilledRectangle($X1, $Y + $YOffset + $YSpace, $X2, $Y + $YOffset + $YSize - $YSpace, $Color);
									if ($InnerColor != NULL) {
										$this->myPicture->drawRectangle(min($X1, $X2) + 1, $Y + $YOffset + $YSpace + 1, max($X1, $X2) - 1, $Y + $YOffset + $YSize - $YSpace - 1, $InnerColor);
									}

									if ($Gradient) {
										$this->myPicture->Shadow = FALSE;
										if ($GradientMode == GRADIENT_SIMPLE) {
											if ($Serie["Data"][$Key] >= 0) {
												$GradienColor = ["StartR" => $GradientStartR,"StartG" => $GradientStartG,"StartB" => $GradientStartB,"EndR" => $GradientEndR,"EndG" => $GradientEndG,"EndB" => $GradientEndB,"Alpha" => $GradientAlpha];
											} else {
												$GradienColor = ["StartR" => $GradientEndR,"StartG" => $GradientEndG,"StartB" => $GradientEndB,"EndR" => $GradientStartR,"EndG" => $GradientStartG,"EndB" => $GradientStartB,"Alpha" => $GradientAlpha];
											}

											$this->myPicture->drawGradientArea($X1, $Y + $YOffset + $YSpace, $X2, $Y + $YOffset + $YSize - $YSpace, DIRECTION_HORIZONTAL, $GradienColor);
										} elseif ($GradientMode == GRADIENT_EFFECT_CAN) {
											$GradienColor1 = ["StartR" => $GradientEndR,"StartG" => $GradientEndG,"StartB" => $GradientEndB,"EndR" => $GradientStartR,"EndG" => $GradientStartG,"EndB" => $GradientStartB,"Alpha" => $GradientAlpha];
											$GradienColor2 = ["StartR" => $GradientStartR,"StartG" => $GradientStartG,"StartB" => $GradientStartB,"EndR" => $GradientEndR,"EndG" => $GradientEndG,"EndB" => $GradientEndB,"Alpha" => $GradientAlpha];
											$YSpan = floor($YSize / 3);
											$this->myPicture->drawGradientArea($X1, $Y + $YOffset + $YSpace, $X2, $Y + $YOffset + $YSpan - $YSpace, DIRECTION_VERTICAL, $GradienColor1);
											$this->myPicture->drawGradientArea($X1, $Y + $YOffset + $YSpan, $X2, $Y + $YOffset + $YSize - $YSpace, DIRECTION_VERTICAL, $GradienColor2);
										}

										$this->myPicture->Shadow = $RestoreShadow;
									}
								}

								if ($Draw0Line) {
									$Line0Color = ["R" => 0,"G" => 0,"B" => 0,"Alpha" => 20];
									$Line0Width = (abs($X1 - $X2) > 3) ? 3 : 1;
									($X2 - $X1 < 0) AND $Line0Width = - $Line0Width;
									$this->myPicture->drawFilledRectangle(floor($X1), $Y + $YOffset + $YSpace, floor($X1) + $Line0Width, $Y + $YOffset + $YSize - $YSpace, $Line0Color);
									$this->myPicture->drawLine(floor($X1), $Y + $YOffset + $YSpace, floor($X1), $Y + $YOffset + $YSize - $YSpace, $Line0Color);
								}
							}

							if ($DisplayValues && $Serie["Data"][$Key] != VOID) {
								($DisplayShadow) AND $this->myPicture->Shadow = TRUE;
								$Caption = $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit);
								$TxtPos = $this->myPicture->getTextBox(0, 0, $DisplayFont, $DisplaySize, 0, $Caption);
								$TxtWidth = $TxtPos[1]["X"] - $TxtPos[0]["X"] + $TxtMargin;
								if ($DisplayPos == LABEL_POS_INSIDE && abs($TxtWidth) < abs($BarWidth)) {
									$CenterX = ($X2 - $X1) / 2 + $X1;
									$CenterY = (($Y + $YOffset + $YSize - $YSpace) - ($Y + $YOffset + $YSpace)) / 2 + ($Y + $YOffset + $YSpace);
									$this->myPicture->drawText($CenterX, $CenterY, $Caption, ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => TEXT_ALIGN_MIDDLEMIDDLE,"FontSize" => $DisplaySize]);
								} else {
									if ($Serie["Data"][$Key] >= 0) {
										$Align = TEXT_ALIGN_MIDDLELEFT;
										$Offset = $DisplayOffset;
									} else {
										$Align = TEXT_ALIGN_MIDDLERIGHT;
										$Offset = - $DisplayOffset;
									}

									$this->myPicture->drawText($X2 + $Offset, $Y + $YOffset + $YSize / 2, $Caption, ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => $Align,"FontSize" => $DisplaySize]);
								}

								$this->myPicture->Shadow = $RestoreShadow;
							}
						}

						$Y = $Y + $YStep;
						$ID++;
					}
				}

				$CurrentSerie++;
			}
		}
	}

	/* Draw a bar chart */
	function drawStackedBarChart(array $Format = [])
	{
		$DisplayValues = FALSE;
		$DisplayOrientation = ORIENTATION_AUTO;
		$DisplayRound = 0;
		$DisplayColor = DISPLAY_MANUAL;
		$DisplayFont = $this->myPicture->FontName;
		$DisplaySize = $this->myPicture->FontSize;
		$DisplayR = 0;
		$DisplayG = 0;
		$DisplayB = 0;
		$Interleave = .5;
		$Rounded = FALSE;
		$RoundRadius = 4;
		$Surrounding = NULL;
		$BorderR = -1;
		$BorderG = -1;
		$BorderB = -1;
		$Gradient = FALSE;
		$GradientMode = GRADIENT_SIMPLE;
		$GradientAlpha = 20;
		$GradientStartR = 255;
		$GradientStartG = 255;
		$GradientStartB = 255;
		$GradientEndR = 0;
		$GradientEndG = 0;
		$GradientEndB = 0;
		$InnerSurrounding = NULL;
		$InnerBorderR = -1;
		$InnerBorderG = -1;
		$InnerBorderB = -1;
		$RecordImageMap = FALSE;
		$FontFactor = 8;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_STACKED;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		$RestoreShadow = $this->myPicture->Shadow;
		$LastX = [];
		$LastY = [];
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				if ($DisplayColor == DISPLAY_AUTO) {
					$DisplayR = 255;
					$DisplayG = 255;
					$DisplayB = 255;
				}

				if ($Surrounding != NULL) {
					$BorderR = $R + $Surrounding;
					$BorderG = $G + $Surrounding;
					$BorderB = $B + $Surrounding;
				}

				if ($InnerSurrounding != NULL) {
					$InnerBorderR = $R + $InnerSurrounding;
					$InnerBorderG = $G + $InnerSurrounding;
					$InnerBorderB = $B + $InnerSurrounding;
				}

				$InnerColor = ($InnerBorderR == - 1) ? NULL : ["R" => $InnerBorderR,"G" => $InnerBorderG,"B" => $InnerBorderB];
				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$SerieDescription = (isset($Serie["Description"])) ? $Serie["Description"] : $SerieName;
				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]], TRUE);
				$YZero = $this->myPicture->scaleComputeY(0, ["AxisID" => $Serie["Axis"]]);
				$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				$Color = ["TransCorner" => TRUE,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"BorderR" => $BorderR,"BorderG" => $BorderG,"BorderB" => $BorderB];
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					($YZero > $this->myPicture->GraphAreaY2 - 1) AND $YZero = $this->myPicture->GraphAreaY2 - 1;
					($YZero > $this->myPicture->GraphAreaY2 - 1) AND $YZero = $this->myPicture->GraphAreaY2 - 1;
					
					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;
					$XSize = ($XStep / (1 + $Interleave));
					$XOffset = - ($XSize / 2);

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					foreach($PosArray as $Key => $Height) {
						if ($Height != VOID && $Serie["Data"][$Key] != 0) {
							$Pos = ($Serie["Data"][$Key] > 0) ? "+" : "-";
	
							(!isset($LastY[$Key])) AND $LastY[$Key] = [];
							(!isset($LastY[$Key][$Pos])) AND $LastY[$Key][$Pos] = $YZero;
							
							$Y1 = $LastY[$Key][$Pos];
							$Y2 = $Y1 - $Height;
							$YSpaceUp = (($Rounded || $BorderR != - 1) && ($Pos == "+" && $Y1 != $YZero)) ? 1 : 0;
							$YSpaceDown = (($Rounded || $BorderR != - 1) && ($Pos == "-" && $Y1 != $YZero)) ? 1 : 0;

							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("RECT", floor($X + $XOffset) . "," . floor($Y1 - $YSpaceUp + $YSpaceDown) . "," . floor($X + $XOffset + $XSize) . "," . floor($Y2), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}

							if ($Rounded) {
								$this->myPicture->drawRoundedFilledRectangle($X + $XOffset, $Y1 - $YSpaceUp + $YSpaceDown, $X + $XOffset + $XSize, $Y2, $RoundRadius, $Color);
							} else {
								$this->myPicture->drawFilledRectangle($X + $XOffset, $Y1 - $YSpaceUp + $YSpaceDown, $X + $XOffset + $XSize, $Y2, $Color);
								if ($InnerColor != NULL) {
									$RestoreShadow = $this->myPicture->Shadow;
									$this->myPicture->Shadow = FALSE;
									$this->myPicture->drawRectangle(min($X + $XOffset + 1, $X + $XOffset + $XSize), min($Y1 - $YSpaceUp + $YSpaceDown, $Y2) + 1, max($X + $XOffset + 1, $X + $XOffset + $XSize) - 1, max($Y1 - $YSpaceUp + $YSpaceDown, $Y2) - 1, $InnerColor);
									$this->myPicture->Shadow = $RestoreShadow;
								}

								if ($Gradient) {
									$this->myPicture->Shadow = FALSE;
									if ($GradientMode == GRADIENT_SIMPLE) {
										$GradientColor = ["StartR" => $GradientStartR,"StartG" => $GradientStartG,"StartB" => $GradientStartB,"EndR" => $GradientEndR,"EndG" => $GradientEndG,"EndB" => $GradientEndB,"Alpha" => $GradientAlpha];
										$this->myPicture->drawGradientArea($X + $XOffset, $Y1 - 1 - $YSpaceUp + $YSpaceDown, $X + $XOffset + $XSize, $Y2 + 1, DIRECTION_VERTICAL, $GradientColor);
									} elseif ($GradientMode == GRADIENT_EFFECT_CAN) {
										$GradientColor1 = ["StartR" => $GradientEndR,"StartG" => $GradientEndG,"StartB" => $GradientEndB,"EndR" => $GradientStartR,"EndG" => $GradientStartG,"EndB" => $GradientStartB,"Alpha" => $GradientAlpha];
										$GradientColor2 = ["StartR" => $GradientStartR,"StartG" => $GradientStartG,"StartB" => $GradientStartB,"EndR" => $GradientEndR,"EndG" => $GradientEndG,"EndB" => $GradientEndB,"Alpha" => $GradientAlpha];
										$XSpan = floor($XSize / 3);
										$this->myPicture->drawGradientArea($X + $XOffset - .5, $Y1 - .5 - $YSpaceUp + $YSpaceDown, $X + $XOffset + $XSpan, $Y2 + .5, DIRECTION_HORIZONTAL, $GradientColor1);
										$this->myPicture->drawGradientArea($X + $XSpan + $XOffset - .5, $Y1 - .5 - $YSpaceUp + $YSpaceDown, $X + $XOffset + $XSize, $Y2 + .5, DIRECTION_HORIZONTAL, $GradientColor2);
									}

									$this->myPicture->Shadow = $RestoreShadow;
								}
							}

							if ($DisplayValues) {
								$BarHeight = abs($Y2 - $Y1) - 2;
								$BarWidth = $XSize + ($XOffset / 2) - $FontFactor;
								$Caption = $this->myPicture->scaleFormat(round($Serie["Data"][$Key], $DisplayRound), $Mode, $Format, $Unit);
								$TxtPos = $this->myPicture->getTextBox(0, 0, $DisplayFont, $DisplaySize, 0, $Caption);
								$TxtHeight = abs($TxtPos[2]["Y"] - $TxtPos[0]["Y"]);
								$TxtWidth = abs($TxtPos[1]["X"] - $TxtPos[0]["X"]);
								$XCenter = (($X + $XOffset + $XSize) - ($X + $XOffset)) / 2 + $X + $XOffset;
								$YCenter = (($Y2) - ($Y1 - $YSpaceUp + $YSpaceDown)) / 2 + $Y1 - $YSpaceUp + $YSpaceDown;
								$Done = FALSE;
								if ($DisplayOrientation == ORIENTATION_HORIZONTAL || $DisplayOrientation == ORIENTATION_AUTO) {
									if ($TxtHeight < $BarHeight && $TxtWidth < $BarWidth) {
										$this->myPicture->drawText($XCenter, $YCenter, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => TEXT_ALIGN_MIDDLEMIDDLE,"FontSize" => $DisplaySize,"FontName" => $DisplayFont]);
										$Done = TRUE;
									}
								}

								if ($DisplayOrientation == ORIENTATION_VERTICAL || ($DisplayOrientation == ORIENTATION_AUTO && !$Done)) {
									if ($TxtHeight < $BarWidth && $TxtWidth < $BarHeight) {
										$this->myPicture->drawText($XCenter, $YCenter, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Angle" => 90,"Align" => TEXT_ALIGN_MIDDLEMIDDLE,"FontSize" => $DisplaySize,"FontName" => $DisplayFont]);
									}
								}
							}

							$LastY[$Key][$Pos] = $Y2;
						}

						$X = $X + $XStep;
					}
				} else { # SCALE_POS_LEFTRIGHT
				
					($YZero < $this->myPicture->GraphAreaX1 + 1) AND $YZero = $this->myPicture->GraphAreaX1 + 1;
					($YZero > $this->myPicture->GraphAreaX2 - 1) AND $YZero = $this->myPicture->GraphAreaX2 - 1;

					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;
					$YSize = $YStep / (1 + $Interleave);
					$YOffset = - ($YSize / 2);

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					foreach($PosArray as $Key => $Width) {
						if ($Width != VOID && $Serie["Data"][$Key] != 0) {
							$Pos = ($Serie["Data"][$Key] > 0) ? "+" : "-";
							(!isset($LastX[$Key])) AND $LastX[$Key] = [];
							(!isset($LastX[$Key][$Pos])) AND $LastX[$Key][$Pos] = $YZero;
							$X1 = $LastX[$Key][$Pos];
							$X2 = $X1 + $Width;
							$XSpaceLeft = (($Rounded || $BorderR != - 1) && ($Pos == "+" && $X1 != $YZero)) ? 2 : 0;
							$XSpaceRight = (($Rounded || $BorderR != - 1) && ($Pos == "-" && $X1 != $YZero)) ? 2 : 0;
							
							if ($RecordImageMap) {
								$this->myPicture->addToImageMap("RECT", floor($X1 + $XSpaceLeft) . "," . floor($Y + $YOffset) . "," . floor($X2 - $XSpaceRight) . "," . floor($Y + $YOffset + $YSize), $this->myPicture->toHTMLColor($R, $G, $B), $SerieDescription, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit));
							}

							if ($Rounded) {
								$this->myPicture->drawRoundedFilledRectangle($X1 + $XSpaceLeft, $Y + $YOffset, $X2 - $XSpaceRight, $Y + $YOffset + $YSize, $RoundRadius, $Color);
							} else {
								$this->myPicture->drawFilledRectangle($X1 + $XSpaceLeft, $Y + $YOffset, $X2 - $XSpaceRight, $Y + $YOffset + $YSize, $Color);
								if ($InnerColor != NULL) {
									$RestoreShadow = $this->myPicture->Shadow;
									$this->myPicture->Shadow = FALSE;
									$this->myPicture->drawRectangle(min($X1 + $XSpaceLeft, $X2 - $XSpaceRight) + 1, min($Y + $YOffset, $Y + $YOffset + $YSize) + 1, max($X1 + $XSpaceLeft, $X2 - $XSpaceRight) - 1, max($Y + $YOffset, $Y + $YOffset + $YSize) - 1, $InnerColor);
									$this->myPicture->Shadow = $RestoreShadow;
								}

								if ($Gradient) {
									$this->myPicture->Shadow = FALSE;
									if ($GradientMode == GRADIENT_SIMPLE) {
										$GradientColor = ["StartR" => $GradientStartR,"StartG" => $GradientStartG,"StartB" => $GradientStartB,"EndR" => $GradientEndR,"EndG" => $GradientEndG,"EndB" => $GradientEndB,"Alpha" => $GradientAlpha];
										$this->myPicture->drawGradientArea($X1 + $XSpaceLeft, $Y + $YOffset, $X2 - $XSpaceRight, $Y + $YOffset + $YSize, DIRECTION_HORIZONTAL, $GradientColor);
									} elseif ($GradientMode == GRADIENT_EFFECT_CAN) {
										$GradientColor1 = ["StartR" => $GradientEndR,"StartG" => $GradientEndG,"StartB" => $GradientEndB,"EndR" => $GradientStartR,"EndG" => $GradientStartG,"EndB" => $GradientStartB,"Alpha" => $GradientAlpha];
										$GradientColor2 = ["StartR" => $GradientStartR,"StartG" => $GradientStartG,"StartB" => $GradientStartB,"EndR" => $GradientEndR,"EndG" => $GradientEndG,"EndB" => $GradientEndB,"Alpha" => $GradientAlpha];
										$YSpan = floor($YSize / 3);
										$this->myPicture->drawGradientArea($X1 + $XSpaceLeft, $Y + $YOffset, $X2 - $XSpaceRight, $Y + $YOffset + $YSpan, DIRECTION_VERTICAL, $GradientColor1);
										$this->myPicture->drawGradientArea($X1 + $XSpaceLeft, $Y + $YOffset + $YSpan, $X2 - $XSpaceRight, $Y + $YOffset + $YSize, DIRECTION_VERTICAL, $GradientColor2);
									}

									$this->myPicture->Shadow = $RestoreShadow;
								}
							}

							if ($DisplayValues) {
								$BarWidth = abs($X2 - $X1) - $FontFactor;
								$BarHeight = $YSize + ($YOffset / 2) - $FontFactor / 2;
								$Caption = $this->myPicture->scaleFormat(round($Serie["Data"][$Key], $DisplayRound), $Mode, $Format, $Unit);
								$TxtPos = $this->myPicture->getTextBox(0, 0, $DisplayFont, $DisplaySize, 0, $Caption);
								$TxtHeight = abs($TxtPos[2]["Y"] - $TxtPos[0]["Y"]);
								$TxtWidth = abs($TxtPos[1]["X"] - $TxtPos[0]["X"]);
								$XCenter = ($X2 - $X1) / 2 + $X1;
								$YCenter = (($Y + $YOffset + $YSize) - ($Y + $YOffset)) / 2 + $Y + $YOffset;
								$Done = FALSE;
								if ($DisplayOrientation == ORIENTATION_HORIZONTAL || $DisplayOrientation == ORIENTATION_AUTO) {
									if ($TxtHeight < $BarHeight && $TxtWidth < $BarWidth) {
										$this->myPicture->drawText($XCenter, $YCenter, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Align" => TEXT_ALIGN_MIDDLEMIDDLE,"FontSize" => $DisplaySize,"FontName" => $DisplayFont]);
										$Done = TRUE;
									}
								}

								if ($DisplayOrientation == ORIENTATION_VERTICAL || ($DisplayOrientation == ORIENTATION_AUTO && !$Done)) {
									if ($TxtHeight < $BarWidth && $TxtWidth < $BarHeight) {
										$this->myPicture->drawText($XCenter, $YCenter, $this->myPicture->scaleFormat($Serie["Data"][$Key], $Mode, $Format, $Unit), ["R" => $DisplayR,"G" => $DisplayG,"B" => $DisplayB,"Angle" => 90,"Align" => TEXT_ALIGN_MIDDLEMIDDLE,"FontSize" => $DisplaySize,"FontName" => $DisplayFont]);
									}
								}
							}

							$LastX[$Key][$Pos] = $X2;
						}

						$Y = $Y + $YStep;
					}
				}
			}
		}
	} 

	/* Draw a stacked area chart */
	function drawStackedAreaChart(array $Format = [])
	{
		$DrawLine = FALSE;
		$LineSurrounding = NULL;
		$LineR = VOID;
		$LineG = VOID;
		$LineB = VOID;
		$LineAlpha = 100;
		$DrawPlot = FALSE;
		$PlotRadius = 2;
		$PlotBorder = 1;
		$PlotBorderSurrounding = NULL;
		$PlotBorderR = 0;
		$PlotBorderG = 0;
		$PlotBorderB = 0;
		$PlotBorderAlpha = 50;
		$ForceTransparency = NULL;
		
		/* Override defaults */
		extract($Format);
		
		$this->myPicture->LastChartLayout = CHART_LAST_LAYOUT_STACKED;
		$Data = $this->myPicture->myData->Data;
		list($XMargin, $XDivs) = $this->myPicture->scaleGetXSettings();
		$RestoreShadow = $this->myPicture->Shadow;
		$this->myPicture->Shadow = FALSE;
		/* Build the offset data series */

		// $OffsetData    = ""; # UNUSED

		$OverallOffset = [];
		$SerieOrder = [];
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				$SerieOrder[] = $SerieName;
				foreach($Serie["Data"] as $Key => $Value) {
					
					($Value == VOID) AND $Value = 0;
					$Sign = ($Value >= 0) ? "+" : "-";
					(!isset($OverallOffset[$Key]) || !isset($OverallOffset[$Key][$Sign])) AND $OverallOffset[$Key][$Sign] = 0;
					$Data["Series"][$SerieName]["Data"][$Key] = ($Sign == "+") ? $Value + $OverallOffset[$Key][$Sign] : $Value - $OverallOffset[$Key][$Sign];
					$OverallOffset[$Key][$Sign] = $OverallOffset[$Key][$Sign] + abs($Value);
				}
			}
		}

		$SerieOrder = array_reverse($SerieOrder);

		// $LastX = ""; $LastY = ""; # UNUSED

		foreach($SerieOrder as $Key => $SerieName) {
			$Serie = $Data["Series"][$SerieName];
			if ($Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"]) {
				
				$R = $Serie["Color"]["R"];
				$G = $Serie["Color"]["G"];
				$B = $Serie["Color"]["B"];
				$Alpha = $Serie["Color"]["Alpha"];
				$Ticks = $Serie["Ticks"];
				($ForceTransparency != NULL) AND $Alpha = $ForceTransparency;
				$Color = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha];
				
				if ($LineSurrounding != NULL) {
					$LineColor = ["R" => $R + $LineSurrounding,"G" => $G + $LineSurrounding,"B" => $B + $LineSurrounding,"Alpha" => $Alpha];
				} elseif ($LineR != VOID) {
					$LineColor = ["R" => $LineR,"G" => $LineG,"B" => $LineB,"Alpha" => $LineAlpha];
				} else {
					$LineColor = $Color;
				}

				if ($PlotBorderSurrounding != NULL) {
					$PlotBorderColor = ["R" => $R + $PlotBorderSurrounding,"G" => $G + $PlotBorderSurrounding,"B" => $B + $PlotBorderSurrounding,"Alpha" => $PlotBorderAlpha];
				} else {
					$PlotBorderColor = ["R" => $PlotBorderR,"G" => $PlotBorderG,"B" => $PlotBorderB,"Alpha" => $PlotBorderAlpha];
				}

				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$PosArray = $this->myPicture->scaleComputeY($Serie["Data"], ["AxisID" => $Serie["Axis"]], TRUE);
				$YZero = $this->myPicture->scaleComputeY([1], ["AxisID" => $Serie["Axis"]]); // MOMCHIL FIX FOR THE INCIDENTS BY TYPE
				$this->myPicture->myData->Data["Series"][$SerieName]["XOffset"] = 0;
				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
					($YZero < $this->myPicture->GraphAreaY1 + 1) AND $YZero = $this->myPicture->GraphAreaY1 + 1;
					($YZero > $this->myPicture->GraphAreaY2 - 1) AND $YZero = $this->myPicture->GraphAreaY2 - 1;

					if ($XDivs == 0) {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / 4;
					} else {
						$XStep = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1 - $XMargin * 2) / $XDivs;
					}

					$X = $this->myPicture->GraphAreaX1 + $XMargin;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$Plots = [$X, $YZero];

					foreach($PosArray as $Key => $Height) {
						if ($Height != VOID) {
							$Plots[] = $X;
							$Plots[] = $YZero - $Height;
						}

						$X = $X + $XStep;
					}

					$Plots[] = $X - $XStep;
					$Plots[] = $YZero;
					$this->myPicture->drawPolygon($Plots, $Color);
					$this->myPicture->Shadow = $RestoreShadow;
					if ($DrawLine) {
						for ($i = 2; $i <= count($Plots) - 6; $i = $i + 2) {
							$this->myPicture->drawLine($Plots[$i], $Plots[$i + 1], $Plots[$i + 2], $Plots[$i + 3], $LineColor);
						}
					}

					if ($DrawPlot) {
						for ($i = 2; $i <= count($Plots) - 4; $i = $i + 2) {
							if ($PlotBorder != 0) {
								$this->myPicture->drawFilledCircle($Plots[$i], $Plots[$i + 1], $PlotRadius + $PlotBorder, $PlotBorderColor);
							}

							$this->myPicture->drawFilledCircle($Plots[$i], $Plots[$i + 1], $PlotRadius, $Color);
						}
					}

					$this->myPicture->Shadow = FALSE;
					
				} elseif ($Data["Orientation"] == SCALE_POS_TOPBOTTOM) {
					($YZero < $this->myPicture->GraphAreaX1 + 1) AND $YZero = $this->myPicture->GraphAreaX1 + 1;
					($YZero > $this->myPicture->GraphAreaX2 - 1) AND $YZero = $this->myPicture->GraphAreaX2 - 1;

					if ($XDivs == 0) {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / 4;
					} else {
						$YStep = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1 - $XMargin * 2) / $XDivs;
					}

					$Y = $this->myPicture->GraphAreaY1 + $XMargin;

					$PosArray = $this->myPicture->convertToArray($PosArray);
					
					$Plots = [$YZero, $Y];
					foreach($PosArray as $Key => $Height) {
						if ($Height != VOID) {
							$Plots[] = $YZero + $Height;
							$Plots[] = $Y;
						}

						$Y = $Y + $YStep;
					}

					$Plots[] = $YZero;
					$Plots[] = $Y - $YStep;
					$this->myPicture->drawPolygon($Plots, $Color);
					$this->myPicture->Shadow = $RestoreShadow;
					if ($DrawLine) {
						for ($i = 2; $i <= count($Plots) - 6; $i = $i + 2) {
							$this->myPicture->drawLine($Plots[$i], $Plots[$i + 1], $Plots[$i + 2], $Plots[$i + 3], $LineColor);
						}
					}

					if ($DrawPlot) {
						for ($i = 2; $i <= count($Plots) - 4; $i = $i + 2) {
							if ($PlotBorder != 0) {
								$this->myPicture->drawFilledCircle($Plots[$i], $Plots[$i + 1], $PlotRadius + $PlotBorder, $PlotBorderColor);
							}

							$this->myPicture->drawFilledCircle($Plots[$i], $Plots[$i + 1], $PlotRadius, $Color);
						}
					}

					$this->myPicture->Shadow = FALSE;
				}
			}
		}

		$this->myPicture->Shadow = $RestoreShadow;
	}

	function drawPolygonChart($Points, array $Format = [])
	{
		$R = isset($Format["R"]) ? $Format["R"] : 0;
		$G = isset($Format["G"]) ? $Format["G"] : 0;
		$B = isset($Format["B"]) ? $Format["B"] : 0;
		$Alpha = isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
		$Threshold = NULL;
		$NoFill = FALSE;
		$NoBorder = FALSE;
		$Surrounding = NULL;
		$BorderR = $R;
		$BorderG = $G;
		$BorderB = $B;
		$BorderAlpha = $Alpha / 2;
		
		extract($Format);
		
		if ($Surrounding != NULL) {
			$BorderR = $R + $Surrounding;
			$BorderG = $G + $Surrounding;
			$BorderB = $B + $Surrounding;
		}

		$RestoreShadow = $this->myPicture->Shadow;
		$this->myPicture->Shadow = FALSE;
		$AllIntegers = TRUE;
		for ($i = 0; $i <= count($Points) - 2; $i = $i + 2) {
			if ($this->myPicture->getFirstDecimal($Points[$i + 1]) != 0) {
				$AllIntegers = FALSE;
			}
		}

		/* Convert polygon to segments */
		$Segments = [];
		for ($i = 2; $i <= count($Points) - 2; $i = $i + 2) {
			$Segments[] = ["X1" => $Points[$i - 2],"Y1" => $Points[$i - 1],"X2" => $Points[$i],"Y2" => $Points[$i + 1]];
		}

		$Segments[] = ["X1" => $Points[$i - 2],"Y1" => $Points[$i - 1],"X2" => $Points[0],"Y2" => $Points[1]];
		/* Simplify straight lines */
		$Result = [];
		$inHorizon = FALSE;
		$LastX = VOID;
		foreach($Segments as $Key => $Pos) {
			if ($Pos["Y1"] != $Pos["Y2"]) {
				if ($inHorizon) {
					$inHorizon = FALSE;
					$Result[] = ["X1" => $LastX,"Y1" => $Pos["Y1"],"X2" => $Pos["X1"],"Y2" => $Pos["Y1"]];
				}

				$Result[] = ["X1" => $Pos["X1"],"Y1" => $Pos["Y1"],"X2" => $Pos["X2"],"Y2" => $Pos["Y2"]];
			} else {
				if (!$inHorizon) {
					$inHorizon = TRUE;
					$LastX = $Pos["X1"];
				}
			}
		}

		$Segments = $Result;
		/* Do we have something to draw */

		if (count($Segments) == 0) {
			return 0;
		}

		/* For segments debugging purpose */

		// foreach($Segments as $Key => $Pos)
		// echo $Pos["X1"].",".$Pos["Y1"].",".$Pos["X2"].",".$Pos["Y2"]."\r\n";

		/* Find out the min & max Y boundaries */
		$MinY = OUT_OF_SIGHT;
		$MaxY = OUT_OF_SIGHT;
		foreach($Segments as $Key => $Coords) {
			if ($MinY == OUT_OF_SIGHT || $MinY > min($Coords["Y1"], $Coords["Y2"])) {
				$MinY = min($Coords["Y1"], $Coords["Y2"]);
			}

			if ($MaxY == OUT_OF_SIGHT || $MaxY < max($Coords["Y1"], $Coords["Y2"])) {
				$MaxY = max($Coords["Y1"], $Coords["Y2"]);
			}
		}

		$YStep = ($AllIntegers) ? 1 : .5;
		$MinY = floor($MinY);
		$MaxY = floor($MaxY);
		/* Scan each Y lines */
		$DefaultColor = $this->myPicture->allocateColor($R, $G, $B, $Alpha);
		#$DebugLine = 0;
		$DebugColor = $this->myPicture->allocateColor(255, 0, 0, 100);
		$MinY = floor($MinY);
		$MaxY = floor($MaxY);
		$YStep = 1;
		if (!$NoFill) {

			// if ( $DebugLine ) { $MinY = $DebugLine; $MaxY = $DebugLine; }

			for ($Y = $MinY; $Y <= $MaxY; $Y = $Y + $YStep) {
				$Intersections = [];
				$LastSlope = NULL;
				$RestoreLast = "-";
				foreach($Segments as $Key => $Coords) {
					$X1 = $Coords["X1"];
					$X2 = $Coords["X2"];
					$Y1 = $Coords["Y1"];
					$Y2 = $Coords["Y2"];
					if (min($Y1, $Y2) <= $Y && max($Y1, $Y2) >= $Y) {
						if ($Y1 == $Y2) {
							$X = $X1;
						} else {
							$X = $X1 + (($Y - $Y1) * $X2 - ($Y - $Y1) * $X1) / ($Y2 - $Y1);
						}

						$X = floor($X);
						if ($X2 == $X1) {
							$Slope = "!";
						} else {
							$SlopeC = ($Y2 - $Y1) / ($X2 - $X1);
							if ($SlopeC == 0) {
								$Slope = "=";
							} elseif ($SlopeC > 0) {
								$Slope = "+";
							} elseif ($SlopeC < 0) {
								$Slope = "-";
							}
						}

						if (!is_array($Intersections)) {
							$Intersections[] = $X;
						} elseif (!in_array($X, $Intersections)) {
							$Intersections[] = $X;
						} elseif (in_array($X, $Intersections)) {
							#if ($Y == $DebugLine) {
							#	echo $Slope . "/" . $LastSlope . "(" . $X . ") ";
							#}

							if ($Slope == "=" && $LastSlope == "-") {
								$Intersections[] = $X;
							}

							if ($Slope != $LastSlope && $LastSlope != "!" && $LastSlope != "=") {
								$Intersections[] = $X;
							}

							if ($Slope != $LastSlope && $LastSlope == "!" && $Slope == "+") {
								$Intersections[] = $X;
							}
						}

						if (is_array($Intersections) && in_array($X, $Intersections) && $LastSlope == "=" && ($Slope == "-")) {
							$Intersections[] = $X;
						}

						$LastSlope = $Slope;
					}
				}

				if ($RestoreLast != "-") {
					$Intersections[] = $RestoreLast;
					echo "@" . $Y . "\r\n";
				}

				if (is_array($Intersections)) {
					sort($Intersections);
					#if ($Y == $DebugLine) {
					#	print_r($Intersections);
					#}

					/* Remove NULL plots */
					$Result = [];
					for ($i = 0; $i <= count($Intersections) - 1; $i = $i + 2) {
						if (isset($Intersections[$i + 1])) {
							if ($Intersections[$i] != $Intersections[$i + 1]) {
								$Result[] = $Intersections[$i];
								$Result[] = $Intersections[$i + 1];
							}
						}
					}

					// if ( is_array($Result) )

					if (count($Result) > 0) {
						$Intersections = $Result;
						$LastX = OUT_OF_SIGHT;
						foreach($Intersections as $Key => $X) {
							if ($LastX == OUT_OF_SIGHT) {
								$LastX = $X;
							} elseif ($LastX != OUT_OF_SIGHT) {
								if ($this->myPicture->getFirstDecimal($LastX) > 1) {
									$LastX++;
								}

								$Color = $DefaultColor;
								if ($Threshold != NULL) {
									foreach($Threshold as $Key => $Parameters) {
										if ($Y <= $Parameters["MinX"] && $Y >= $Parameters["MaxX"]) {
											$R = (isset($Parameters["R"])) ? $Parameters["R"] : 0;
											$G = (isset($Parameters["G"])) ? $Parameters["G"] : 0;
											$B = (isset($Parameters["B"])) ? $Parameters["B"] : 0;
											$Alpha = (isset($Parameters["Alpha"])) ? $Parameters["Alpha"] : 100;
											$Color = $this->myPicture->allocateColor($R, $G, $B, $Alpha);
										}
									}
								}

								imageline($this->myPicture->Picture, $LastX, $Y, $X, $Y, $Color);
								#if ($Y == $DebugLine) {
								#	imageline($this->myPicture->Picture, $LastX, $Y, $X, $Y, $DebugColor);
								#}

								$LastX = OUT_OF_SIGHT;
							}
						}
					}
				}
			}
		} # No Fill

		/* Draw the polygon border, if required */
		if (!$NoBorder) {
			foreach($Segments as $Key => $Coords) {
				$this->myPicture->drawLine($Coords["X1"], $Coords["Y1"], $Coords["X2"], $Coords["Y2"], ["R" => $BorderR,"G" => $BorderG,"B" => $BorderB,"Alpha" => $BorderAlpha,"Threshold" => $Threshold]);
			}
		}

		$this->myPicture->Shadow = $RestoreShadow;
	}
	
}

?>