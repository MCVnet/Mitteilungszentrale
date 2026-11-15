<?php
declare(strict_types=1);
const VARIABLE_PRESENTATION_LEGACY='legacy'; const VARIABLE_PRESENTATION_VALUE_PRESENTATION='value';
const IPS_KERNELMESSAGE=1;const VM_UPDATE=2;const VM_DELETE=3;const KR_READY=10103;const KL_WARNING=102;
$GLOBALS['calls']=[];$GLOBALS['semaphores']=[];$GLOBALS['fail']=false;$GLOBALS['reenter']=false;
function IPS_SetHidden($id,$hidden){}
function IPS_SetProperty($id,$name,$value){$GLOBALS['m']->p[$name]=$value;}
function IPS_ApplyChanges($id){$GLOBALS['m']->ApplyChanges();}
function IPS_GetKernelRunlevel(){return KR_READY;}
function IPS_VariableExists($id){return $id===10;}
function IPS_GetParent($id){return 100;}
function IPS_GetName($id){return $id===100?'EG | Büro links | Fenster-offen':'Value';}
function IPS_GetVariable($id){return ['VariableType'=>0];}
function IPS_InstanceExists($id){return $id===20;}
function IPS_GetInstance($id){return ['ModuleInfo'=>['ModuleID'=>'{26F752F0-4D4E-A52C-DB3C-35DFEA979F44}'],'InstanceStatus'=>102];}
function IPS_SemaphoreEnter($key,$timeout){if($GLOBALS['semaphores'][$key]??false)return false;$GLOBALS['semaphores'][$key]=true;return true;}
function IPS_SemaphoreLeave($key){$GLOBALS['semaphores'][$key]=false;}
function DS_Play($device,$text){$GLOBALS['calls'][]=[$device,$text];if(isset($GLOBALS['duringPlay']))($GLOBALS['duringPlay'])();if($GLOBALS['reenter'])$GLOBALS['m']->ProcessAnnouncements();return $GLOBALS['fail']?false:null;}
class IPSModuleStrict{
 public array $p=[],$a=[],$b=[],$v=[],$timers=[],$logs=[];public int $status=0;protected int $InstanceID=1;
 public function Create():void{}public function ApplyChanges():void{}
 public function __call($name,$args){
  if(str_starts_with($name,'RegisterProperty')){$this->p[$args[0]]??=$args[1];return;}
  if(str_starts_with($name,'RegisterAttribute')){$this->a[$args[0]]??=$args[1];return;}
  if(str_starts_with($name,'ReadProperty'))return $this->p[$args[0]];
  if(str_starts_with($name,'ReadAttribute'))return $this->a[$args[0]];
  if(str_starts_with($name,'WriteAttribute')){$this->a[$args[0]]=$args[1];return;}
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
