<?php

//echo rawurlencode('ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC+UTjmuw2Ds5/2oLCynEigyQZ3E5ExzO8f0Btg5NWJiiGPSWcR5sJrUOOpMevm+X7c1hY+CaAiH2Cx/Ept1n/e+YVQcR6aRcqwYGX1BtLUu/RwIGo0F8JOSjzMTYAeqJKimu0wI0NM8kXVQPZAcsNGvZTrep/iSlgZR3ivS6ySJ2Y3G2frtRmzXRjHrVghTlkSvY6Euqd3kclfXEuY//bW0P2XWTiAmcT0PjhGGYLbYwfFY7w/7TT7tVX7Q5WNyo7XRiH6nYSw2k9WM2WruI8bgeREJ9IFLVKCoj7p6w3oYNZ4v7gMPZCpCIT5cl4fLt9CvrTfKjVlzimKeqVgqkqEWS/jpF1oheAcTbPP5lLqPryq+4UHtPlOzyZY52YQtftCocl+mvAJ6k9nLW2S6pXYPDkGAiJsJki4QJ8O8PUzLNsc9gb+3tN58wXzAbQbcXOuDMbwEiKqRwEj1BEyTZ09x2Df0Knp2LHlB89mmBwoXfTlVIgTo46+gp2RcRkGe9M= ioannapsylla@dhcp-10-201-255-86.clients.net.dtu.dk')

function getGithubContent($uri)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

header('Content-Type:application/json');
$url = "https://github.com/deic-dk/pod_manifests";


$res = getGithubContent($url);
//print_r($res->{'full_description'});
$dom = new DomDocument();
$dom->loadHTML($res, LIBXML_NOERROR);

$finder = new DomXPath($dom);
$classname="js-navigation-open Link--primary";
$nodes = $finder->query("//*[contains(@class, '$classname')]");
//$child_elements = $table->getElementsByTagName('tr'); //DOMNodeList
//$row_count = $child_elements->length - 1;
foreach( $nodes as $elem ) {
    $test = $elem->textContent;
//    echo $test;
}

echo gethostname();

?>

