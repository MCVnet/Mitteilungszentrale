<?php
declare(strict_types=1);
const VARIABLE_PRESENTATION_LEGACY='legacy'; const VARIABLE_PRESENTATION_VALUE_PRESENTATION='value';
const IPS_KERNELMESSAGE=1;const VM_UPDATE=2;const VM_DELETE=3;const KR_READY=10103;const KL_WARNING=102;
$GLOBALS['calls']=[];$GLOBALS['semaphores']=[];$GLOBALS['fail']=false;$GLOBALS['reenter']=false;
function IPS_SetHidden($id,$hidden){}
function IPS_SetProperty($id,$name,$value){if($id===20){$GLOBALS['deviceProps'][$name]=$value;}else{$GLOBALS['m']->p[$name]=$value;}}
function IPS_GetProperty($id,$name){return $GLOBALS['deviceProps'][$name];}
function IPS_HasChanges($id){return $GLOBALS['pendingDeviceChanges']??false;}
function IPS_ApplyChanges($id){if($id!==20)$GLOBALS['m']->ApplyChanges();}
function IPS_GetKernelRunlevel(){return KR_READY;}
function IPS_VariableExists($id){return $id===10;}
function IPS_GetParent($id){return 100;}
function IPS_GetName($id){return $id===100?'EG | Büro links | Fenster-offen':'Value';}
function IPS_GetVariable($id){return ['VariableType'=>0];}
function IPS_InstanceExists($id){return !in_array($id,$GLOBALS['missingInstances']??[],true)&&in_array($id,[20,30,40],true);}
function IPS_ObjectExists($id){return in_array($id,[1,90],true);}
function IPS_GetInstanceListByModuleID($id){return $id==='{B5B875BB-9B76-45FD-4E67-2607E45B3AC4}'?[30]:($id==='{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}'?[40]:[]);}
function VISU_PostNotification($id,$title,$text,$type,$target){$GLOBALS['push'][]=['tile',$id,$title,$text,$target];return ($GLOBALS['pushFail']??false)?false:123;}
function WFC_PushNotification($id,$title,$text,$type,$target){$GLOBALS['push'][]=['webfront',$id,$title,$text,$target];return true;}
function IPS_GetInstance($id){return ['ModuleInfo'=>['ModuleID'=>[20=>'{26F752F0-4D4E-A52C-DB3C-35DFEA979F44}',30=>'{B5B875BB-9B76-45FD-4E67-2607E45B3AC4}',40=>'{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}'][$id]],'InstanceStatus'=>102];}
function IPS_SemaphoreEnter($key,$timeout){if($GLOBALS['semaphores'][$key]??false)return false;$GLOBALS['semaphores'][$key]=true;return true;}
function IPS_SemaphoreLeave($key){$GLOBALS['semaphores'][$key]=false;}
function DS_Play($device,$text){$GLOBALS['calls'][]=[$device,$text,$GLOBALS['deviceProps']['SonosVolume']??null,$GLOBALS['deviceProps']['MediaPlayerVolume']??null];if(isset($GLOBALS['duringPlay']))($GLOBALS['duringPlay'])();if($GLOBALS['reenter'])$GLOBALS['m']->ProcessAnnouncements();return $GLOBALS['fail']?false:null;}
class IPSModuleStrict{
 public array $p=[],$a=[],$b=[],$v=[],$timers=[],$logs=[],$debug=[];public int $status=0;protected int $InstanceID=1;
 public function Create():void{}public function ApplyChanges():void{}
 public function __call($name,$args){
  if(str_starts_with($name,'RegisterProperty')){$this->p[$args[0]]??=$args[1];return;}
  if(str_starts_with($name,'RegisterAttribute')){$this->a[$args[0]]??=$args[1];return;}
  if(str_starts_with($name,'ReadProperty'))return $this->p[$args[0]];
  if(str_starts_with($name,'ReadAttribute'))return $this->a[$args[0]];
  if(str_starts_with($name,'WriteAttribute')){$this->a[$args[0]]=$args[1];return;}
  if($name==='SendDebug'){$this->debug[]=$args;return;}
  if($name==='GetBufferList'||$name==='GetReferenceList')return [];
  if($name==='GetBuffer')return $this->b[$args[0]]??'';
  if($name==='SetBuffer'){$this->b[$args[0]]=$args[1];return;}
  if($name==='SetValue'){$this->v[$args[0]]=$args[1];return;}
  if($name==='GetValue')return $this->v[$args[0]]??'';
  if($name==='GetIDForIdent')return 200;
  if($name==='SetTimerInterval'){$this->timers[$args[0]]=$args[1];return;}
  if($name==='SetStatus'){$this->status=$args[0];return;}
  if($name==='LogMessage'){$this->logs[]=$args[0];return;}
 }
}
require __DIR__ . '/../VisuMessages/module.php';
function check($ok,$label){if(!$ok)throw new RuntimeException($label);echo "OK: $label\n";}
function setup($announce=true,$repeats=2){
 $m=new VisuMessages();$GLOBALS['m']=$m;$m->Create();$m->p['UseVariableIcons']=false;
 $m->p['Rules']=json_encode([['Enabled'=>true,'VariableID'=>10,'Operator'=>'eq','Value'=>'true','Text'=>'{parent} · {name}: {value}','Announce'=>$announce,'AnnouncementID'=>20,'AnnouncementRepeats'=>$repeats,'AnnouncementInterval'=>60]]);
 $m->ApplyChanges();return $m;
}
function trigger($m){$m->MessageSink(time(),10,VM_UPDATE,[true,true,false]);}
function due($m){$q=json_decode($m->a['AnnouncementQueue'],true);foreach($q as &$j)$j['due']=time()-1;$m->a['AnnouncementQueue']=json_encode($q);}
$m=setup();trigger($m);check(count($GLOBALS['calls'])===0,'trigger queues audio without synchronous playback');
$m->ProcessAnnouncements();check(count($GLOBALS['calls'])===1,'first playback');check($GLOBALS['calls'][0][1]==='EG | Büro links | Fenster-offen · Value: true','resolved parent and variable text passed to DS_Play');
$m->ProcessAnnouncements();check(count($GLOBALS['calls'])===1,'repeat waits for interval');
due($m);$m->ProcessAnnouncements();due($m);$m->ProcessAnnouncements();check(count($GLOBALS['calls'])===3,'two repeats produce three total outputs');check($m->a['AnnouncementQueue']==='[]'&&$m->timers['Announcements']===0,'finished queue stops timer');
$m=setup();trigger($m);$m->RequestAction('Delete',1);check($m->a['AnnouncementQueue']==='[]','single delete cancels pending playback');
$m=setup();trigger($m);$m->RequestAction('Pin',1);check(count(json_decode($m->a['AnnouncementQueue'],true))===1,'pin does not cancel repeats');$m->RequestAction('Unpin',1);$m->RequestAction('Clear',0);check($m->a['AnnouncementQueue']==='[]','bulk clear cancels pending playback');
$m=setup();trigger($m);$m->ApplyChanges();check($m->a['AnnouncementQueue']==='[]','configuration/restart cancels old playback');
$m=setup(false);trigger($m);check($m->a['AnnouncementQueue']==='[]'&&$m->v['MessageCount']===1,'disabled audio keeps visual message only');
$m=setup();trigger($m);$GLOBALS['reenter']=true;$before=count($GLOBALS['calls']);$m->ProcessAnnouncements();$GLOBALS['reenter']=false;check(count($GLOBALS['calls'])===$before+1,'overlapping worker cannot duplicate playback');
$m=setup();trigger($m);$GLOBALS['fail']=true;$m->ProcessAnnouncements();$GLOBALS['fail']=false;check($m->a['AnnouncementQueue']==='[]'&&count($m->logs)===1&&$m->v['MessageCount']===1,'audio failure stops repeats, logs warning, preserves message');
$m=setup(true,101);check($m->status===200,'invalid repeat count rejected');
$m=setup();trigger($m);$m->MessageSink(time(),10,VM_UPDATE,[true,true,true]);check($m->v['MessageCount']===1,'no duplicate message while condition stays true');

