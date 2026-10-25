// Poll only the signed-in user's notifications and application statuses.
async function refreshUpdates(){
 if(document.hidden)return;
 try{
  const response=await fetch('index.php?poll=1',{headers:{Accept:'application/json'}});
  if(!response.ok || !response.headers.get('content-type')?.includes('application/json'))return;
  const data=await response.json();
  const box=document.getElementById('notifications');
  if(box && data.notifications.length){
   box.replaceChildren(...data.notifications.map(n=>{const p=document.createElement('p');p.className='notification';p.textContent=n.message;const s=document.createElement('small');s.textContent=n.created_at;p.append(s);return p;}));
  }
  for(const a of data.applications){const target=document.querySelector(`[data-application="${a.id}"]`);if(target){const b=document.createElement('span');b.className='badge '+a.status.toLowerCase();b.textContent=a.status;target.replaceChildren(b);}}
 }catch{ /* A later poll retries transient network failures. */ }
}
setInterval(refreshUpdates,10000);
