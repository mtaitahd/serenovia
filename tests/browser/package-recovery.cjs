// Run with PLAYWRIGHT_MODULE pointing to an installed Playwright module.
const {chromium}=require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs=require('fs'); const assert=require('node:assert/strict');
const script=fs.readFileSync('public/js/admin-package-recovery.js','utf8');
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});
 try {
 const page=await browser.newPage();
 let requests=0, dialogs=0;
 page.on('dialog', async dialog=>{dialogs++;await dialog.dismiss()});
 await page.route('http://recovery.test/admin/tours/9',route=>{requests++;return route.fulfill({contentType:'text/html',body:'<p>Saved response</p>'})});
 await page.route('http://recovery.test/edit',route=>route.fulfill({contentType:'text/html',body:`<form class="package-builder" method="POST" action="/admin/tours/9" data-recovery-user="1" data-recovery-version="v1"><p data-recovery-status></p><input name="_token" value="fresh-token"><input name="title" value="Server title"><input name="gallery_image_id" value=""><input id="cover-upload" type="file" name="image"><div id="cover-preview"></div><input name="is_featured" type="checkbox" value="1"><select name="category_ids[]" multiple><option value="2">Mountain</option><option value="3">Safari</option></select><div class="itinerary-day-card"><input name="itinerary_days[0][id]" data-day-field="id" value="day-1"><textarea name="itinerary_days[0][details]" data-day-field="details">Original day</textarea></div><button type="submit">Save package</button></form><script>${script}</script>`}));
 await page.goto('http://recovery.test/edit');
 await page.waitForFunction(()=>document.querySelector('[data-recovery-status]').textContent.includes('ready'));
 await page.locator('[name=title]').fill('Recovered title');
 await page.locator('[name=gallery_image_id]').fill('12');
 await page.locator('[data-day-field=details]').fill('Recovered day activities');
 await page.locator('[name=is_featured]').check();
 await page.locator('[name="category_ids[]"]').selectOption(['2','3']);
 await page.locator('#cover-upload').setInputFiles({name:'cover.png',mimeType:'image/png',buffer:Buffer.from('test-file-content')});
 await page.evaluate(()=>window.savePackageRecovery());
 await page.reload();
 await page.waitForFunction(()=>document.querySelector('[data-recovery-status]').textContent.includes('restored'));
 assert.equal(await page.locator('[name=title]').inputValue(),'Recovered title');
 assert.equal(await page.locator('[name=gallery_image_id]').inputValue(),'12');
 assert.equal(await page.locator('[data-day-field=details]').inputValue(),'Recovered day activities');
 assert.equal(await page.locator('[name=is_featured]').isChecked(),true);
 assert.deepEqual(await page.locator('[name="category_ids[]"]').evaluate(s=>[...s.selectedOptions].map(o=>o.value)),['2','3']);
 assert.equal(await page.locator('#cover-upload').evaluate(async e=>await e.files[0].text()),'test-file-content');
 assert.equal(await page.locator('[name=_token]').inputValue(),'fresh-token');
 await page.evaluate(()=>localStorage.setItem('package-saved:1:/admin/tours/9',String(Date.now()+1000)));
 await page.reload();
 await page.waitForFunction(()=>document.querySelector('[data-recovery-status]').textContent.includes('ready'));
 assert.equal(await page.locator('[name=title]').inputValue(),'Server title');
 // Reproduce the submit-time change made by itinerary serialization.
 await page.locator('[name=title]').fill('Just edited before saving');
 await page.evaluate(()=>document.querySelector('form').addEventListener('submit',()=>{document.querySelector('[data-day-field=details]').value='Serialized during submit'}));
 await page.getByRole('button',{name:'Save package'}).click();
 await page.waitForURL('http://recovery.test/admin/tours/9');
 assert.equal(dialogs,0,'Saving must not show the Leave site warning');
 assert.equal(requests,1,'Only one save request should be sent');
 console.log('PASS: submit-time edits save without Leave site warnings or duplicate requests.');
 console.log('PASS: text, gallery selection, day activities, checkboxes, multiple selections and upload files recover; confirmed saves do not restore stale edits.');
 } finally {await browser.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
