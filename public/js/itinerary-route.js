(() => {
 document.querySelectorAll('[data-itinerary-map]').forEach(root=>{
  let stops,startStop,trail;try{stops=JSON.parse(root.querySelector('[data-route-data]').textContent);startStop=JSON.parse(root.dataset.routeStart||'null')}catch{return}if(!stops.length)return;
  try{trail=JSON.parse(root.querySelector('[data-route-trail]')?.textContent||'null')}catch{trail=null}
  const canvas=root.querySelector('[data-route-canvas]'),play=root.querySelector('[data-route-play]'),reset=root.querySelector('[data-route-reset]'),tabs=[...root.querySelectorAll('[data-route-stop]')];
  const valid=s=>s&&Number.isFinite(s.latitude)&&Number.isFinite(s.longitude),reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
  const walking=root.dataset.routeType==='mountain';
  const travelerSvg=walking
   ? '<svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="27" cy="8" r="5" fill="currentColor"/><g fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M24 17l-3 12"/><path class="route-walk-arm" d="M24 18l8 7 7-1"/><path class="route-walk-leg" d="M21 29l-8 8-4 7"/><path class="route-walk-leg route-walk-leg-back" d="M21 29l8 7 3 8"/><path d="M38 23l-4 21" stroke-width="2"/></g><path d="M15 15l7 2-3 12-8-2z" fill="#dc704f"/></svg>'
   : '<svg viewBox="0 0 56 40" aria-hidden="true"><g class="route-car-body"><path d="M4 16h6l5-10h21l8 10h7v15H4z" fill="currentColor"/><path d="M18 9h8v8H14zm11 0h6l6 8H29z" fill="#faf9f5"/><path d="M12 3h26M6 22h4m36 0h5" stroke="#dc704f" stroke-width="3"/><path d="M27 20v8" stroke="#faf9f5" stroke-width="2"/></g><g fill="#203d2e" stroke="#faf9f5" stroke-width="3"><circle cx="14" cy="31" r="6"/><circle cx="42" cy="31" r="6"/></g></svg>';
  let selected=0,playing=false,frame=0,startedAt=0,elapsed=0,map,traveler,line;
  const allPinned=stops.every(valid),known=stops.filter(valid),points=[...(valid(startStop)?[startStop]:[]),...stops];
  const hasStart=valid(startStop);let duration=Math.min(30000,Math.max(5000,(points.length-1)*2400));
  // Walk every bend of a supplied trail, with distance-based timing rather than vertex count.
  const traced=Array.isArray(trail)&&trail.length===stops.length&&trail.every(s=>Array.isArray(s.coordinates)&&s.coordinates.length>1&&s.coordinates.every(p=>Array.isArray(p)&&p.length===2&&p.every(Number.isFinite)));
  if(traced)duration=60000;
  const path=[],dayEnds=[];
  if(traced){trail.forEach(segment=>{segment.coordinates.forEach(p=>{const last=path[path.length-1];if(!last||last[0]!==p[0]||last[1]!==p[1])path.push(p)});dayEnds.push(path.length-1)})}
  else{points.forEach(p=>path.push([p.latitude,p.longitude]));stops.forEach((s,i)=>dayEnds.push(i+(hasStart?1:0)))}
  const distances=[0];for(let i=1;i<path.length;i++){const a=path[i-1],b=path[i],dx=(b[1]-a[1])*Math.cos((a[0]+b[0])*Math.PI/360),dy=b[0]-a[0];distances.push(distances[i-1]+Math.hypot(dx,dy))}
  const length=distances[distances.length-1]||0;
  const dayProgress=dayEnds.map(i=>length?distances[i]/length:0);
  function select(index,open=false){selected=index;const stop=stops[index];root.querySelector('[data-route-day-label]').textContent=`DAY ${stop.day}`;root.querySelector('[data-route-current-title]').textContent=stop.title;root.querySelector('[data-route-current-location]').textContent=stop.label||stop.title;const img=root.querySelector('[data-route-photo]');img.hidden=!stop.photo;if(stop.photo){img.src=stop.photo;img.alt=`Day ${stop.day}: ${stop.title}`}tabs.forEach((tab,i)=>tab.setAttribute('aria-pressed',String(i===index)));root.querySelector('[data-route-status]').textContent=`Day ${stop.day}: ${stop.label||stop.title}`;if(open){const day=document.querySelector(`[data-itinerary-day="${index}"]`);if(day)day.open=true;}}
  function pause(){playing=false;root.classList.remove('route-is-playing');cancelAnimationFrame(frame);play.textContent='▶ Play journey';play.setAttribute('aria-pressed','false')}
  function paint(progress){
   if(!traveler||!allPinned)return;
   if(!length){traveler.setLatLng(path[0]);return}
   const target=Math.max(0,Math.min(1,progress))*length;
   let low=0,high=path.length-1;while(low+1<high){const mid=(low+high)>>1;if(distances[mid]<=target)low=mid;else high=mid}
   const index=Math.min(path.length-2,low),a=path[index],b=path[index+1],span=distances[index+1]-distances[index],fraction=span?(target-distances[index])/span:0;
   const position=[a[0]+(b[0]-a[0])*fraction,a[1]+(b[1]-a[1])*fraction];
   traveler.setLatLng(position);map.panInside(position,{paddingTopLeft:[70,80],paddingBottomRight:[70,60],animate:false});line.setLatLngs([...path.slice(0,index+1),position]);
   const icon=traveler.getElement()?.querySelector('.route-traveler-facing');if(icon&&b[1]!==a[1])icon.style.transform=b[1]<a[1]?'scaleX(-1)':'scaleX(1)';
  }
  function tick(time){if(!playing)return;if(!startedAt)startedAt=time-elapsed;elapsed=time-startedAt;const progress=Math.min(1,elapsed/duration);paint(progress);const index=Math.max(0,dayProgress.findIndex(end=>end>=progress));if(index!==selected)select(index);if(progress===1){select(stops.length-1);pause();elapsed=duration;startedAt=0;return}frame=requestAnimationFrame(tick)}
  function begin(){if(playing){pause();return}if(elapsed>=duration){elapsed=0;select(0)}playing=true;map.panTo(traveler.getLatLng(),{animate:false});root.classList.add('route-is-playing');startedAt=0;play.textContent='Ⅱ Pause journey';play.setAttribute('aria-pressed','true');frame=requestAnimationFrame(tick)}
  select(0);
  if(!window.L){canvas.classList.add('route-map-unavailable');canvas.textContent='The map could not load. Check your internet connection and reload this page.';root.querySelector('[data-route-mode]').textContent='Route map unavailable';return;}
  map=L.map(canvas,{scrollWheelZoom:false,zoomControl:false});L.control.zoom({position:'topright'}).addTo(map);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:18,attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}).addTo(map);
  const locations=[...(hasStart?[startStop]:[]),...known],positions=traced?path:locations.map(s=>[s.latitude,s.longitude]);
  if(positions.length)map.fitBounds(L.latLngBounds(positions),{padding:[50,55],maxZoom:traced?13:10});else map.setView([0,25],3);
  if(allPinned){L.polyline(path,{color:'#203d2e',weight:4,opacity:.65,dashArray:'5 8'}).addTo(map);line=L.polyline([],{color:'#dc704f',weight:4}).addTo(map);traveler=L.marker([points[0].latitude,points[0].longitude],{interactive:false,keyboard:false,zIndexOffset:1000,icon:L.divIcon({className:`route-traveler-icon ${walking?'route-walker':'route-car'}`,html:`<span class="route-traveler-facing">${travelerSvg}</span>`,iconSize:[48,48],iconAnchor:[24,40]})}).addTo(map);}
  const grouped=new Map();stops.forEach((stop,index)=>{if(!valid(stop))return;const key=`${stop.latitude.toFixed(5)},${stop.longitude.toFixed(5)}`;if(!grouped.has(key))grouped.set(key,[]);grouped.get(key).push(index)});
  grouped.forEach(indices=>{const first=stops[indices[0]],numbers=indices.map(i=>stops[i].day).join('·');const marker=L.marker([first.latitude,first.longitude],{icon:L.divIcon({className:'route-stop-icon',html:`<span>${numbers}</span>`,iconSize:[36,33],iconAnchor:[18,30]}),title:`Days ${numbers}: ${first.label}`}).addTo(map);const label=document.createElement('span');label.textContent=`Day ${numbers}: ${first.label}`;marker.bindTooltip(label,{direction:'top',offset:[0,-25]});marker.on('click',()=>{pause();select(indices[0],true)});});
  if(hasStart){const label=document.createElement('span');label.textContent=`Journey starts: ${startStop.label}`;L.circleMarker([startStop.latitude,startStop.longitude],{radius:6,color:'#203d2e',fillColor:'#fff',fillOpacity:1,weight:2}).addTo(map).bindTooltip(label);}
  root.querySelector('[data-route-mode]').textContent=walking?'Mountain trekking route':'Safari route map';root.querySelector('[data-route-note]').textContent=traced?'Follow the mapped Machame and Mweka walking trails. This illustration is not a navigation guide.':allPinned?'Place markers show itinerary areas. Connecting lines indicate the journey, not exact walking or driving directions.':'Some day locations are not pinned yet. The map shows the known locations.';
  tabs.forEach((tab,index)=>tab.addEventListener('click',()=>{pause();select(index,true);if(allPinned){elapsed=dayProgress[index]*duration;paint(elapsed/duration);map.panTo(traveler.getLatLng(),{animate:!reduced})}else if(valid(stops[index]))map.panTo([stops[index].latitude,stops[index].longitude],{animate:!reduced})}));
  play.hidden=!allPinned||!length;reset.hidden=play.hidden;play.addEventListener('click',begin);reset.addEventListener('click',()=>{pause();elapsed=0;startedAt=0;select(0);paint(0);if(positions.length)map.fitBounds(L.latLngBounds(positions),{padding:[50,55],maxZoom:traced?13:10})});paint(0);
  function refreshViewport(){
   if(!canvas.clientWidth||!canvas.clientHeight)return;
   map.invalidateSize({pan:false});
   if(playing&&traveler)map.panTo(traveler.getLatLng(),{animate:false});
   else if(positions.length)map.fitBounds(L.latLngBounds(positions),{padding:[60,65],maxZoom:traced?13:10,animate:false});
  }
  if('ResizeObserver'in window)new ResizeObserver(refreshViewport).observe(canvas);
  document.addEventListener('visibilitychange',()=>{if(document.hidden)pause()});
  if('IntersectionObserver'in window){let started=false;new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){refreshViewport();if(!started){started=true;if(allPinned&&!reduced&&length>0)begin()}}else pause()}),{threshold:.3}).observe(root)}
 });
})();
