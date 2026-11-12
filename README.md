# Serenovia Africa Safaris — XAMPP

Project hii ni Laravel 12, PHP 8.2+, ikiwa na database ya SQLite iliyopo kwenye `database/database.sqlite`.

## Kufungua
1. Fungua XAMPP Control Panel na uwashe Apache.
2. Tovuti: http://localhost/serenova/
3. Admin: http://localhost/serenova/admin/login

Akaunti ya admin tayari ipo kwenye database. Tumia taarifa zako za kuingia zilizopo.
MySQL haihitajiki kwa usanidi huu. Database na maudhui yaliyokuwepo yamehifadhiwa.

## Usanidi uliokamilishwa
- Mafaili ya routes, resources, storage na vendor yametolewa kutoka serenova.zip.
- APP_URL imewekwa kuwa http://localhost/serenova.
- Database migrations zote zimekamilika kwenye database iliyopo.
- PHP GD imewashwa kwa kupakia na kubana picha.
- CSS na JavaScript zinatumika moja kwa moja kutoka public; npm/Vite haihitajiki kufungua kurasa za sasa.

## Ukihamisha project
Sasisha APP_URL kwenye .env na RewriteBase kwenye .htaccess kulingana na jina la folder.
PHP lazima iwe na pdo_sqlite, fileinfo, mbstring, openssl na gd.
Usifute database/database.sqlite au kubadilisha APP_KEY ya database iliyopo.

## Itinerary: picha na ramani
Katika Admin → Safari packages → Add/Edit → Step 3 (Itinerary):
- Weka alama kwenye **Need a route map?** ili kuonyesha ramani yenye animation.
- Kila siku ina **Photographs & route stop**: weka jina la kituo, chagua picha za gallery au upload picha mpya.
- **Pin this stop on the map** huweka latitude na longitude. Ukipin siku zote, website itaonyesha ramani ya kijiografia (OpenStreetMap; inahitaji internet).
- Bila pins, mfumo huchora mpangilio wa safari wenye animation moja kwa moja. Huu ni mchoro wa itinerary, si maelekezo ya kuendesha gari au uthibitisho wa flight.
- Kila siku huonyesha picha zake; unaweza kuongeza au kuondoa picha wakati wa ku-edit. Itinerary zilizokuwepo bado zinaonekana.
- Picha nyingi zinaweza kuhifadhiwa kwa awamu kulingana na upload limits za XAMPP. Tumia gallery kuchagua picha nyingi bila kuzipakia tena.
