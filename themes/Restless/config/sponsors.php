<?php

$this->assign('blockSponsorsTitle', $this->get('cfg')->get('blockSponsors.title'));

$i = 0;
$arrayTemp = array();

$elementWidth = 372;

$dbsSponsors = 'SELECT id, name, logo
              FROM '.SPONSORS_TABLE.'
              WHERE logo != "" ';
$dbeSponsors = mysql_query($dbsSponsors);

while ($dbrSponsors = mysql_fetch_assoc($dbeSponsors)) {
    $arrayTemp[$i]['link'] = 'index.php?file=News&amp;op=index_comment&news_id='.$dbrSponsors['id'];
    $arrayTemp[$i]['title'] = $dbrSponsors['name'];
    $arrayTemp[$i]['src'] = $dbrSponsors['logo'];
    $arrayTemp[$i]['id'] = 'id="RL_sponsorsElement'.($i+1).'"';
    $arrayTemp[$i]['current'] = null;
    $i++;
}

$count = count($arrayTemp);

$this->assign('totalWidth', $count * $elementWidth);

$arrayLeft = array();

$maxLeft = intval('-'.($count - 1) * $elementWidth);

$j = 0;
for ($i = 0; $i >= $maxLeft; $i -= $elementWidth) {
    $arrayLeft[$j] = $i;
    $j++;
}

$rand = rand(0, (count($arrayLeft) - 1));

$arrayTemp[$rand]['current'] = 'class="RL_sponsorsCurrent"';

$this->assign('sponsorsImages', $arrayTemp);

$this->assign('initLeft', $arrayLeft[$rand]);

$this->assign('nbSponsorsImages', $count);

$this->assign('elementWidth', $elementWidth);