---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Pošli správu](../../../media/bricks/broadcast2-1.png)

Pošle správu ostatným častiam tvojho projektu a povie im, aby spustili svoje skripty. Je to ako oznam, ktorý počúva každý.

**Prečo posielať správy?**

Vďaka správam sa môžu rôzne objekty v scéne dorozumieť a navzájom pôsobiť. Jeden objekt môže poslať správu všetkým ostatným objektom a prijímajúce objekty na ňu potom môžu zareagovať.

Tieto správy sa **nezobrazujú** na obrazovke! Ak chceš zobraziť správy, použi bloky z kategórie „Vzhľad“!

**Ako to funguje?**

- *Odosielajúci objekt:* Odosielajúci objekt začne komunikáciu blokom *„Pošli správu“*. Tento blok pošle správu všetkým ostatným objektom v scéne (aj pozadiu).
- *Prijímajúci objekt:* Prijímajúci objekt počúva správy a reaguje na ne. Blokom *„Keď dostaneš správu“* zistí, kedy sa dostane konkrétna správa.
- *Spracovanie správy:* Keď prijímajúci objekt dostane správu, vykoná všetky bloky pod blokom *„Keď dostaneš správu“*. Vďaka tomu môžeš určiť vlastné správanie alebo akcie, ktoré nastanú pri prijatí konkrétnej správy.
