<?php
// Algemene spullen
error_reporting (0);
if ($_GET['debug'] > 0)
	header ("Content-Type: text/plain");
else
	header ("Content-Type: image/png");

function hsltorgb ($h, $s, $l)
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
			$t += 1.0;
		else if ($t > 1)
			$t -= 1.0;

		if ($t < (1 / 6))
			$rgb[] = ($p + (($q - $p) * 6 * $t));
		else if (((1 / 6) <= $t) && ($t < 0.5))
			$rgb[] = $q;
		else if ((0.5 <= $t) && ($t < (2 / 3)))
			$rgb[] = ($p + (($q - $p) * 6 * ((2 / 3) - $t)));
		else
			$rgb[] = $p;
	}

	list ($r, $g, $b) = $rgb;
	$r = round (255 * $r);
	$g = round (255 * $g);
	$b = round (255 * $b);
	
	return array
	(
            'r' => $r,
            'g' => $g,
            'b' => $b
        );
}

function selectfromrange ($rangestr)
{
	$range = array ();
	$subranges = preg_split ("/ *,+ */", (string) $rangestr);
	
	foreach ($subranges as $subrange)
	{
		if (strpos ($subrange, "-") !== false)
			list ($start, $end) = str_replace ('n', '-', preg_split ("/ *-+ */", $subrange));
		else
			$start = $end = $subrange;
                
		$range = array_merge ($range, range ((int) $start, (int) $end));
	}
	return $range[mt_rand (0, count ($range) - 1)];
}

// HSLA range instellingen
$hue = "0-360,0-260,0-260";
$saturation = "30-100";
$lightness = "30-70";
$alpha = "0";

$images = array ('penguin', 'objection', 'snor', 'mario', 'arch');

if (strstr ($_SERVER['HTTP_REFERER'], 'digitalplace.nl') || ($_GET['dp'] > 0))
{
	//array_push ($images, 'dp');
	
	if (strstr ($_SERVER['HTTP_REFERER'], 't=5390'))
	{
		$images = array ('troll', 'dp-troll');
	}
	else if (strstr ($_SERVER['HTTP_REFERER'], 't=65') || ($_GET['3am'] > 0))
	{
		if (idate ('H', time ()) >= 3)
		{
			$images = array ('3am/teLaat.png', '3am/teLaat.png');
		}
		else
		{
			$uren = date ('H', time ());
			$uren = (int) $uren;

			if ($uren < 1)
				$images = array ('3am/1uur.png');
			else if ($uren < 2)
				$images = array ('3am/2uur.png');
			else if ($uren < 3)
				$images = array ('3am/3uur.png');
			else
				$images = array ('3am/teLaat.png');
		}
	}
	else if (strstr ($_SERVER['HTTP_REFERER'], 't=5392') || ($_GET['nyan'] > 0))
	{
		$images = array ('nyan/nyan-troll.png', 'nyan/nyan-why-u-no-nyan.png', 'nyan/nyan-why-u-no-nyan-2.png'/*, 'nyan/nyan-why-u-no-nyan-3.png'*/);
	}
}

$choice = array_rand ($images);
$image = $images[$choice];

// Core script
if (in_array ($image, array ('robin', 'snor', 'troll', 'arch', 'ubuntu', 'qrrobinjbe')))
{
	$back = imagecreatefrompng ('./' . $image . '/' . $image . '.png');
	imagealphablending ($back, true);
	
	$color = array_values (hsltorgb (selectfromrange ($hue), selectfromrange ($saturation), selectfromrange ($lightness)));
	$color[] = floor (selectfromrange ($alpha) / 100 * 127);
	
	imagefilter ($back, IMG_FILTER_COLORIZE, $color[0], $color[1], $color[2], $color[3]);
	imagesavealpha ($back, true);
}
else if (strstr ($image, '3am/') || strstr ($image, 'nyan/'))
{
	$back = imagecreatefrompng ($image);
	imagealphablending ($back, true);
	
	$color = array_values (hsltorgb(selectfromrange ($hue), selectfromrange ($saturation), selectfromrange ($lightness)));
	$color[] = floor (selectfromrange ($alpha) / 100 * 127);
	
	imagefilter ($back, IMG_FILTER_COLORIZE, $color[0], $color[1], $color[2], $color[3]);
	imagesavealpha ($back, true);
}
else
{
	$flip = (mt_rand(0, 1) == 1) ? '-hflip' : ''; // Willekeurig bepalen of afbeelding geflipt gaat worden
	$back = imagecreatefrompng ('./' . $image . '/' . $image . '-back' . $flip . '.png'); // Achtergrond importeren
	imagealphablending ($back, true); // Transparantie instellen
	
	$color = array_values (hsltorgb (selectfromrange ($hue), selectfromrange ($saturation), selectfromrange ($lightness))); // RGB kleur genereren op basis van HSL ranges
	$color[] = floor (selectfromrange ($alpha) / 100 * 127); // Alphakanaal toevoegen
	
	imagefilter ($back, IMG_FILTER_COLORIZE, $color[0], $color[1], $color[2], $color[3]); // In de blender gooien
	imagesavealpha ($back, true); // Alphakanaal opslaan

	$front = imagecreatefrompng ('./' . $image . '/' . $image . '-front' . $flip . '.png'); // Voorgrond importeren
	imagecopy ($back, $front, 0, 0, 0, 0, imagesx ($back), imagesy ($back)); // Voorgrond over achtergrond plakken
	imagedestroy ($front); // En weer weggooien
	imagesavealpha ($back, true); // Alphakanaal opslaan
}

imagepng ($back); // Afbeelding outputten
imagedestroy ($back); // En weggooien
?>