$m=setup();$rules=json_decode($m->p['Rules'],true);foreach(['Announce','AnnouncementID','AnnouncementRepeats','AnnouncementInterval'] as $key)unset($rules[0][$key]);$m->p['Rules']=json_encode($rules);$m->ApplyChanges();$rules=json_decode($m->p['Rules'],true);check($rules[0]['Announce']===false&&$rules[0]['AnnouncementRepeats']===0,'legacy rules receive disabled audio defaults');
$m=setup();trigger($m);$GLOBALS['duringPlay']=fn()=>$m->RequestAction('Delete',1);$m->ProcessAnnouncements();unset($GLOBALS['duringPlay']);check($m->a['AnnouncementQueue']==='[]','deletion during device call cannot requeue repeats');
$m=setup();$m->p['Limit']=1;$m->ApplyChanges();trigger($m);trigger($m);check(count(json_decode($m->a['AnnouncementQueue'],true))===1,'storage limit also prunes pending audio');

$GLOBALS['deviceProps']=['OutputType'=>0,'SonosVolume'=>'17','MediaPlayerVolume'=>25];
$m=setup();$rules=json_decode($m->p['Rules'],true);$rules[0]['AnnouncementOwnVolume']=true;$rules[0]['AnnouncementVolume']=65;$m->p['Rules']=json_encode($rules);$m->ApplyChanges();trigger($m);$m->ProcessAnnouncements();$last=end($GLOBALS['calls']);check($last[2]==='65'&&$GLOBALS['deviceProps']['SonosVolume']==='17','Sonos volume applied to call and original setting restored');
$data=json_decode($m->v['MessageData'],true);check($data['messages'][0]['announcement']['volume']===65&&$data['messages'][0]['announcement']['repeats']===2,'saved message includes volume and repeat metadata');
$GLOBALS['fail']=true;due($m);$m->ProcessAnnouncements();$GLOBALS['fail']=false;check($GLOBALS['deviceProps']['SonosVolume']==='17','volume restored on playback failure');
$GLOBALS['deviceProps']['OutputType']=1;$m=setup();$rules=json_decode($m->p['Rules'],true);$rules[0]['AnnouncementOwnVolume']=true;$rules[0]['AnnouncementVolume']=40;$m->p['Rules']=json_encode($rules);$m->ApplyChanges();trigger($m);$m->ProcessAnnouncements();$last=end($GLOBALS['calls']);check($last[3]===40&&$GLOBALS['deviceProps']['MediaPlayerVolume']===25,'MediaPlayer volume applied and module setting restored');
$m=setup();$rules=json_decode($m->p['Rules'],true);$rules[0]['AnnouncementOwnVolume']=true;$rules[0]['AnnouncementVolume']=0;$m->p['Rules']=json_encode($rules);$m->ApplyChanges();check($m->status===200,'unsupported zero volume rejected instead of meaning no change on Sonos');

