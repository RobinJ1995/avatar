<?php
// Algemene spullen
error_reporting(0);
if ($_GET['debug'] > 0)
{
	header("Content-Type: text/plain");
}
else
{
	header("Content-Type: image/png");
}

function hsltorgb($h, $s, $l)
{
	$h /= 360;
	$s /= 100;
	$l /= 100;

	$q = ($l < 0.5) ? ($l * (1 + $s)) : ($l + $s - ($l * $s));
	$p = ((2 * $l) - $q);

	$rgb = array();
	for ($i = 0; $i < 3; $i++)
	{
		switch ($i)
		{
			case 0: $t = ($h + (1 / 3)); break;
			case 1: $t = $h; break;
			case 2: $t = ($h - (1 / 3)); break;
		}

		if ($t < 0)
		{
			$t += 1.0;
		}
		if ($t > 1)
		{
			$t -= 1.0;
		}

		if ($t < (1 / 6))
		{
			$rgb[] = ($p + (($q - $p) * 6 * $t));
		}
		else if (((1 / 6) <= $t) && ($t < 0.5))
		{
			$rgb[] = $q;
		}
		else if ((0.5 <= $t) && ($t < (2 / 3)))
		{
			$rgb[] = ($p + (($q - $p) * 6 * ((2 / 3) - $t)));
		}
		else
		{
			$rgb[] = $p;
		}
	}

	list($r, $g, $b) = $rgb;
	$r = round(255 * $r);
	$g = round(255 * $g);
	$b = round(255 * $b);
	return array("r" => $r, "g" => $g, "b" => $b);
}

function selectfromrange($rangestr)
{
	$range = array();
	$subranges = preg_split("/ *,+ */", (string) $rangestr);
	foreach ($subranges as $subrange)
	{
		if (strpos($subrange, "-") !== false)
		{
			list($start, $end) = str_replace("n", "-", preg_split("/ *-+ */", $subrange));
		}
		else
		{
			$start = $end = $subrange;
		}
		$range = array_merge($range, range((int) $start, (int) $end));
	}
	return $range[mt_rand(0, count($range) - 1)];
}

// HSLA range instellingen
$hue = "0-360,0-260,0-260";
$saturation = "30-100";
$lightness = "30-70";
$alpha = "0";

// Core script
$back = imagecreatefrompng('snor_64px/snor_64px.png'); // Achtergrond importeren
imagealphablending($back, true); // Transparantie instellen
$color = array_values(hsltorgb(selectfromrange($hue), selectfromrange($saturation), selectfromrange($lightness))); // RGB kleur genereren op basis van HSL ranges
$color[] = floor(selectfromrange($alpha) / 100 * 127); // Alphakanaal toevoegen
imagefilter($back, IMG_FILTER_COLORIZE, $color[0], $color[1], $color[2], $color[3]); // In de blender gooien
imagesavealpha($back, true); // Alphakanaal opslaan

imagepng($back); // Afbeelding outputten
imagedestroy($back); // En weggooien
?>
