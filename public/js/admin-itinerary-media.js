(() => {
    let map, marker, activeCard, selected;
    const numberValue = input => input.value.trim() === '' ? null : Number(input.value);
    window.setupItineraryDayMedia = (card, day, list) => {
        const uuid = () => crypto.randomUUID ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,c=>{const r=Math.random()*16|0;return (c==='x'?r:(r&3|8)).toString(16)});
        ['id','location','latitude','longitude','meals','altitude','habitat','overnight_camp','distance','walking_time'].forEach(key => { card.querySelector(`[data-day-field="${key}"]`).value = key === 'id' ? (day.id || uuid()) : (day[key] ?? ''); });
        const gallery = card.querySelector('[data-day-field="gallery_ids"]');
        [...gallery.options].forEach(option => { option.selected = (day.gallery_ids || []).map(String).includes(option.value); });
        const transport = card.querySelector('[data-day-field="transport_gallery_ids"]');
        if (transport) {
            [...transport.options].forEach(option => { option.selected = (day.transport_gallery_ids || []).map(String).includes(option.value); });
        }
        let saved = {}; try { saved = JSON.parse(list.dataset.savedPhotos || '{}'); } catch {}
        const images = saved[day.id] || day.images || [];
        images.forEach(path => {
            const label = document.createElement('label'); label.className = 'day-saved-photo';
            const img = document.createElement('img'); img.src = new URL(path, list.dataset.photoBase).href; img.alt = 'Saved day photograph';
            const text = document.createElement('span'); const checkbox = document.createElement('input'); checkbox.type = 'checkbox'; checkbox.value = path; checkbox.dataset.removePhoto = ''; checkbox.checked = (day.remove_images || []).includes(path);
            text.append(checkbox, document.createTextNode(' Remove photo')); label.append(img,text); card.querySelector('[data-day-saved-photos]').append(label);
        });
        const previews = card.querySelector('[data-day-upload-previews]'); let objectUrls = [];
        const paint = () => {
            objectUrls.forEach(URL.revokeObjectURL); objectUrls = []; previews.replaceChildren();
            [...gallery.selectedOptions].filter(o=>o.value).forEach(option=>{const img=document.createElement('img');img.src=option.dataset.photoUrl;img.alt=option.textContent;previews.append(img)});
            [...card.querySelector('[data-day-field="uploads"]').files].forEach(file => { const img=document.createElement('img');const url=URL.createObjectURL(file);objectUrls.push(url);img.src=url;img.alt=file.name;previews.append(img); });
        };
        gallery.addEventListener('change', paint); card.querySelector('[data-day-field="uploads"]').addEventListener('change',paint); paint();
        card.querySelector('[data-pin-day]').addEventListener('click', () => {
            const dialog = document.getElementById('route-location-dialog'); activeCard = card; selected = null;
            dialog.querySelector('[data-save-route-pin]').disabled = true; dialog.showModal();
            const status = dialog.querySelector('[data-route-map-status]');
            if (!window.L) { status.textContent = 'The map could not load. Check your internet connection, or enter latitude and longitude in the day form.'; return; }
            if (!map) {
                map = L.map('admin-day-location-map').setView([0,25],3);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:18,attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}).addTo(map);
                map.on('click', event => {
                    selected = event.latlng;
                    if (marker) marker.setLatLng(selected); else marker = L.circleMarker(selected,{radius:9,color:'#dc704f',fillColor:'#dc704f',fillOpacity:.8}).addTo(map);
                    status.textContent = `Selected: ${selected.lat.toFixed(6)}, ${selected.lng.toFixed(6)}`;
                    dialog.querySelector('[data-save-route-pin]').disabled = false;
                });
            }
            requestAnimationFrame(()=>{
                map.invalidateSize(); const lat = numberValue(card.querySelector('[data-day-field="latitude"]')), lng = numberValue(card.querySelector('[data-day-field="longitude"]'));
                if (lat !== null && lng !== null) { map.setView([lat,lng],9); if(marker) marker.setLatLng([lat,lng]);else marker=L.circleMarker([lat,lng],{radius:9,color:'#dc704f'}).addTo(map); }
                else { map.setView([0,25],3); if(marker){map.removeLayer(marker);marker=null} }
                status.textContent = 'Click the destination to place this day’s pin.';
            });
        });
    };
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('.package-builder');
        form?.addEventListener('submit',event=>{
            const files=[...form.querySelectorAll('input[type="file"]')].flatMap(input=>[...input.files]);
            const match=(form.dataset.maxPostSize||'0').match(/^([\d.]+)\s*([KMG]?)$/i);
            const limit=match?Number(match[1])*Math.pow(1024,({K:1,M:2,G:3}[match[2].toUpperCase()]||0)):0;
            let message='';
            if(files.length>Number(form.dataset.maxUploadFiles||20))message=`This server accepts up to ${form.dataset.maxUploadFiles||20} uploads at once. Save these photos first, then add more in Edit. You can also select saved gallery photographs.`;
            else if(files.some(file=>file.size>8*1024*1024))message='Each photograph must be 8 MB or smaller.';
            else if(limit && files.reduce((sum,file)=>sum+file.size,0)>limit*.9)message='These photos exceed the server upload size. Save fewer photos at a time, or use the photo gallery.';
            if(message){event.preventDefault();alert(message);}
        });
        const dialog = document.getElementById('route-location-dialog'); if(!dialog) return;
        dialog.querySelector('[data-close-route-map]').addEventListener('click',()=>dialog.close());
        dialog.querySelector('[data-save-route-pin]').addEventListener('click',()=>{
            if(!selected || !activeCard) return;
            activeCard.querySelector('[data-day-field="latitude"]').value=selected.lat.toFixed(6);
            activeCard.querySelector('[data-day-field="longitude"]').value=selected.lng.toFixed(6); dialog.close();
        });
    });
})();