function pushRule($m,$visu=30,$target=0){$r=json_decode($m->p['Rules'],true);$r[0]['PushEnabled']=true;$r[0]['PushVisuID']=$visu;$r[0]['PushTitle']='Fenstermeldung';$r[0]['PushTargetID']=$target;$m->p['Rules']=json_encode($r);$m->ApplyChanges();}
$GLOBALS['push']=[];
$m=setup(false);pushRule($m);trigger($m);check(count($GLOBALS['push'])===0,'push queued without synchronous send');$m->ProcessAnnouncements();check(count($GLOBALS['push'])===1&&$GLOBALS['push'][0][0]==='tile'&&$GLOBALS['push'][0][4]===1,'tile push uses selected visualization and module default target');check($GLOBALS['push'][0][3]==='EG | Büro links | Fenster-offen · Value: true','push contains fully resolved message text');$m->ProcessAnnouncements();check(count($GLOBALS['push'])===1,'push sent only once');
$m=setup(false);pushRule($m,40,90);trigger($m);$m->ProcessAnnouncements();$last=end($GLOBALS['push']);check($last[0]==='webfront'&&$last[4]===90,'WebFront push supports explicit target');
$m=setup(false);pushRule($m);trigger($m);$m->RequestAction('Delete',1);$n=count($GLOBALS['push']);$m->ProcessAnnouncements();check(count($GLOBALS['push'])===$n,'deleted message cancels queued push');
$m=setup();pushRule($m);trigger($m);$n=count($GLOBALS['push']);$m->ProcessAnnouncements();$m->ProcessAnnouncements();due($m);$m->ProcessAnnouncements();due($m);$m->ProcessAnnouncements();check(count($GLOBALS['push'])===$n+1,'audio repetitions do not repeat push');
$m=setup();pushRule($m);trigger($m);$GLOBALS['pushFail']=true;$m->ProcessAnnouncements();$GLOBALS['pushFail']=false;$n=count($GLOBALS['calls']);$m->ProcessAnnouncements();check(count($GLOBALS['calls'])===$n+1&&count($m->logs)===1,'push failure preserves independent announcement');
$m=setup(false);pushRule($m);$r=json_decode($m->p['Rules'],true);$r[0]['PushTitle']=str_repeat('Ä',40);$r[0]['Text']=str_repeat('Ö',300);$m->p['Rules']=json_encode($r);$m->ApplyChanges();trigger($m);$m->ProcessAnnouncements();$last=end($GLOBALS['push']);preg_match_all('/./us',$last[2],$title);preg_match_all('/./us',$last[3],$text);check(count($title[0])===32&&count($text[0])===256,'push limits preserve Unicode characters');check(strlen(json_decode($m->v['MessageData'],true)['messages'][0]['text'])===600,'stored message retains full text');
$m=setup(false);pushRule($m,20);trigger($m);check($m->status===102&&$m->v['MessageCount']===1&&$m->a['AnnouncementQueue']==='[]','invalid optional recipient skips push but retains visual message');
$m=setup(false);$form=json_decode($m->GetConfigurationForm(),true);$section=end($form['elements'][0]['form']);check(count($section['items'][1]['options'])===3,'recipient selector lists tile and WebFront instances');

