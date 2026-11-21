import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';
const source=readFileSync(new URL('../../public/js/itinerary-route.js',import.meta.url),'utf8');
function setup(type,missing=false,reduced=false){
 const nodes=new Map(),classes=new Set(),markers=[]; let frame,observer;
 const element=()=>({style:{},hidden:false,setAttribute(){},addEventListener(event,fn){this[event]=fn;},classList:{add(){}}});
 const node=key=>{if(!nodes.has(key))nodes.set(key,element());return nodes.get(key)};
 node('[data-route-data]').textContent=JSON.stringify([{day:1,title:'Start',label:'Start',latitude:-3,longitude:37},{day:2,title:'End',label:'End',latitude:missing?null:-4,longitude:38}]);
 const root={dataset:{routeType:type},querySelector:node,querySelectorAll:()=>[],classList:{add:c=>classes.add(c),remove:c=>classes.delete(c)}};
 const layer=()=>({addTo(){return this},setLatLngs(){},bindTooltip(){return this},on(){return this}});
 const L={map:()=>({fitBounds(){},setView(){},invalidateSize(){}}),control:{zoom:()=>layer()},tileLayer:()=>layer(),latLngBounds:x=>x,polyline:()=>layer(),divIcon:x=>x,marker:(position,options)=>{const face=element(),m={...layer(),position,options,setLatLng(p){this.position=p},getElement:()=>({querySelector:()=>face})};markers.push(m);return m}};
 class Observer{constructor(fn){observer=fn}observe(){}}
 vm.runInNewContext(source,{document:{querySelectorAll:()=>[root],querySelector:()=>null,createElement:element,addEventListener(){}},window:{L,IntersectionObserver:Observer},L,IntersectionObserver:Observer,matchMedia:()=>({matches:reduced}),requestAnimationFrame:fn=>{frame=fn;return 1},cancelAnimationFrame:()=>{frame=null}});
 return {node,classes,traveler:markers.find(m=>m.options.interactive===false),tick:t=>frame(t),enter:()=>observer([{isIntersecting:true}])};
}
for(const [type,icon] of [['mountain','route-walker'],['safari','route-car']]){
 test(`${type} marker travels, pauses and resets`,()=>{
  const s=setup(type); assert.ok(s.traveler.options.icon.className.includes(icon));
  s.node('[data-route-play]').click();s.tick(100);s.tick(2600);
  assert.deepEqual(Array.from(s.traveler.position),[-3.5,37.5]);
  assert.ok(s.classes.has('route-is-playing'));
  s.node('[data-route-play]').click();assert.ok(!s.classes.has('route-is-playing'));
  s.node('[data-route-reset]').click();assert.deepEqual(Array.from(s.traveler.position),[-3,37]);
 });
}
test('missing coordinates disable playback',()=>{const s=setup('mountain',true);assert.equal(s.traveler,undefined);assert.equal(s.node('[data-route-play]').hidden,true)});
test('reduced motion prevents automatic playback',()=>{const s=setup('mountain',false,true);s.enter();assert.ok(!s.classes.has('route-is-playing'))});
