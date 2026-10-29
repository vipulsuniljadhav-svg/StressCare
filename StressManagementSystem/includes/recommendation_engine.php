<?php
function getRecommendations(mysqli $c,string $emotion,string $situation,int $stress,int $limit=6):array{
 $s=$c->prepare("SELECT * FROM recommendation_rules WHERE emotion=? AND situation=? AND ? BETWEEN min_stress AND max_stress ORDER BY priority DESC,id LIMIT ?");
 $s->bind_param('ssii',$emotion,$situation,$stress,$limit);$s->execute();$r=$s->get_result();$out=[];while($x=$r->fetch_assoc())$out[]=$x;$s->close();
 if($out)return $out;
 $s=$c->prepare("SELECT * FROM recommendation_rules WHERE situation='General Stress' AND ? BETWEEN min_stress AND max_stress ORDER BY priority DESC,id LIMIT ?");
 $s->bind_param('ii',$stress,$limit);$s->execute();$r=$s->get_result();while($x=$r->fetch_assoc())$out[]=$x;$s->close();return $out;
}
?>