$m=setup(false);pushRule($m);$r=json_decode($m->p['Rules'],true);$r[0]['PushRepeats']=2;$r[0]['PushInterval']=90;$m->p['Rules']=json_encode($r);$m->ApplyChanges();trigger($m);$before=count($GLOBALS['push']);$m->ProcessAnnouncements();$queue=json_decode($m->a['AnnouncementQueue'],true);check($queue[0]['remaining']===2&&$queue[0]['due']>=time()+89,'push repeats use their own count and 90-second interval');$m->ProcessAnnouncements();check(count($GLOBALS['push'])===$before+1,'push repeat does not run before interval');due($m);$m->ProcessAnnouncements();due($m);$m->ProcessAnnouncements();check(count($GLOBALS['push'])===$before+3&&$m->a['AnnouncementQueue']==='[]','two push repeats produce three total sends and stop');
$m=setup(false);pushRule($m);$r=json_decode($m->p['Rules'],true);$r[0]['PushRepeats']=101;$m->p['Rules']=json_encode($r);$m->ApplyChanges();check($m->status===200,'excessive push repeat count rejected');
$m=setup(false);pushRule($m);$r=json_decode($m->p['Rules'],true);$r[0]['PushInterval']=0;$m->p['Rules']=json_encode($r);$m->ApplyChanges();check($m->status===200,'zero push interval rejected');

$GLOBALS['missingInstances']=[20];$m=setup();trigger($m);check($m->status===102&&$m->v['MessageCount']===1&&$m->a['AnnouncementQueue']==='[]','missing announcement instance does not block message rules');check(count(json_decode($m->b['AvailabilityInfo'],true))===1,'missing announcement produces settings hint');
$GLOBALS['missingInstances']=[30];$m=setup(false);pushRule($m);trigger($m);check($m->status===102&&$m->v['MessageCount']===1&&$m->a['AnnouncementQueue']==='[]','missing push visualization does not block message rules');$GLOBALS['missingInstances']=[];
$m=setup(false);trigger($m);check($m->debug===[],'debug disabled by default');$m->p['DebugEnabled']=true;$m->ApplyChanges();trigger($m);check(count($m->debug)>0,'debug enabled emits message diagnostics');check(!str_contains(json_encode($m->debug),'Fenster-offen'),'debug metadata does not contain message text');$m->p['DebugEnabled']=false;$m->debug=[];$m->ApplyChanges();trigger($m);check($m->debug===[],'debug can be switched off again');
$m=setup(false);$m->p['HeadingTitle']='Meine Hinweise';$m->p['ShowHeading']=false;$m->ApplyChanges();$appearance=(new ReflectionMethod($m,'appearance'))->invoke($m);check($appearance['heading']['title']==='Meine Hinweise'&&$appearance['heading']['visible']===false,'heading settings included in visualization appearance');
